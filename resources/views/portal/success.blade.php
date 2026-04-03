<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.continue_to_internet') }}</title>
    <style>
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; display: grid; place-items: center; min-height: 100vh; background: linear-gradient(180deg,#ecfeff,#f8fafc); }
        .card { width: min(620px, 92vw); background: #fff; border: 1px solid #bae6fd; border-radius: 16px; padding: 24px; }
        h1 { color: #065f46; margin-top: 0; }
        .hint { color: #374151; }
        .campaign { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; margin-top: 14px; }
        .btn { display: inline-block; background: #0f766e; color: #fff; text-decoration: none; border-radius: 8px; padding: 10px 12px; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ __('ui.thanks_access_enabled') }}</h1>
    <p class="hint">{{ __('ui.session_token') }}: <code>{{ $session->session_token }}</code></p>
    <p class="hint">{{ __('ui.refresh_hint') }}</p>

    @if($campaign)
        <div class="campaign">
            <h3>{{ $campaign->title }}</h3>
            <p>{{ $campaign->body }}</p>
            @if($campaign->image_url)
                <img src="{{ $campaign->image_url }}" alt="campaign" style="max-width:100%;border-radius:8px;">
            @endif
            @if($campaign->cta_url && $campaign->cta_text)
                <p><a class="btn" href="{{ $campaign->cta_url }}" target="_blank">{{ $campaign->cta_text }}</a></p>
            @endif
        </div>
    @endif

    @if($portal->post_login_redirect_url)
        <p><a class="btn" href="{{ $portal->post_login_redirect_url }}">{{ __('ui.continue') }}</a></p>
    @endif
</div>
</body>
</html>
