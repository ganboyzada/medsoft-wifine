@extends('layouts.app')
@php($title = 'Edit Portal')

@section('content')
    <div class="card">
        <h2>Edit Portal</h2>
        <form method="POST" action="{{ route('organization.portals.update', $portal) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid-2">
                <div>
                    <label>Name</label>
                    <input name="name" value="{{ old('name', $portal->name) }}" required>
                </div>
                <div>
                    <label>Slug</label>
                    <input name="slug" value="{{ old('slug', $portal->slug) }}" required>
                </div>
                <div>
                    <label>Survey Template</label>
                    <select name="survey_template_id">
                        <option value="">Use organization default</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected((string) old('survey_template_id', $portal->survey_template_id) === (string) $template->id)>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Vendor</label>
                    <input name="network_vendor" value="{{ old('network_vendor', $portal->network_vendor) }}" required>
                </div>
                <div>
                    <label>Session TTL minutes</label>
                    <input type="number" min="30" max="1440" name="session_ttl_minutes" value="{{ old('session_ttl_minutes', $portal->session_ttl_minutes) }}" required>
                </div>
                <div>
                    <label>Redirect URL</label>
                    <input name="post_login_redirect_url" value="{{ old('post_login_redirect_url', $portal->post_login_redirect_url) }}">
                </div>
            </div>
            <label>Welcome title</label>
            <input name="welcome_title" value="{{ old('welcome_title', $portal->welcome_title) }}" required>
            <label>Welcome text</label>
            <textarea name="welcome_text">{{ old('welcome_text', $portal->welcome_text) }}</textarea>
            <label>Terms text</label>
            <textarea name="terms_text">{{ old('terms_text', $portal->terms_text) }}</textarea>

            <label>Replace logo override</label>
            <input type="file" name="logo_override" accept="image/*">
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="require_marketing_consent" value="1" @checked(old('require_marketing_consent', $portal->require_marketing_consent))>
                Require marketing consent
            </label>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="is_active" value="1" @checked(old('is_active', $portal->is_active))>
                Portal active
            </label>
            <button class="btn" type="submit">Save Portal</button>
        </form>
    </div>
@endsection
