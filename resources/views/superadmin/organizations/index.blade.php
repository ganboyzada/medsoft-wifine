@extends('layouts.app')
@php($title = 'Organizations')

@section('top_actions')
    <a class="btn" href="{{ route('superadmin.organizations.create') }}">+ New Organization</a>
@endsection

@section('content')
    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Users</th>
                <th>Portals</th>
                <th>Guests</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($organizations as $organization)
                <tr>
                    <td>{{ $organization->name }}</td>
                    <td>{{ $organization->status }}</td>
                    <td>{{ $organization->users_count }}</td>
                    <td>{{ $organization->portals_count }}</td>
                    <td>{{ $organization->guests_count }}</td>
                    <td><a href="{{ route('superadmin.organizations.show', $organization) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No organizations yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $organizations->links() }}
@endsection
