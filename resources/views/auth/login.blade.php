<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.sign_in') }} | {{ __('ui.app_name') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: radial-gradient(circle at top right, #dbeafe, #eef2ff 45%, #f8fafc); font-family: "Segoe UI", Arial, sans-serif; }
        .card { width: min(460px, 92vw); background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 12px 32px rgba(2, 8, 23, .08); }
        h1 { margin-top: 0; }
        input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; margin-top: 6px; margin-bottom: 12px; }
        button { width: 100%; border: 0; background: #744487; color: #fff; border-radius: 8px; padding: 12px; font-weight: 650; cursor: pointer; }
        .err { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 8px; padding: 10px; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ __('ui.sign_in') }}</h1>
    <p>{{ __('ui.sign_in_subtitle') }}</p>

    @if($errors->any())
        <div class="err">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <label>{{ __('ui.email') }}</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>{{ __('ui.password') }}</label>
        <input type="password" name="password" required>

        <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
            <input type="checkbox" name="remember" value="1" style="width:auto;margin:0;">
            {{ __('ui.keep_me_signed_in') }}
        </label>

        <button type="submit">{{ __('ui.sign_in') }}</button>
    </form>
</div>
</body>
</html>
