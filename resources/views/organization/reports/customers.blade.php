@extends('layouts.app')
@php($title = 'Customer Reports')

@section('top_actions')
    <a class="btn secondary" href="{{ route('organization.reports.customers.export', request()->query()) }}">Export CSV</a>
@endsection

@section('content')
    <div class="card">
        <h2>Customer Reporting</h2>
        <p class="muted">Generate monthly, annual, or custom-range reports for customer activity and engagement.</p>
        <form method="GET" action="{{ route('organization.reports.customers') }}">
            <div class="grid-2">
                <div>
                    <label>Report Type</label>
                    <select name="period">
                        <option value="monthly" @selected($filters['period'] === 'monthly')>Monthly</option>
                        <option value="annual" @selected($filters['period'] === 'annual')>Annual</option>
                        <option value="custom" @selected($filters['period'] === 'custom')>Custom Range</option>
                    </select>
                </div>
                <div>
                    <label>Month (for monthly)</label>
                    <input type="month" name="month" value="{{ $filters['month'] }}">
                </div>
                <div>
                    <label>Year (for annual)</label>
                    <input type="number" min="2000" max="2100" name="year" value="{{ $filters['year'] }}">
                </div>
                <div>
                    <label>Start date (for custom)</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}">
                </div>
                <div>
                    <label>End date (for custom)</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}">
                </div>
            </div>
            <button class="btn" type="submit">Generate Report</button>
        </form>
        <p class="muted" style="margin-top:8px;">
            Current range: <strong>{{ $startAt->toDateString() }}</strong> to <strong>{{ $endAt->toDateString() }}</strong>
        </p>
    </div>

    <div class="grid-3">
        <div class="card"><strong>{{ $summary['new_customers'] }}</strong><br><span class="muted">New customers</span></div>
        <div class="card"><strong>{{ $summary['active_customers'] }}</strong><br><span class="muted">Active customers</span></div>
        <div class="card"><strong>{{ $summary['returning_customers'] }}</strong><br><span class="muted">Returning customers</span></div>
        <div class="card"><strong>{{ $summary['total_sessions'] }}</strong><br><span class="muted">WiFi sessions</span></div>
        <div class="card"><strong>{{ $summary['survey_completed_sessions'] }}</strong><br><span class="muted">Survey completed sessions</span></div>
        <div class="card"><strong>{{ $summary['completion_rate'] }}%</strong><br><span class="muted">Survey completion rate</span></div>
        <div class="card"><strong>{{ $summary['avg_sessions_per_active_customer'] }}</strong><br><span class="muted">Avg sessions per active customer</span></div>
    </div>

    <div class="card">
        <h3>Trend Breakdown</h3>
        <table>
            <thead>
            <tr>
                <th>Period</th>
                <th>New customers</th>
                <th>Active customers</th>
                <th>Sessions</th>
                <th>Survey completed</th>
                <th>Completion rate</th>
            </tr>
            </thead>
            <tbody>
            @forelse($trendRows as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['new_customers'] }}</td>
                    <td>{{ $row['active_customers'] }}</td>
                    <td>{{ $row['sessions'] }}</td>
                    <td>{{ $row['survey_completed_sessions'] }}</td>
                    <td>{{ $row['completion_rate'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No data in selected range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Top Portals by Sessions</h3>
        <table>
            <thead><tr><th>Portal</th><th>Sessions</th></tr></thead>
            <tbody>
            @forelse($topPortals as $portal)
                <tr>
                    <td>{{ $portal['name'] }}</td>
                    <td>{{ $portal['session_count'] }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="muted">No sessions found for this range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Customers In Selected Interval</h3>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>First seen</th>
                <th>Last seen</th>
                <th>Sessions in period</th>
                <th>Responses in period</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->first_name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ optional($customer->first_seen_at)->toDateTimeString() ?? '-' }}</td>
                    <td>{{ optional($customer->last_seen_at)->toDateTimeString() ?? '-' }}</td>
                    <td>{{ $customer->sessions_in_period_count }}</td>
                    <td>{{ $customer->responses_in_period_count }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No active customers in this range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
@endsection
