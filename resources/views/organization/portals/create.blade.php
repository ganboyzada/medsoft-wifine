@extends('layouts.app')
@php($title = 'Create Portal')

@section('content')
    <div class="card">
        <h2>Create Guest WiFi Portal</h2>
        <form method="POST" action="{{ route('organization.portals.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid-2">
                <div>
                    <label>Name</label>
                    <input name="name" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label>Slug (optional)</label>
                    <input name="slug" value="{{ old('slug') }}">
                </div>
                <div>
                    <label>Survey Template</label>
                    <select name="survey_template_id">
                        <option value="">Use organization default</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected((string) old('survey_template_id') === (string) $template->id)>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Vendor</label>
                    <select name="network_vendor">
                        @foreach(['custom','mikrotik','unifi','aruba','cisco'] as $vendor)
                            <option value="{{ $vendor }}" @selected(old('network_vendor','custom')===$vendor)>{{ ucfirst($vendor) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Session TTL minutes</label>
                    <input type="number" min="30" max="1440" name="session_ttl_minutes" value="{{ old('session_ttl_minutes', 120) }}" required>
                </div>
                <div>
                    <label>Redirect URL after internet grant (optional)</label>
                    <input name="post_login_redirect_url" value="{{ old('post_login_redirect_url') }}">
                </div>
            </div>

            <label>Welcome title</label>
            <input name="welcome_title" value="{{ old('welcome_title', 'Welcome to Guest WiFi') }}" required>
            <label>Welcome text</label>
            <textarea name="welcome_text">{{ old('welcome_text') }}</textarea>
            <label>Terms text</label>
            <textarea name="terms_text">{{ old('terms_text') }}</textarea>

            <label>Portal logo override</label>
            <input type="file" name="logo_override" accept="image/*">

            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="require_marketing_consent" value="1" @checked(old('require_marketing_consent'))>
                Require marketing consent checkbox
            </label>

            <button class="btn" type="submit">Create Portal</button>
        </form>
    </div>
@endsection
