<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $portal->welcome_title }}</title>
    <style>
        :root { --brand: {{ $portal->organization->primary_color ?? '#0F766E' }}; --accent: {{ $portal->organization->accent_color ?? '#0369A1' }}; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: linear-gradient(180deg, #ecfeff, #f8fafc); }
        .wrap { max-width: 760px; margin: 0 auto; padding: 16px; }
        .card { background: #fff; border: 1px solid #dbeafe; border-radius: 14px; padding: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, .07); }
        h1 { margin-top: 0; color: var(--brand); }
        input, select, textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin: 6px 0 12px 0; }
        .btn { width: 100%; border: 0; background: var(--brand); color: #ffffff; border-radius: 10px; padding: 13px; font-size: 16px; font-weight: 650; }
        .muted { color: #6b7280; }
        .error { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @if($portal->logo_override_path)
            <img src="{{ asset('storage/'.$portal->logo_override_path) }}" alt="portal logo" style="max-height: 70px;">
        @elseif($portal->organization->logo_path)
            <img src="{{ asset('storage/'.$portal->organization->logo_path) }}" alt="org logo" style="max-height: 70px;">
        @endif
        <h1>{{ $portal->welcome_title }}</h1>
        <p class="muted">{{ $portal->welcome_text }}</p>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('portal.submit', $portal) }}">
            @csrf
            <input type="hidden" name="session_token" value="{{ old('session_token', $session->session_token) }}">

            <label>{{ __('ui.first_name') }}</label>
            <input name="first_name" value="{{ old('first_name', $session->guest?->first_name) }}" required>

            <label>{{ __('ui.phone_number') }}</label>
            <input name="phone" value="{{ old('phone', $session->guest?->phone ?? $knownPhone) }}" required>

            <label>{{ __('ui.gender_optional') }}</label>
            <select name="gender">
                <option value="">{{ __('ui.prefer_not_to_say') }}</option>
                <option value="male" @selected(old('gender')==='male')>{{ __('ui.male') }}</option>
                <option value="female" @selected(old('gender')==='female')>{{ __('ui.female') }}</option>
                <option value="non_binary" @selected(old('gender')==='non_binary')>{{ __('ui.non_binary') }}</option>
                <option value="prefer_not_to_say" @selected(old('gender')==='prefer_not_to_say')>{{ __('ui.prefer_not_to_say') }}</option>
            </select>

            <hr style="border:0;border-top:1px solid #e5e7eb; margin: 18px 0;">
            <h3>{{ __('ui.quick_satisfaction_survey') }}</h3>

            @foreach($template->questions as $question)
                <label>{{ $question->label }} @if($question->is_required)<span style="color:#dc2626">*</span>@endif</label>
                @switch($question->type)
                    @case('short_text')
                        <input name="answers[{{ $question->id }}]" value="{{ old('answers.'.$question->id) }}" placeholder="{{ $question->placeholder }}">
                        @break
                    @case('long_text')
                        <textarea name="answers[{{ $question->id }}]" placeholder="{{ $question->placeholder }}">{{ old('answers.'.$question->id) }}</textarea>
                        @break
                    @case('single_choice')
                        <select name="answers[{{ $question->id }}]">
                            <option value="">{{ __('ui.select_one') }}</option>
                            @foreach($question->options ?? [] as $option)
                                <option value="{{ $option }}" @selected(old('answers.'.$question->id)===$option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('multi_choice')
                        @foreach($question->options ?? [] as $option)
                            <label style="display:flex;gap:8px;align-items:center;">
                                <input type="checkbox" style="width:auto;" name="answers[{{ $question->id }}][]" value="{{ $option }}"
                                       @checked(in_array($option, old('answers.'.$question->id, []), true))>
                                {{ $option }}
                            </label>
                        @endforeach
                        @break
                    @case('rating')
                        <select name="answers[{{ $question->id }}]">
                            <option value="">{{ __('ui.select_rating') }}</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected((string) old('answers.'.$question->id) === (string) $i)>{{ $i }}/5</option>
                            @endfor
                        </select>
                        @break
                    @case('nps')
                        <select name="answers[{{ $question->id }}]">
                            <option value="">{{ __('ui.select_score') }}</option>
                            @for($i = 0; $i <= 10; $i++)
                                <option value="{{ $i }}" @selected((string) old('answers.'.$question->id) === (string) $i)>{{ $i }}</option>
                            @endfor
                        </select>
                        @break
                    @case('yes_no')
                        <select name="answers[{{ $question->id }}]">
                            <option value="">{{ __('ui.select') }}</option>
                            <option value="1" @selected(old('answers.'.$question->id)==='1')>{{ __('ui.yes') }}</option>
                            <option value="0" @selected(old('answers.'.$question->id)==='0')>{{ __('ui.no') }}</option>
                        </select>
                        @break
                    @case('phone')
                        <input name="answers[{{ $question->id }}]" value="{{ old('answers.'.$question->id) }}">
                        @break
                    @case('date')
                        <input type="date" name="answers[{{ $question->id }}]" value="{{ old('answers.'.$question->id) }}">
                        @break
                @endswitch
            @endforeach

            @if($portal->require_marketing_consent)
                <label style="display:flex;gap:8px;align-items:center;">
                    <input type="checkbox" style="width:auto;" name="consent_marketing" value="1" @checked(old('consent_marketing'))>
                    {{ __('ui.agree_promotions') }}
                </label>
            @else
                <input type="hidden" name="consent_marketing" value="0">
            @endif

            <label style="display:flex;gap:8px;align-items:center; margin-top:8px;">
                <input type="checkbox" style="width:auto;" name="consent_terms" value="1" required @checked(old('consent_terms'))>
                {{ __('ui.accept_terms_and_privacy') }}
            </label>

            @if($portal->terms_text)
                <p class="muted" style="font-size:13px;">{{ $portal->terms_text }}</p>
            @endif

            <button class="btn" type="submit">{{ __('ui.continue_to_internet') }}</button>
        </form>
    </div>
</div>
</body>
</html>
