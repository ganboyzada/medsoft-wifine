@extends('layouts.app')
@php($title = 'Create Campaign')

@section('content')
    <div class="card">
        <h2>Create Campaign</h2>
        <form method="POST" action="{{ route('organization.campaigns.store') }}">
            @csrf
            @include('organization.campaigns.partials.form', ['campaign' => null])
            <button class="btn" type="submit">Create Campaign</button>
        </form>
    </div>
@endsection
