@extends('layouts.app')
@php($title = 'Edit Organization')

@section('content')
    <div class="card">
        <h2>Edit Organization</h2>
        <form method="POST" action="{{ route('superadmin.organizations.update', $organization) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid-2">
                <div>
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" required>
                </div>
                <div>
                    <label>Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $organization->slug) }}" required>
                </div>
                <div>
                    <label>Legal name</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name', $organization->legal_name) }}">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="active" @selected(old('status', $organization->status)==='active')>Active</option>
                        <option value="suspended" @selected(old('status', $organization->status)==='suspended')>Suspended</option>
                    </select>
                </div>
                <div>
                    <label>Contact email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $organization->contact_email) }}">
                </div>
                <div>
                    <label>Contact phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $organization->contact_phone) }}">
                </div>
                <div>
                    <label>{{ __('ui.timezone') }}</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $organization->timezone) }}" required>
                </div>
                <div>
                    <label>{{ __('ui.default_language') }}</label>
                    <select name="default_language" required>
                        <option value="az" @selected(old('default_language', $organization->default_language) === 'az')>{{ __('ui.language_az') }}</option>
                        <option value="en" @selected(old('default_language', $organization->default_language) === 'en')>{{ __('ui.language_en') }}</option>
                    </select>
                </div>
                <div>
                    <label>Primary color</label>
                    <input type="text" name="primary_color" value="{{ old('primary_color', $organization->primary_color) }}">
                </div>
                <div>
                    <label>Accent color</label>
                    <input type="text" name="accent_color" value="{{ old('accent_color', $organization->accent_color) }}">
                </div>
            </div>
            <label>Replace logo</label>
            <input type="file" name="logo" accept="image/*">
            <button class="btn" type="submit">Save changes</button>
        </form>
    </div>
@endsection
