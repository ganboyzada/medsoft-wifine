@extends('layouts.app')
@php($title = 'Provision Organization')

@section('content')
    <div class="card">
        <h2>Provision Organization</h2>
        <p class="muted">Create tenant + admin account + starter portal in one action.</p>
        <form method="POST" action="{{ route('superadmin.organizations.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid-2">
                <div>
                    <label>Organization name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label>Slug (optional)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}">
                </div>
                <div>
                    <label>Legal name</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name') }}">
                </div>
                <div>
                    <label>{{ __('ui.timezone') }}</label>
                    <input type="text" name="timezone" value="{{ old('timezone', 'Asia/Baku') }}" required>
                </div>
                <div>
                    <label>{{ __('ui.default_language') }}</label>
                    <select name="default_language" required>
                        <option value="az" @selected(old('default_language', 'az') === 'az')>{{ __('ui.language_az') }}</option>
                        <option value="en" @selected(old('default_language') === 'en')>{{ __('ui.language_en') }}</option>
                    </select>
                </div>
                <div>
                    <label>Contact email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}">
                </div>
                <div>
                    <label>Contact phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}">
                </div>
                <div>
                    <label>Primary color</label>
                    <input type="text" name="primary_color" value="{{ old('primary_color', '#0F766E') }}">
                </div>
                <div>
                    <label>Accent color</label>
                    <input type="text" name="accent_color" value="{{ old('accent_color', '#0369A1') }}">
                </div>
            </div>

            <label>Organization logo</label>
            <input type="file" name="logo" accept="image/*">

            <hr style="margin: 18px 0; border:0; border-top: 1px solid #e5e7eb;">
            <h3>Initial Organization Admin</h3>
            <div class="grid-3">
                <div>
                    <label>Admin name</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required>
                </div>
                <div>
                    <label>Admin email</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required>
                </div>
                <div>
                    <label>Admin password</label>
                    <input type="text" name="admin_password" value="{{ old('admin_password') }}" required>
                </div>
            </div>

            <button class="btn" type="submit">Create Organization</button>
        </form>
    </div>
@endsection
