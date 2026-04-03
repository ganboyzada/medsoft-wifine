@extends('layouts.app')
@php($title = 'Organization Dashboard')

@section('top_actions')
    <a class="btn" href="{{ route('organization.portals.create') }}">+ Add Portal</a>
@endsection

@section('content')
    <div class="grid-3">
        <div class="card"><strong>{{ $stats['total_guests'] }}</strong><br><span class="muted">Total guests</span></div>
        <div class="card"><strong>{{ $stats['sessions_today'] }}</strong><br><span class="muted">Sessions today</span></div>
        <div class="card"><strong>{{ $stats['responses_today'] }}</strong><br><span class="muted">Survey submissions today</span></div>
        <div class="card"><strong>{{ $stats['active_portals'] }}</strong><br><span class="muted">Active portals</span></div>
        <div class="card"><strong>{{ $stats['templates'] }}</strong><br><span class="muted">Survey templates</span></div>
        <div class="card"><strong>{{ $npsAverage ?? '-' }}</strong><br><span class="muted">Average NPS</span></div>
    </div>

    <div class="card">
        <h3>Recent Completions</h3>
        <table>
            <thead>
            <tr><th>When</th><th>Portal</th><th>Guest</th><th>Status</th></tr>
            </thead>
            <tbody>
            @forelse($recentResponses as $session)
                <tr>
                    <td>{{ optional($session->survey_submitted_at)->toDateTimeString() }}</td>
                    <td>{{ $session->portal?->name }}</td>
                    <td>{{ $session->guest?->first_name }} ({{ $session->guest?->phone }})</td>
                    <td>{{ $session->status }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No submissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
