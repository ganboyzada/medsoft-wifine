@extends('layouts.app')
@php($title = 'Campaigns')

@section('top_actions')
    <a class="btn" href="{{ route('organization.campaigns.create') }}">+ New Campaign</a>
@endsection

@section('content')
    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Display Rule</th><th>Active</th><th>Window</th><th></th></tr></thead>
            <tbody>
            @forelse($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->name }}</td>
                    <td>{{ $campaign->display_rule }}</td>
                    <td>{{ $campaign->is_active ? 'Yes' : 'No' }}</td>
                    <td>{{ optional($campaign->starts_at)->toDateString() ?? '-' }} to {{ optional($campaign->ends_at)->toDateString() ?? '-' }}</td>
                    <td><a href="{{ route('organization.campaigns.edit', $campaign) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No campaigns configured.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $campaigns->links() }}
@endsection
