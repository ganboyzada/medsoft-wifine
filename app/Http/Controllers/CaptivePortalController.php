<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Guest;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplate;
use App\Models\WifiPortal;
use App\Models\WifiSession;
use App\Support\AuditTrail;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CaptivePortalController extends Controller
{
    public function show(Request $request, WifiPortal $portal): View
    {
        abort_if(! $portal->is_active, 404);

        $portal->load('organization');

        $template = $this->resolveTemplate($portal);
        abort_if(! $template || $template->questions()->count() === 0, 503, 'Survey template is not configured.');

        $session = $this->resolveSession($request, $portal);
        $session->load('guest');

        return view('portal.show', [
            'portal' => $portal,
            'template' => $template->load('questions'),
            'session' => $session,
            'knownPhone' => $request->cookie('wifi_guest_phone', ''),
        ]);
    }

    public function submit(Request $request, WifiPortal $portal): RedirectResponse|View
    {
        abort_if(! $portal->is_active, 404);

        $template = $this->resolveTemplate($portal);
        abort_if(! $template, 503);

        $baseValidator = Validator::make($request->all(), [
            'session_token' => ['required', 'uuid'],
            'first_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
            'gender' => ['nullable', 'in:male,female,non_binary,prefer_not_to_say'],
            'consent_terms' => ['accepted'],
            'consent_marketing' => ['nullable', 'boolean'],
        ]);

        $dynamicRules = [];

        $questions = $template->questions()->orderBy('order_index')->get();
        foreach ($questions as $question) {
            $key = 'answers.'.$question->id;
            $dynamicRules += $this->rulesForQuestion($question, $key);
        }

        $allValidator = Validator::make(
            $request->all(),
            array_merge($baseValidator->getRules(), $dynamicRules),
            [
                'consent_terms.accepted' => 'You must accept terms to continue.',
            ]
        );

        if ($allValidator->fails()) {
            return redirect()
                ->back()
                ->withErrors($allValidator)
                ->withInput();
        }

        $session = WifiSession::query()
            ->where('wifi_portal_id', $portal->id)
            ->where('session_token', $request->string('session_token'))
            ->firstOrFail();

        if ($session->expires_at && now()->gt($session->expires_at)) {
            $session->update(['status' => 'expired']);
            abort(410, 'Session expired. Please reconnect to WiFi and try again.');
        }

        $normalizedPhone = PhoneNormalizer::normalize($request->string('phone')->value());

        if ($normalizedPhone === '+') {
            return back()
                ->withErrors(['phone' => 'Enter a valid phone number.'])
                ->withInput();
        }

        $isReturningGuest = false;

        DB::transaction(function () use (
            $request,
            $portal,
            $template,
            $session,
            $questions,
            $normalizedPhone,
            &$isReturningGuest
        ): void {
            $guest = Guest::query()
                ->where('organization_id', $portal->organization_id)
                ->where('phone_normalized', $normalizedPhone)
                ->first();

            $isReturningGuest = $guest !== null;

            if (! $guest) {
                $guest = new Guest([
                    'organization_id' => $portal->organization_id,
                    'phone' => $request->string('phone')->value(),
                    'phone_normalized' => $normalizedPhone,
                ]);
            }

            $guest->first_name = $request->string('first_name')->value();
            $guest->gender = $request->string('gender')->value() ?: null;
            $guest->consent_marketing = $request->boolean('consent_marketing');
            $guest->consent_terms = true;
            $guest->touchSeenAt();
            $guest->save();

            $response = SurveyResponse::query()->create([
                'organization_id' => $portal->organization_id,
                'wifi_portal_id' => $portal->id,
                'survey_template_id' => $template->id,
                'guest_id' => $guest->id,
                'wifi_session_id' => $session->id,
                'submitted_at' => now(),
                'sentiment_score' => $this->calculateSentimentScore($questions, (array) $request->input('answers', [])),
            ]);

            foreach ($questions as $question) {
                SurveyAnswer::query()->create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $question->id,
                    ...$this->mapAnswerPayload($question, $request->input('answers.'.$question->id)),
                ]);
            }

            $session->update([
                'guest_id' => $guest->id,
                'status' => 'survey_completed',
                'survey_submitted_at' => now(),
            ]);
        });

        $campaign = Campaign::query()
            ->where('organization_id', $portal->organization_id)
            ->get()
            ->first(fn (Campaign $item): bool => $item->isVisibleFor($isReturningGuest));

        AuditTrail::record(
            action: 'portal.survey.completed',
            organizationId: $portal->organization_id,
            subject: $session,
            payload: ['portal_id' => $portal->id],
            request: $request
        );

        return response()
            ->view('portal.success', [
                'portal' => $portal,
                'session' => $session->fresh(),
                'campaign' => $campaign,
            ])
            ->cookie('wifi_guest_phone', $request->string('phone')->value(), 60 * 24 * 365);
    }

    private function resolveTemplate(WifiPortal $portal): ?SurveyTemplate
    {
        if ($portal->survey_template_id) {
            return SurveyTemplate::query()
                ->where('organization_id', $portal->organization_id)
                ->where('is_active', true)
                ->find($portal->survey_template_id);
        }

        return SurveyTemplate::query()
            ->where('organization_id', $portal->organization_id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    private function resolveSession(Request $request, WifiPortal $portal): WifiSession
    {
        $sessionToken = $request->query('session_token');

        if ($sessionToken) {
            $session = WifiSession::query()
                ->where('wifi_portal_id', $portal->id)
                ->where('session_token', $sessionToken)
                ->first();

            if ($session) {
                return $session;
            }
        }

        return WifiSession::query()->create([
            'organization_id' => $portal->organization_id,
            'wifi_portal_id' => $portal->id,
            'status' => 'initiated',
            'client_mac' => $request->query('client_mac'),
            'ap_mac' => $request->query('ap_mac'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'redirect_url' => $request->query('redirect', $portal->post_login_redirect_url),
            'expires_at' => now()->addMinutes($portal->session_ttl_minutes),
            'metadata' => [
                'query' => $request->except(['session_token']),
            ],
        ]);
    }

    private function rulesForQuestion(SurveyQuestion $question, string $ruleKey): array
    {
        $requiredPrefix = $question->is_required ? 'required' : 'nullable';
        $options = $question->options ?? [];
        $escapedOptions = implode(',', array_map(static fn ($option) => str_replace(',', '\,', $option), $options));

        return match ($question->type) {
            'short_text' => [$ruleKey => [$requiredPrefix, 'string', 'max:255']],
            'long_text' => [$ruleKey => [$requiredPrefix, 'string', 'max:4000']],
            'single_choice' => [$ruleKey => $escapedOptions === '' ? [$requiredPrefix, 'string', 'max:255'] : [$requiredPrefix, 'in:'.$escapedOptions]],
            'multi_choice' => [
                $ruleKey => [$requiredPrefix, 'array'],
                $ruleKey.'.*' => $escapedOptions === '' ? ['string', 'max:255'] : ['in:'.$escapedOptions],
            ],
            'rating' => [$ruleKey => [$requiredPrefix, 'integer', 'between:1,5']],
            'nps' => [$ruleKey => [$requiredPrefix, 'integer', 'between:0,10']],
            'yes_no' => [$ruleKey => [$requiredPrefix, 'boolean']],
            'phone' => [$ruleKey => [$requiredPrefix, 'string', 'max:40']],
            'date' => [$ruleKey => [$requiredPrefix, 'date']],
            default => [$ruleKey => [$requiredPrefix]],
        };
    }

    private function mapAnswerPayload(SurveyQuestion $question, mixed $answer): array
    {
        return match ($question->type) {
            'rating', 'nps' => ['answer_number' => is_numeric($answer) ? (float) $answer : null],
            'yes_no' => ['answer_boolean' => $answer === null ? null : (bool) $answer],
            'multi_choice' => ['answer_json' => $answer],
            default => ['answer_text' => $answer === null ? null : (string) $answer],
        };
    }

    private function calculateSentimentScore($questions, array $answers): ?float
    {
        $values = [];

        foreach ($questions as $question) {
            if (! in_array($question->type, ['rating', 'nps'], true)) {
                continue;
            }

            $answer = $answers[$question->id] ?? null;

            if (is_numeric($answer)) {
                if ($question->type === 'nps') {
                    $values[] = ((float) $answer / 10) * 5;
                } else {
                    $values[] = (float) $answer;
                }
            }
        }

        if ($values === []) {
            return null;
        }

        return round(array_sum($values) / count($values), 2);
    }
}
