@extends('layouts.app')
@php($title = 'Survey Responses')

@section('top_actions')
    <a class="btn secondary" href="{{ route('organization.responses.export') }}">Export CSV</a>
@endsection

@section('content')
    <div class="card">
        <table>
            <thead><tr><th>Submitted</th><th>Portal</th><th>Template</th><th>Guest</th><th>Sentiment</th></tr></thead>
            <tbody>
            @forelse($responses as $response)
                <tr>
                    <td>{{ optional($response->submitted_at)->toDateTimeString() }}</td>
                    <td>{{ $response->portal?->name }}</td>
                    <td>{{ $response->template?->name }}</td>
                    <td>{{ $response->guest?->first_name }} ({{ $response->guest?->phone }})</td>
                    <td>{{ $response->sentiment_score ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No responses yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $responses->links() }}
@endsection
