@extends('layouts.app')
@php($title = 'Organization Profile')

@section('top_actions')
    <a class="btn secondary" href="{{ route('superadmin.organizations.edit', $organization) }}">Edit</a>
@endsection

@section('content')
    <div class="card">
        <h2>{{ $organization->name }}</h2>
        <p class="muted">{{ $organization->slug }} | {{ $organization->timezone }} | {{ $organization->status }}</p>
        <p class="muted">{{ __('ui.default_language') }}: <strong>{{ strtoupper($organization->default_language ?? 'az') }}</strong></p>
        @if($organization->logo_path)
            <img src="{{ asset('storage/'.$organization->logo_path) }}" alt="logo" style="max-height:80px;border-radius:8px;">
        @endif
    </div>

    @if(session('integration_credentials'))
        <div class="card" style="border-color:#93c5fd;background:#eff6ff;">
            <h3>Gateway Credentials (copy now)</h3>
            <p><strong>Portal slug:</strong> {{ session('integration_credentials.portal_slug') }}</p>
            <p><strong>Portal key:</strong> <code>{{ session('integration_credentials.portal_key') }}</code></p>
            <p><strong>Portal secret:</strong> <code>{{ session('integration_credentials.portal_secret') }}</code></p>
            <p class="muted">Secret is encrypted at rest and not shown again automatically.</p>
        </div>
    @endif

    <div class="card">
        <h3>Users</h3>
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last login</th></tr></thead>
            <tbody>
            @foreach($organization->users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ optional($user->last_login_at)->toDateTimeString() ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Portals</h3>
        <table>
            <thead><tr><th>Name</th><th>Slug</th><th>Active</th><th>Vendor</th></tr></thead>
            <tbody>
            @foreach($organization->portals as $portal)
                <tr>
                    <td>{{ $portal->name }}</td>
                    <td>{{ $portal->slug }}</td>
                    <td>{{ $portal->is_active ? 'Yes' : 'No' }}</td>
                    <td>{{ $portal->network_vendor }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('superadmin.organizations.destroy', $organization) }}" onsubmit="return confirm('Archive this organization?');">
        @csrf
        @method('DELETE')
        <button class="btn light" type="submit">Archive Organization</button>
    </form>
@endsection
