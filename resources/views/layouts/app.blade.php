<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('ui.app_name') }}</title>
    <style>
        :root {
            --brand: {{ ($organization ?? null)?->primary_color ?? '#0F766E' }};
            --accent: {{ ($organization ?? null)?->accent_color ?? '#0369A1' }};
            --bg: #f4f7fb;
            --text: #1f2937;
            --muted: #6b7280;
            --surface: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: var(--bg); color: var(--text); }
        .shell { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
        .sidebar { background: linear-gradient(180deg, var(--brand), #0b3d4d); color: #fff; padding: 20px; }
        .sidebar h1 { margin-top: 0; font-size: 20px; }
        .sidebar a { color: #fff; text-decoration: none; display: block; padding: 8px 0; opacity: .92; }
        .sidebar a:hover { opacity: 1; }
        .main { padding: 24px; }
        .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .btn { background: var(--brand); color: #fff; border: 0; border-radius: 8px; padding: 10px 14px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn.secondary { background: var(--accent); }
        .btn.light { background: #e5e7eb; color: #111827; }
        input, select, textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; margin-top: 6px; margin-bottom: 12px; }
        label { font-size: 14px; color: var(--muted); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .flash { border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; }
        .flash.ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .flash.err { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .muted { color: var(--muted); }
        @media (max-width: 920px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: sticky; top: 0; z-index: 10; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <h1>{{ __('ui.app_name') }}</h1>
        @auth
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('superadmin.organizations.index') }}">{{ __('ui.organizations') }}</a>
                <a href="{{ route('superadmin.organizations.create') }}">{{ __('ui.provision_new_tenant') }}</a>
            @else
                <a href="{{ route('organization.dashboard') }}">{{ __('ui.dashboard') }}</a>
                <a href="{{ route('organization.portals.index') }}">{{ __('ui.portals') }}</a>
                <a href="{{ route('organization.surveys.index') }}">{{ __('ui.survey_builder') }}</a>
                <a href="{{ route('organization.guests.index') }}">{{ __('ui.guests') }}</a>
                <a href="{{ route('organization.reports.customers') }}">{{ __('ui.customer_reports') }}</a>
                <a href="{{ route('organization.responses.index') }}">{{ __('ui.responses') }}</a>
                <a href="{{ route('organization.campaigns.index') }}">{{ __('ui.campaigns') }}</a>
                <a href="{{ route('organization.settings.edit') }}">{{ __('ui.settings') }}</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="margin-top: 24px;">
                @csrf
                <button class="btn light" type="submit">{{ __('ui.sign_out') }}</button>
            </form>
        @endauth
    </aside>
    <main class="main">
        <div class="top">
            <div>
                <strong>{{ $title ?? __('ui.dashboard') }}</strong><br>
                <span class="muted">{{ ($organization ?? null)?->name ?? __('ui.platform_management') }}</span>
            </div>
            @yield('top_actions')
        </div>

        @if(session('status'))
            <div class="flash ok">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="flash err">
                <strong>{{ __('ui.please_fix') }}</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
