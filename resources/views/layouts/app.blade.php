<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('ui.app_name') }}</title>
    @php
        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
        $basePrimary = '#925aa5';
        $sidebarBrand = $isSuperAdmin ? '#925aa5' : (($organization ?? null)?->primary_color ?? '#925aa5');
        $sidebarAccent = $isSuperAdmin ? '#6f3e84' : (($organization ?? null)?->accent_color ?? '#7a4a8d');
    @endphp
    <style>
        :root {
            --brand: {{ $sidebarBrand }};
            --accent: {{ $sidebarAccent }};
            --theme-primary: {{ $basePrimary }};
            --theme-primary-dark: #744487;
            --theme-primary-light: #b07cc0;
            --bg: #f4f7fb;
            --text: #1f2937;
            --muted: #6b7280;
            --surface: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: var(--bg); color: var(--text); }
        .shell { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
        .sidebar { background: linear-gradient(180deg, var(--brand), #0b3d4d); color: #fff; padding: 20px; display: flex; flex-direction: column; min-height: 100vh; }
        .sidebar .brand-logo { display: block; margin: 0 0 16px 0; }
        .sidebar .brand-logo img { display: block; width: 170px; max-width: 100%; height: auto; }
        .sidebar .sidebar-nav { display: block; }
        .sidebar a { color: #fff; text-decoration: none; display: block; padding: 8px 0; opacity: .92; }
        .sidebar a:hover { opacity: 1; }
        .sidebar .logout-form { margin-top: auto; padding-top: 18px; }
        .sidebar .logout-btn {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(0, 0, 0, 0.18);
            color: #fff;
            border-radius: 10px;
            padding: 10px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 650;
            cursor: pointer;
            transition: all .2s ease;
        }
        .sidebar .logout-btn:hover {
            background: rgba(0, 0, 0, 0.28);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .sidebar .logout-btn svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
            flex: 0 0 auto;
        }
        .main { padding: 24px; }
        .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .btn { background: var(--theme-primary); color: #ffffff; border: 0; border-radius: 8px; padding: 10px 14px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 650; }
        .btn.secondary { background: var(--theme-primary-dark); }
        .btn.light { background: #eceff3; color: #1b2430; border: 1px solid #c6cfdb; }
        input, select, textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; margin-top: 6px; margin-bottom: 12px; }
        label { font-size: 14px; color: #6e4a7d; }
        .main strong, .main h1, .main h2, .main h3 { color: var(--theme-primary-dark); }
        .main a { color: var(--theme-primary-dark); }
        .main a:hover { color: var(--theme-primary); }
        .main a.btn, .main a.btn:visited, .main a.btn:hover { color: #ffffff; }
        .main a.btn.light, .main a.btn.light:visited, .main a.btn.light:hover { color: #1b2430; }
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
        <a href="{{ route('dashboard') }}" class="brand-logo" aria-label="{{ __('ui.app_name') }}">
            <img src="{{ asset('assets/wifine_logo.svg') }}" alt="{{ __('ui.app_name') }}">
        </a>
        @auth
            <div class="sidebar-nav">
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
            </div>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button class="logout-btn" type="submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5v-2H5V5h5V3Zm5.71 4.29L14.29 8.7 16.59 11H9v2h7.59l-2.3 2.29 1.42 1.41L20.41 12l-4.7-4.71Z"/>
                    </svg>
                    <span>{{ __('ui.sign_out') }}</span>
                </button>
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
