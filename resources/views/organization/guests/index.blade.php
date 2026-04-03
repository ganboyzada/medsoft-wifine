@extends('layouts.app')
@php($title = 'Guests')

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('organization.guests.index') }}">
            <label>Search by name or phone</label>
            <input name="q" value="{{ $search }}">
            <button class="btn secondary" type="submit">Search</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Phone</th><th>Gender</th><th>Last seen</th><th></th></tr></thead>
            <tbody>
            @forelse($guests as $guest)
                <tr>
                    <td>{{ $guest->first_name }}</td>
                    <td>{{ $guest->phone }}</td>
                    <td>{{ $guest->gender ?? '-' }}</td>
                    <td>{{ optional($guest->last_seen_at)->toDateTimeString() }}</td>
                    <td><a href="{{ route('organization.guests.show', $guest) }}">Details</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No guests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $guests->links() }}
@endsection
