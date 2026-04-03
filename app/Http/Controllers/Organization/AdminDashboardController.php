<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;

        abort_if(! $organization, 403);

        $stats = [
            'total_guests' => $organization->guests()->count(),
            'sessions_today' => $organization->sessions()->whereDate('created_at', today())->count(),
            'responses_today' => $organization->sessions()->whereDate('survey_submitted_at', today())->count(),
            'active_portals' => $organization->portals()->where('is_active', true)->count(),
            'templates' => $organization->surveyTemplates()->count(),
        ];

        $npsAvg = SurveyAnswer::query()
            ->whereHas('response', fn ($query) => $query->where('organization_id', $organization->id))
            ->whereHas('question', fn ($query) => $query->where('type', 'nps'))
            ->avg('answer_number');

        $recentResponses = $organization->sessions()
            ->with(['guest', 'portal'])
            ->whereNotNull('survey_submitted_at')
            ->latest('survey_submitted_at')
            ->limit(12)
            ->get();

        return view('organization.dashboard', [
            'organization' => $organization,
            'stats' => $stats,
            'npsAverage' => $npsAvg ? round((float) $npsAvg, 1) : null,
            'recentResponses' => $recentResponses,
        ]);
    }
}
