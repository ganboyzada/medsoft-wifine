<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ResponseController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $responses = SurveyResponse::query()
            ->where('organization_id', $organization->id)
            ->with(['guest', 'portal', 'template'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('organization.responses.index', compact('organization', 'responses'));
    }

    public function export(Request $request): StreamedResponse
    {
        $organization = $request->user()->organization;

        $responses = SurveyResponse::query()
            ->where('organization_id', $organization->id)
            ->with(['guest', 'portal', 'template', 'answers.question'])
            ->latest('submitted_at')
            ->limit(2000)
            ->get();

        $filename = 'responses-'.$organization->slug.'-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($responses): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['submitted_at', 'portal', 'template', 'guest_name', 'phone', 'question', 'answer']);

            foreach ($responses as $response) {
                foreach ($response->answers as $answer) {
                    fputcsv($out, [
                        $response->submitted_at?->toDateTimeString(),
                        $response->portal?->name,
                        $response->template?->name,
                        $response->guest?->first_name,
                        $response->guest?->phone,
                        $answer->question?->label,
                        $this->stringifyAnswer($answer),
                    ]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function stringifyAnswer($answer): string
    {
        if ($answer->answer_json !== null) {
            return implode(' | ', (array) $answer->answer_json);
        }

        if ($answer->answer_boolean !== null) {
            return $answer->answer_boolean ? 'Yes' : 'No';
        }

        if ($answer->answer_number !== null) {
            return (string) $answer->answer_number;
        }

        return (string) ($answer->answer_text ?? '');
    }
}
