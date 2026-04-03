@extends('layouts.app')
@php($title = 'Create Survey Template')

@section('content')
    <div class="card">
        <h2>Create Survey Template</h2>
        <form method="POST" action="{{ route('organization.surveys.store') }}">
            @csrf
            <label>Name</label>
            <input name="name" value="{{ old('name') }}" required>
            <label>Description</label>
            <input name="description" value="{{ old('description') }}">
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="is_default" value="1" @checked(old('is_default'))>
                Set as default template
            </label>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="is_active" value="1" @checked(old('is_active', true))>
                Active template
            </label>
            <button class="btn" type="submit">Create Template</button>
        </form>
    </div>
@endsection
