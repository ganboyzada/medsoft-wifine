@extends('layouts.app')
@php($title = __('ui.organization_settings'))

@section('content')
    <div class="card">
        <h2>{{ __('ui.organization_settings') }}</h2>
        <p class="muted">{{ __('ui.organization_settings_hint') }}</p>

        <form method="POST" action="{{ route('organization.settings.update') }}">
            @csrf
            @method('PATCH')

            <div class="grid-2">
                <div>
                    <label>{{ __('ui.default_language') }}</label>
                    <select name="default_language" required>
                        <option value="az" @selected(old('default_language', $organization->default_language) === 'az')>{{ __('ui.language_az') }}</option>
                        <option value="en" @selected(old('default_language', $organization->default_language) === 'en')>{{ __('ui.language_en') }}</option>
                    </select>
                </div>
                <div>
                    <label>{{ __('ui.timezone') }}</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $organization->timezone) }}" required>
                </div>
            </div>

            <button class="btn" type="submit">{{ __('ui.save_changes') }}</button>
        </form>
    </div>
@endsection
