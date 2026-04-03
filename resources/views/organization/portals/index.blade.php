@extends('layouts.app')
@php($title = 'Portals')

@section('top_actions')
    <a class="btn" href="{{ route('organization.portals.create') }}">+ New Portal</a>
@endsection

@section('content')
    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Template</th>
                <th>Vendor</th>
                <th>Active</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($portals as $portal)
                <tr>
                    <td>{{ $portal->name }}</td>
                    <td><code>{{ $portal->slug }}</code></td>
                    <td>{{ $portal->surveyTemplate?->name ?? 'Default tenant template' }}</td>
                    <td>{{ $portal->network_vendor }}</td>
                    <td>{{ $portal->is_active ? 'Yes' : 'No' }}</td>
                    <td><a href="{{ route('organization.portals.show', $portal) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No portals created yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $portals->links() }}
@endsection
