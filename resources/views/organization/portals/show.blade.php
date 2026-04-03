@extends('layouts.app')
@php($title = 'Portal Details')

@section('top_actions')
    <a class="btn secondary" href="{{ route('organization.portals.edit', $portal) }}">Edit Portal</a>
@endsection

@section('content')
    <div class="card">
        <h2>{{ $portal->name }}</h2>
        <p><strong>Current survey template:</strong> {{ $portal->surveyTemplate?->name ?? 'Organization default template' }}</p>
        <p class="muted">Public URL: <a href="{{ route('portal.show', $portal) }}" target="_blank">{{ route('portal.show', $portal) }}</a></p>
        <p><strong>Integration key:</strong> <code>{{ $portal->integration_key }}</code></p>
        <p><strong>Integration secret:</strong> <code>{{ substr($portal->integration_secret, 0, 6) }}********{{ substr($portal->integration_secret, -4) }}</code></p>
        <p class="muted">Keep these secret and use HMAC headers in gateway API calls.</p>
    </div>

    <div class="card">
        <h3>Switch Survey Template</h3>
        @if($templates->count() > 0)
            <form method="POST" action="{{ route('organization.portals.template.update', $portal) }}">
                @csrf
                @method('PATCH')
                <label>Template for this portal</label>
                <select name="survey_template_id">
                    <option value="">Use organization default template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" @selected((string) $portal->survey_template_id === (string) $template->id)>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>
                <button class="btn secondary" type="submit">Update Template</button>
            </form>
        @else
            <p class="muted">No active templates found. Create one in Survey Builder first.</p>
        @endif
    </div>

    @if(session('integration_credentials'))
        <div class="card" style="border-color:#93c5fd;background:#eff6ff;">
            <h3>New Credentials (copy now)</h3>
            <p><strong>Portal key:</strong> <code>{{ session('integration_credentials.portal_key') }}</code></p>
            <p><strong>Portal secret:</strong> <code>{{ session('integration_credentials.portal_secret') }}</code></p>
        </div>
    @endif

    <div class="card">
        <h3>Recent Sessions</h3>
        <table>
            <thead><tr><th>Started</th><th>Status</th><th>Guest</th><th>Client MAC</th></tr></thead>
            <tbody>
            @forelse($portal->sessions as $session)
                <tr>
                    <td>{{ optional($session->started_at)->toDateTimeString() }}</td>
                    <td>{{ $session->status }}</td>
                    <td>{{ $session->guest?->first_name ?? '-' }}</td>
                    <td>{{ $session->client_mac ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No sessions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('organization.portals.destroy', $portal) }}" onsubmit="return confirm('Delete this portal?');">
        @csrf
        @method('DELETE')
        <button class="btn light" type="submit">Delete Portal</button>
    </form>
@endsection
