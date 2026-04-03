<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\WifiPortal;
use App\Models\WifiSession;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerReportController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        [$filters, $startAt, $endAt, $bucketType, $bucketLabels] = $this->resolveWindow($request);

        $newCustomers = Guest::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('first_seen_at', [$startAt, $endAt])
            ->count();

        $activeCustomers = Guest::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('last_seen_at', [$startAt, $endAt])
            ->count();

        $returningCustomers = Guest::query()
            ->where('organization_id', $organization->id)
            ->where('first_seen_at', '<', $startAt)
            ->whereBetween('last_seen_at', [$startAt, $endAt])
            ->count();

        $totalSessions = WifiSession::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('started_at', [$startAt, $endAt])
            ->count();

        $surveyCompletedSessions = WifiSession::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('survey_submitted_at', [$startAt, $endAt])
            ->count();

        $topPortalRows = WifiSession::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('started_at', [$startAt, $endAt])
            ->whereNotNull('wifi_portal_id')
            ->selectRaw('wifi_portal_id, COUNT(*) as session_count')
            ->groupBy('wifi_portal_id')
            ->orderByDesc('session_count')
            ->limit(5)
            ->get();

        $portalNames = WifiPortal::query()
            ->whereIn('id', $topPortalRows->pluck('wifi_portal_id')->filter()->unique()->values())
            ->pluck('name', 'id');

        $topPortals = $topPortalRows->map(static function ($row) use ($portalNames): array {
            return [
                'name' => $portalNames[$row->wifi_portal_id] ?? 'Unknown Portal',
                'session_count' => (int) $row->session_count,
            ];
        });

        $trendRows = $this->buildTrendRows(
            organizationId: $organization->id,
            startAt: $startAt,
            endAt: $endAt,
            bucketType: $bucketType,
            bucketLabels: $bucketLabels
        );

        $customers = $this->baseCustomerQuery(
            organizationId: $organization->id,
            startAt: $startAt,
            endAt: $endAt
        )->paginate(25)->withQueryString();

        return view('organization.reports.customers', [
            'organization' => $organization,
            'filters' => $filters,
            'startAt' => $startAt,
            'endAt' => $endAt,
            'summary' => [
                'new_customers' => $newCustomers,
                'active_customers' => $activeCustomers,
                'returning_customers' => $returningCustomers,
                'total_sessions' => $totalSessions,
                'survey_completed_sessions' => $surveyCompletedSessions,
                'completion_rate' => $totalSessions > 0 ? round(($surveyCompletedSessions / $totalSessions) * 100, 1) : 0.0,
                'avg_sessions_per_active_customer' => $activeCustomers > 0 ? round($totalSessions / $activeCustomers, 2) : 0.0,
            ],
            'trendRows' => $trendRows,
            'topPortals' => $topPortals,
            'customers' => $customers,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $organization = $request->user()->organization;
        [$filters, $startAt, $endAt] = $this->resolveWindow($request);

        $customers = $this->baseCustomerQuery(
            organizationId: $organization->id,
            startAt: $startAt,
            endAt: $endAt
        )->limit(5000)->get();

        $filename = 'customer-report-'.$organization->slug.'-'.$filters['period'].'-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($customers, $startAt, $endAt): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'guest_id',
                'first_name',
                'phone',
                'gender',
                'first_seen_at',
                'last_seen_at',
                'sessions_in_period',
                'responses_in_period',
                'is_new_in_period',
                'is_returning_in_period',
            ]);

            foreach ($customers as $customer) {
                $isNew = $customer->first_seen_at !== null && $customer->first_seen_at->betweenIncluded($startAt, $endAt);
                $isReturning = ! $isNew;

                fputcsv($out, [
                    $customer->id,
                    $customer->first_name,
                    $customer->phone,
                    $customer->gender,
                    optional($customer->first_seen_at)->toDateTimeString(),
                    optional($customer->last_seen_at)->toDateTimeString(),
                    $customer->sessions_in_period_count,
                    $customer->responses_in_period_count,
                    $isNew ? 'yes' : 'no',
                    $isReturning ? 'yes' : 'no',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function resolveWindow(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:monthly,annual,custom'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $period = $validated['period'] ?? 'monthly';
        $today = now();

        if ($period === 'annual') {
            $year = (int) ($validated['year'] ?? $today->year);
            $startAt = Carbon::create($year, 1, 1)->startOfDay();
            $endAt = Carbon::create($year, 12, 31)->endOfDay();
            $bucketType = 'month';
        } elseif ($period === 'custom') {
            $startAt = isset($validated['start_date'])
                ? Carbon::parse($validated['start_date'])->startOfDay()
                : $today->copy()->subDays(29)->startOfDay();
            $endAt = isset($validated['end_date'])
                ? Carbon::parse($validated['end_date'])->endOfDay()
                : $today->copy()->endOfDay();
            $bucketType = 'day';
        } else {
            $month = $validated['month'] ?? $today->format('Y-m');
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $startAt = $monthDate->copy()->startOfMonth()->startOfDay();
            $endAt = $monthDate->copy()->endOfMonth()->endOfDay();
            $period = 'monthly';
            $bucketType = 'day';
        }

        $bucketLabels = [];

        if ($bucketType === 'month') {
            $periodMonths = CarbonPeriod::create($startAt->copy()->startOfMonth(), '1 month', $endAt->copy()->startOfMonth());

            foreach ($periodMonths as $monthItem) {
                $bucketLabels[$monthItem->format('Y-m')] = $monthItem->format('M Y');
            }
        } else {
            $periodDays = CarbonPeriod::create($startAt->copy()->startOfDay(), '1 day', $endAt->copy()->startOfDay());

            foreach ($periodDays as $dayItem) {
                $bucketLabels[$dayItem->format('Y-m-d')] = $dayItem->format('d M');
            }
        }

        return [[
            'period' => $period,
            'month' => $startAt->format('Y-m'),
            'year' => $startAt->year,
            'start_date' => $startAt->toDateString(),
            'end_date' => $endAt->toDateString(),
        ], $startAt, $endAt, $bucketType, $bucketLabels];
    }

    private function buildTrendRows(
        int $organizationId,
        Carbon $startAt,
        Carbon $endAt,
        string $bucketType,
        array $bucketLabels
    ): Collection {
        $rows = collect($bucketLabels)->map(static fn ($label) => [
            'label' => $label,
            'new_customers' => 0,
            'active_customers' => 0,
            'sessions' => 0,
            'survey_completed_sessions' => 0,
            'completion_rate' => 0.0,
        ]);

        $newCustomerGroups = Guest::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('first_seen_at', [$startAt, $endAt])
            ->get(['first_seen_at'])
            ->groupBy(fn ($item) => optional($item->first_seen_at)->format($bucketType === 'month' ? 'Y-m' : 'Y-m-d'));

        $activeCustomerGroups = Guest::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('last_seen_at', [$startAt, $endAt])
            ->get(['last_seen_at'])
            ->groupBy(fn ($item) => optional($item->last_seen_at)->format($bucketType === 'month' ? 'Y-m' : 'Y-m-d'));

        $sessionGroups = WifiSession::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('started_at', [$startAt, $endAt])
            ->get(['started_at'])
            ->groupBy(fn ($item) => optional($item->started_at)->format($bucketType === 'month' ? 'Y-m' : 'Y-m-d'));

        $completionGroups = WifiSession::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('survey_submitted_at', [$startAt, $endAt])
            ->get(['survey_submitted_at'])
            ->groupBy(fn ($item) => optional($item->survey_submitted_at)->format($bucketType === 'month' ? 'Y-m' : 'Y-m-d'));

        foreach ($bucketLabels as $key => $label) {
            $sessions = $sessionGroups->get($key, collect())->count();
            $completed = $completionGroups->get($key, collect())->count();

            $rows[$key]['new_customers'] = $newCustomerGroups->get($key, collect())->count();
            $rows[$key]['active_customers'] = $activeCustomerGroups->get($key, collect())->count();
            $rows[$key]['sessions'] = $sessions;
            $rows[$key]['survey_completed_sessions'] = $completed;
            $rows[$key]['completion_rate'] = $sessions > 0 ? round(($completed / $sessions) * 100, 1) : 0.0;
        }

        return $rows->values();
    }

    private function baseCustomerQuery(int $organizationId, Carbon $startAt, Carbon $endAt)
    {
        return Guest::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('last_seen_at')
            ->whereBetween('last_seen_at', [$startAt, $endAt])
            ->withCount([
                'sessions as sessions_in_period_count' => fn ($query) => $query->whereBetween('started_at', [$startAt, $endAt]),
                'responses as responses_in_period_count' => fn ($query) => $query->whereBetween('submitted_at', [$startAt, $endAt]),
            ])
            ->orderByDesc('last_seen_at');
    }
}
