@extends('layouts.app')
@php($title = 'Edit Campaign')

@section('content')
    <div class="card">
        <h2>Edit Campaign</h2>
        <form method="POST" action="{{ route('organization.campaigns.update', $campaign) }}">
            @csrf
            @method('PUT')
            @include('organization.campaigns.partials.form', ['campaign' => $campaign])
            <button class="btn" type="submit">Save Campaign</button>
        </form>
    </div>

    <form method="POST" action="{{ route('organization.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this campaign?');">
        @csrf
        @method('DELETE')
        <button class="btn light" type="submit">Delete Campaign</button>
    </form>
@endsection
