@extends('layouts.app')
@php($title = 'Guest Profile')

@section('content')
    <div class="card">
        <h2>{{ $guest->first_name }}</h2>
        <p class="muted">{{ $guest->phone }} | {{ $guest->gender ?? 'N/A' }}</p>
        <p>First seen: {{ optional($guest->first_seen_at)->toDateTimeString() }}</p>
        <p>Last seen: {{ optional($guest->last_seen_at)->toDateTimeString() }}</p>
        <p>Marketing consent: {{ $guest->consent_marketing ? 'Yes' : 'No' }}</p>
    </div>

    <div class="card">
        <h3>Recent Sessions</h3>
        <table>
            <thead><tr><th>Started</th><th>Status</th><th>Portal</th><th>Client MAC</th></tr></thead>
            <tbody>
            @foreach($guest->sessions as $session)
                <tr>
                    <td>{{ optional($session->started_at)->toDateTimeString() }}</td>
                    <td>{{ $session->status }}</td>
                    <td>{{ $session->portal?->name }}</td>
                    <td>{{ $session->client_mac ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Latest Answers</h3>
        @foreach($guest->responses as $response)
            <div style="padding:12px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:10px;">
                <strong>{{ optional($response->submitted_at)->toDateTimeString() }}</strong><br>
                @foreach($response->answers as $answer)
                    <div style="margin-top:6px;">
                        <span class="muted">{{ $answer->question?->label }}:</span>
                        @if($answer->answer_text !== null)
                            {{ $answer->answer_text }}
                        @elseif($answer->answer_number !== null)
                            {{ $answer->answer_number }}
                        @elseif($answer->answer_boolean !== null)
                            {{ $answer->answer_boolean ? 'Yes' : 'No' }}
                        @else
                            {{ implode(', ', $answer->answer_json ?? []) }}
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
