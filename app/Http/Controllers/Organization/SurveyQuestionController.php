<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyQuestionController extends Controller
{
    public function store(Request $request, SurveyTemplate $survey): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($survey->organization_id !== $organization->id, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:short_text,long_text,single_choice,multi_choice,rating,nps,yes_no,phone,date'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'options_text' => ['nullable', 'string', 'max:4000'],
            'is_required' => ['nullable', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $question = $survey->questions()->create([
            'question_key' => 'q_'.Str::lower(Str::random(8)),
            'label' => $validated['label'],
            'type' => $validated['type'],
            'placeholder' => $validated['placeholder'] ?? null,
            'options' => $this->parseOptions($validated['options_text'] ?? null),
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'order_index' => $validated['order_index'] ?? ($survey->questions()->max('order_index') + 1),
        ]);

        AuditTrail::record(
            action: 'organization.survey_question.created',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $question,
            request: $request
        );

        return redirect()
            ->route('organization.surveys.edit', $survey)
            ->with('status', 'Question added.');
    }

    public function update(Request $request, SurveyTemplate $survey, SurveyQuestion $question): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($survey->organization_id !== $organization->id, 404);
        abort_if($question->survey_template_id !== $survey->id, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:short_text,long_text,single_choice,multi_choice,rating,nps,yes_no,phone,date'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'options_text' => ['nullable', 'string', 'max:4000'],
            'is_required' => ['nullable', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $question->update([
            'label' => $validated['label'],
            'type' => $validated['type'],
            'placeholder' => $validated['placeholder'] ?? null,
            'options' => $this->parseOptions($validated['options_text'] ?? null),
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'order_index' => $validated['order_index'] ?? $question->order_index,
        ]);

        AuditTrail::record(
            action: 'organization.survey_question.updated',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $question,
            request: $request
        );

        return redirect()
            ->route('organization.surveys.edit', $survey)
            ->with('status', 'Question updated.');
    }

    public function destroy(Request $request, SurveyTemplate $survey, SurveyQuestion $question): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($survey->organization_id !== $organization->id, 404);
        abort_if($question->survey_template_id !== $survey->id, 404);

        $question->delete();

        AuditTrail::record(
            action: 'organization.survey_question.deleted',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $question,
            request: $request
        );

        return redirect()
            ->route('organization.surveys.edit', $survey)
            ->with('status', 'Question removed.');
    }

    private function parseOptions(?string $raw): ?array
    {
        if (! $raw) {
            return null;
        }

        $parts = preg_split('/[\r\n,]+/', $raw) ?: [];
        $options = array_values(array_filter(array_map(static fn ($item) => trim($item), $parts)));

        return $options === [] ? null : $options;
    }
}
