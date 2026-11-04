<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SZG3N3VKYE"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-SZG3N3VKYE');
    </script>
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NBPW3VHH');</script>
    <!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script type="text/javascript">
        (function (c, l, a, r, i, t, y) {
            c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments) };
            t = l.createElement(r); t.async = 1; t.src = "https://www.clarity.ms/tag/" + i;
            y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
        })(window, document, "clarity", "script", "ycju085szi");
    </script>
    <title>@yield('title', 'Admin') — Darul Ifta Admin Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;600;700&family=Noto+Nastaliq+Urdu:wght@400;600;700&family=Noto+Serif+Malayalam:wght@400;500;600;700&family=Noto+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app-fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/fevi.png') }}">

    {{-- PWA Meta --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#102B43">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo/fevi.png') }}">

    <style>
        :root {
            --sidebar-w: 260px;
            --brand-navy-dark: #102B43;
            --brand-navy-active: #0D2338;
            --workspace-bg: #F4F7FB;
            --card-border: #E2E8F0;
            --primary-blue: #2563EB;
            --primary-blue-hover: #1D4ED8;
            --text-heading: #172033;
            --text-sub: #64748B;
            --admin-footer-h: 42px;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--workspace-bg);
            color: var(--text-heading);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Modern Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--brand-navy-dark);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1060;
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .sidebar-brand-box {
            padding: 1.4rem 1.25rem 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }

        .sidebar-brand-box::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 1.25rem;
            width: 50px;
            height: 2px;
            background: #EAB308;
            border-radius: 2px;
        }

        .sidebar-section-title {
            padding: 1.25rem 1.25rem 0.4rem;
            font-size: 0.68rem;
            text-transform: uppercase;
            color: #829ab1;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        .sidebar-nav {
            padding: 0.5rem 0.65rem 2rem;
        }

        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.72);
            padding: 0.62rem 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.88rem;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 2px;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
            position: relative;
        }

        .sidebar-nav .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar-nav .nav-link.active {
            color: #ffffff;
            background: rgba(37, 99, 235, 0.18);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.15rem;
            width: 22px;
            text-align: center;
            opacity: 0.85;
            color: #94A3B8;
        }

        .sidebar-nav .nav-link:hover i,
        .sidebar-nav .nav-link.active i {
            color: #ffffff;
            opacity: 1;
        }

        /* Submenu Styling */
        .sidebar-submenu {
            padding-left: 0.5rem;
            margin: 2px 0 6px;
        }

        .sidebar-submenu .nav-link {
            padding: 0.5rem 0.85rem 0.5rem 2.4rem;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.65);
        }

        .sidebar-submenu .nav-link i {
            font-size: 0.95rem;
            width: 18px;
            opacity: 0.7;
        }

        .sidebar-submenu .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }

        .sidebar-submenu .nav-link.active {
            color: #10B981 !important;
            background: rgba(16, 185, 129, 0.1) !important;
            font-weight: 600;
        }

        .sidebar-submenu .nav-link.active i {
            color: #10B981 !important;
        }

        .sidebar-submenu .nav-link.active::before {
            content: '●';
            color: #10B981;
            font-size: 0.75rem;
            position: absolute;
            left: 1.15rem;
        }

        .collapse-caret {
            transition: transform 0.2s ease;
            margin-left: auto;
            font-size: 0.75rem !important;
            opacity: 0.6;
        }

        .nav-link[aria-expanded="true"] .collapse-caret {
            transform: rotate(180deg);
        }

        /* ── Sidebar Count Badges ── */
        .sidebar-count-pill {
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
            letter-spacing: 0.2px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            line-height: 1.2;
            transition: all 0.15s ease;
        }

        .sidebar-nav .nav-link:hover .sidebar-count-pill,
        .sidebar-nav .nav-link.active .sidebar-count-pill {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
        }

        .sidebar-sub-pill {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.68rem;
            font-weight: 600;
            padding: 1.5px 6px;
            border-radius: 8px;
            line-height: 1.2;
        }

        .sidebar-sub-pill-amber {
            background: rgba(217, 119, 6, 0.25);
            color: #FCD34D;
            border: 1px solid rgba(217, 119, 6, 0.35);
            font-size: 0.68rem;
            font-weight: 700;
            padding: 1.5px 6px;
            border-radius: 8px;
            line-height: 1.2;
        }

        .sidebar-sub-pill-emerald {
            background: rgba(16, 185, 129, 0.2);
            color: #6EE7B7;
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-size: 0.68rem;
            font-weight: 600;
            padding: 1.5px 6px;
            border-radius: 8px;
            line-height: 1.2;
        }

        .sidebar-count-pill-live {
            background: rgba(16, 185, 129, 0.18);
            color: #34D399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
            line-height: 1.2;
        }

        .sidebar-pulse-dot {
            width: 5px;
            height: 5px;
            background-color: #34D399;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
            animation: pulse-green 2s infinite;
        }

        .nav-link .sidebar-count-pill + .collapse-caret {
            margin-left: 0.35rem;
        }

        /* ── Main Content Container ── */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── Modern Topbar Header ── */
        .modern-topbar {
            background: #ffffff;
            border-bottom: 1px solid var(--card-border);
            padding: 0.85rem 1.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .header-title-box {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .header-page-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #ECFDF5;
            color: #059669;
            border: 1px solid #D1FAE5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .header-title-text h4 {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
            color: #0F172A;
            line-height: 1.2;
        }

        .header-breadcrumbs {
            font-size: 0.78rem;
            color: var(--text-sub);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .header-breadcrumbs a {
            color: var(--text-sub);
            text-decoration: none;
            transition: color 0.15s;
        }

        .header-breadcrumbs a:hover {
            color: var(--primary-blue);
        }

        /* Information Pills in Header */
        .header-pill-capsule {
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: #1E293B;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
            white-space: nowrap;
        }

        .header-pill-capsule i {
            color: var(--primary-blue);
            font-size: 0.95rem;
        }

        /* ── Workspace Page Content ── */
        .page-content-wrapper {
            padding: 1.75rem 1.85rem;
            padding-bottom: calc(var(--admin-footer-h) + 1.75rem) !important;
            flex-grow: 1;
        }

        /* ── Global Fixed Footer ── */
        .admin-global-fixed-footer {
            position: fixed;
            bottom: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--admin-footer-h);
            background: #FFFFFF;
            border-top: 1px solid var(--card-border);
            z-index: 1020;
            display: flex;
            align-items: center;
            padding: 0 1.85rem;
            font-size: 0.78rem;
            color: var(--text-sub);
            box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.02);
            transition: left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admin-footer-inner {
            width: 100%;
        }

        .developer-credit-badge {
            display: inline-flex;
            align-items: center;
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
            border-radius: 6px;
            padding: 2px 8px;
            font-weight: 700;
            font-size: 0.75rem;
            line-height: 1.3;
            letter-spacing: 0.2px;
        }

        @media print {
            .admin-global-fixed-footer {
                display: none !important;
            }
        }

        /* ── Modern Cards & Panels ── */
        .modern-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
        }

        /* ── Metric Summary Tiles (Reference Design) ── */
        .metric-card-tile {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
        }

        .metric-card-tile:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .metric-icon-square {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .metric-val-num {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.1;
            margin: 0;
        }

        .metric-label-txt {
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-sub);
            margin-top: 3px;
        }

        .metric-trend-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        /* ── Modern Action Buttons ── */
        .btn-modern-primary {
            background-color: var(--primary-blue);
            color: #ffffff !important;
            border: 1px solid var(--primary-blue);
            border-radius: 10px;
            padding: 0.45rem 1.15rem;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.18);
        }

        .btn-modern-primary:hover {
            background-color: var(--primary-blue-hover);
            border-color: var(--primary-blue-hover);
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.28);
        }

        .btn-modern-secondary {
            background-color: #ffffff;
            color: #475569 !important;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            padding: 0.45rem 1rem;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-modern-secondary:hover {
            background-color: #F8FAFC;
            color: #1E293B !important;
            border-color: #94A3B8;
        }

        /* ── Modern Controls ── */
        .modern-input,
        .modern-select {
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            padding: 0.48rem 0.85rem;
            font-size: 0.86rem;
            color: #1E293B;
            background-color: #ffffff;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .modern-input:focus,
        .modern-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
            outline: none;
        }

        /* ── Clean Data Tables ── */
        .modern-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table thead th {
            background: #F8FAFC;
            color: #64748B;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #E2E8F0;
            border-top: none;
            white-space: nowrap;
        }

        .modern-table tbody td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #F1F5F9;
            color: #1E293B;
            font-size: 0.86rem;
            vertical-align: middle;
            background: #ffffff;
            transition: background 0.12s;
        }

        .modern-table tbody tr:hover td {
            background: #F8FAFC;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Monogram Avatar */
        .avatar-monogram {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        /* Multilingual typography */
        .font-malayalam { font-family: 'Noto Serif Malayalam', 'Gayathri', serif !important; }
        .font-arabic { font-family: 'Noto Naskh Arabic', 'Amiri', serif !important; direction: rtl; }
        .font-urdu { font-family: 'Noto Nastaliq Urdu', serif !important; direction: rtl; }

        /* Mobile responsiveness */
        @media (max-width: 991px) {
            :root {
                --sidebar-w: 260px;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .admin-global-fixed-footer {
                left: 0;
                padding: 0 1rem;
                height: auto;
                min-height: var(--admin-footer-h);
                padding-top: 0.35rem;
                padding-bottom: 0.35rem;
            }

            .page-content-wrapper {
                padding-bottom: calc(var(--admin-footer-h) + 2.5rem) !important;
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(2px);
                z-index: 1050;
                display: none;
            }

            body.sidebar-open .sidebar-overlay {
                display: block;
            }

            .modern-topbar {
                padding: 0.75rem 1rem;
            }

            .page-content-wrapper {
                padding: 1rem;
            }
        }
    </style>
    <!-- Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    @stack('styles')
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NBPW3VHH" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    {{-- Left Navy Sidebar --}}
    <aside class="sidebar">
        {{-- Branding --}}
        <div class="sidebar-brand-box">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2.5 text-decoration-none">
                <img src="{{ asset('assets/logo/fevi.png') }}" alt="Darul Ifta Logo" width="38" height="38" class="rounded-circle shadow-xs flex-shrink-0">
                <div class="overflow-hidden">
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="text-white fw-bold" style="letter-spacing: 0.5px; font-size: 0.95rem;">DARUL IFTA</span>
                        <span class="text-white opacity-70" style="font-family: 'Amiri', serif; font-size: 0.95rem;">دار الإفتاء</span>
                    </div>
                    <div class="text-white opacity-50" style="font-size: 0.65rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        The Center for Islamic Research & Guidance
                    </div>
                </div>
            </a>
            <div class="d-flex align-items-center justify-content-between mt-2 pt-1">
                <span class="text-white small fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.3px;">Admin Console</span>
            </div>
        </div>

        {{-- Navigation Menu --}}
        <nav class="sidebar-nav">
            <div class="sidebar-section-title">MAIN</div>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>

            <div class="sidebar-section-title">QUESTIONS</div>
            @php
                $isQuestionsActive = request()->routeIs('admin.questions*') || request()->routeIs('admin.fatwas*');
                $currentStatus = request('status');
                $totalQCount = $sidebarCounts['total_questions'] ?? 0;
            @endphp
            <a href="#questionsSubmenu" 
               data-bs-toggle="collapse" 
               role="button" 
               aria-expanded="{{ $isQuestionsActive ? 'true' : 'false' }}" 
               aria-controls="questionsSubmenu" 
               class="nav-link {{ $isQuestionsActive ? 'active' : '' }}">
                <i class="bi bi-question-circle-fill"></i>
                <span>Questions</span>
                @if($totalQCount > 0)
                    <span class="badge sidebar-count-pill ms-auto">{{ number_format($totalQCount) }}</span>
                @endif
                <i class="bi bi-chevron-down collapse-caret"></i>
            </a>
            <div class="collapse {{ $isQuestionsActive ? 'show' : '' }}" id="questionsSubmenu">
                <div class="sidebar-submenu d-flex flex-column">
                    <a href="{{ route('admin.questions.index') }}"
                        class="nav-link {{ request()->routeIs('admin.questions.index') && !$currentStatus ? 'active' : '' }}">
                        <i class="bi bi-journals"></i> 
                        <span>All Questions</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($totalQCount) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'pending']) }}" 
                        class="nav-link {{ $currentStatus === 'pending' ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> 
                        <span>Pending (Review)</span>
                        @php $pendingCount = $sidebarCounts['pending'] ?? 0; @endphp
                        <span class="badge {{ $pendingCount > 0 ? 'sidebar-sub-pill-amber' : 'sidebar-sub-pill' }} ms-auto">{{ number_format($pendingCount) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'accepted']) }}" 
                        class="nav-link {{ $currentStatus === 'accepted' ? 'active' : '' }}">
                        <i class="bi bi-check2-circle"></i> 
                        <span>Accepted</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($sidebarCounts['accepted'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'assigned']) }}" 
                        class="nav-link {{ $currentStatus === 'assigned' ? 'active' : '' }}">
                        <i class="bi bi-person-check"></i> 
                        <span>Assigned to Mufti</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($sidebarCounts['assigned'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'drafted']) }}" 
                        class="nav-link {{ $currentStatus === 'drafted' ? 'active' : '' }}">
                        <i class="bi bi-pencil-square"></i> 
                        <span>Mufti Drafted</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($sidebarCounts['drafted'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'answered']) }}" 
                        class="nav-link {{ $currentStatus === 'answered' ? 'active' : '' }}">
                        <i class="bi bi-chat-check"></i> 
                        <span>Answered by Mufti</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($sidebarCounts['answered'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'under_review']) }}" 
                        class="nav-link {{ $currentStatus === 'under_review' ? 'active' : '' }}">
                        <i class="bi bi-arrow-repeat"></i> 
                        <span>Under Review</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($sidebarCounts['under_review'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'published']) }}" 
                        class="nav-link {{ $currentStatus === 'published' ? 'active' : '' }}">
                        <i class="bi bi-journal-check"></i> 
                        <span>Published to Public</span>
                        <span class="badge sidebar-sub-pill-emerald ms-auto">{{ number_format($sidebarCounts['published'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.questions.index', ['status' => 'rejected']) }}" 
                        class="nav-link {{ $currentStatus === 'rejected' ? 'active' : '' }}">
                        <i class="bi bi-x-circle"></i> 
                        <span>Rejected</span>
                        <span class="badge sidebar-sub-pill ms-auto">{{ number_format($sidebarCounts['rejected'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('admin.fatwas.import-old') }}" 
                        class="nav-link {{ request()->routeIs('admin.fatwas.import-old') ? 'active' : '' }}">
                        <i class="bi bi-cloud-arrow-up text-warning"></i> 
                        <span>Import Old Fatwa</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-section-title">INTELLIGENCE & REPORTS</div>
            <a href="{{ route('admin.reports.index') }}"
                class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reports & Analytics</span>
            </a>
            <a href="{{ route('admin.user-logs.index') }}"
                class="nav-link {{ request()->routeIs('admin.user-logs*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>User Behavior Logs</span>
                @if(($sidebarCounts['user_logs'] ?? 0) > 0)
                    <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['user_logs']) }}</span>
                @endif
            </a>
            <a href="{{ route('admin.traffic.performance') }}"
                class="nav-link {{ request()->routeIs('admin.traffic.performance*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Traffic &amp; Performance</span>
                <span class="badge rounded-pill px-2 py-0.5 text-2xs fw-bold ms-auto" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                    <span class="live-pulse-dot me-1" style="width: 5px; height: 5px;"></span> Live
                </span>
            </a>

            <div class="sidebar-section-title">SETTINGS</div>
            <a href="{{ route('admin.muftis.index') }}"
                class="nav-link {{ request()->routeIs('admin.muftis*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i>
                <span>Mufti Management</span>
                <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['muftis'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.categories.index') }}"
                class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i>
                <span>Categories</span>
                <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['categories'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.madhabs.index') }}"
                class="nav-link {{ request()->routeIs('admin.madhabs*') ? 'active' : '' }}">
                <i class="bi bi-book-fill"></i>
                <span>Madhab</span>
                <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['madhabs'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.questioners.index') }}"
                class="nav-link {{ request()->routeIs('admin.questioners*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Questioners</span>
                <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['questioners'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.featured-rules.index') }}"
                class="nav-link {{ request()->routeIs('admin.featured-rules*') ? 'active' : '' }}">
                <i class="bi bi-stars"></i>
                <span>Featured Fatwas</span>
                <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['featured'] ?? 0) }}</span>
            </a>

            <div class="sidebar-section-title">SYSTEM CONTROL</div>
            <a href="{{ route('admin.visitor.insights') }}"
                class="nav-link {{ request()->routeIs('admin.visitor.insights') ? 'active' : '' }}">
                <i class="bi bi-globe-americas"></i>
                <span>Visitor Insights</span>
                <span class="badge sidebar-count-pill ms-auto">{{ number_format($sidebarCounts['total_visitors'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.settings.system') }}"
                class="nav-link {{ request()->routeIs('admin.settings.system') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i>
                <span>System Settings</span>
            </a>
            <a href="{{ route('admin.mail-management.index') }}"
                class="nav-link {{ request()->routeIs('admin.mail-management*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper-fill"></i>
                <span>Mailing Management</span>
            </a>
            <a href="/system-health" target="_blank"
                class="nav-link">
                <i class="bi bi-shield-check text-info"></i>
                <span>System Health & Portal</span>
            </a>
            <a href="{{ route('admin.fatwa-correction.index') }}"
                class="nav-link {{ request()->routeIs('admin.fatwa-correction*') ? 'active' : '' }}">
                <i class="bi bi-database-check text-warning"></i>
                <span>Data Integrity & Restoration</span>
                @if(!empty($sidebarCounts['collisions']) && $sidebarCounts['collisions'] > 0)
                    <span class="badge sidebar-sub-pill-amber ms-auto" title="Collisions Awaiting Review">{{ $sidebarCounts['collisions'] }}</span>
                @endif
            </a>

            <div class="mt-4 px-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light w-100 py-2 border-opacity-20 d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Mobile Overlay --}}
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    {{-- Main Workspace Area --}}
    <div class="main-content">
        {{-- Modern Topbar --}}
        <header class="modern-topbar">
            {{-- Left Title & Breadcrumbs --}}
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-lg-none" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="header-title-box">
                    <div class="header-page-icon">
                        <i class="bi @yield('topbar-icon', 'bi-file-earmark-text-fill')"></i>
                    </div>
                    <div class="header-title-text">
                        <h4>@yield('topbar-title', 'Question Management')</h4>
                        <div class="header-breadcrumbs">
                            @sectionMissing('breadcrumbs')
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
                                <span class="text-dark fw-semibold">@yield('topbar-title', 'Overview')</span>
                            @else
                                @yield('breadcrumbs')
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Center Information Pills (Reference Design) --}}
            <div class="d-none d-xl-flex align-items-center gap-2.5">
                {{-- Live Clock --}}
                <div class="header-pill-capsule">
                    <i class="bi bi-clock"></i>
                    <span id="admin-live-clock" class="fw-bold" style="font-family: 'Inter', monospace; font-size: 0.88rem;">00:00:00</span>
                    <span class="text-muted small" style="font-size: 0.72rem;">IST</span>
                </div>

                {{-- Gregorian Date --}}
                <div class="header-pill-capsule">
                    <i class="bi bi-calendar3"></i>
                    <span class="fw-semibold">{{ date('d M, Y') }}</span>
                </div>

                {{-- Hijri Date --}}
                <div class="header-pill-capsule">
                    <i class="bi bi-moon-stars" style="color: #D97706;"></i>
                    <span class="fw-semibold" style="color: #92400E;">{{ \App\Helpers\HijriHelper::now('j F Y') }}</span>
                </div>
            </div>

            {{-- Right Controls: Notifications & User Badge --}}
            <div class="d-flex align-items-center gap-3">
                @php
                    $pendingAvatarUsers = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->get()->filter(function($u) {
                        return \Illuminate\Support\Facades\Storage::disk('public')->exists("avatars/pending_{$u->id}.jpg");
                    });
                    $pendingAvatarCount = $pendingAvatarUsers->count();
                @endphp

                {{-- Notifications Bell --}}
                <div class="dropdown no-print">
                    <button class="btn btn-light rounded-circle border p-2 position-relative text-secondary" type="button" id="avatarNotifications" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px;">
                        <i class="bi bi-bell"></i>
                        @if($pendingAvatarCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem;">
                                {{ $pendingAvatarCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 mt-2" aria-labelledby="avatarNotifications" style="width: 320px; border-radius: 14px;">
                        <li class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between">
                            <span class="small fw-bold text-dark">Notifications</span>
                            @if($pendingAvatarCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $pendingAvatarCount }} Pending</span>
                            @endif
                        </li>
                        @forelse($pendingAvatarUsers as $u)
                            <li class="my-2 p-2 rounded d-flex align-items-center justify-content-between hover-bg-light">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('storage/avatars/pending_' . $u->id . '.jpg') }}" class="rounded border" width="36" height="36" style="object-fit: cover;">
                                    <div>
                                        <div class="small fw-bold text-dark">{{ $u->name }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Pending avatar approval</div>
                                    </div>
                                </div>
                                <button class="btn btn-xs btn-primary rounded-pill px-2.5 py-1" 
                                        style="font-size: 0.72rem;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#verifyAvatarModal" 
                                        data-user-id="{{ $u->id }}"
                                        data-user-name="{{ $u->name }}"
                                        data-current-avatar="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=102B43&color=fff&size=40' }}"
                                        data-pending-avatar="{{ asset('storage/avatars/pending_' . $u->id . '.jpg') }}">
                                    Review
                                </button>
                            </li>
                        @empty
                            <li class="text-center py-4 text-muted small">
                                <i class="bi bi-bell-slash fs-4 d-block mb-1 text-slate-400"></i>
                                No new notifications
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- User Avatar Pill (Reference Design) --}}
                <div class="dropdown">
                    <button class="btn btn-light border rounded-pill d-flex align-items-center gap-2 py-1 px-2.5 text-decoration-none shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=102B43&color=fff&size=36' }}"
                            class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                        <div class="text-start d-none d-sm-block pe-1">
                            <div class="fw-bold text-dark" style="font-size: 0.8rem; line-height: 1.1;">DARUL IFTA</div>
                            <div class="text-muted" style="font-size: 0.68rem; line-height: 1;">Admin Console</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted" style="font-size: 0.7rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 mt-2" style="border-radius: 12px; min-width: 200px;">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-dark small">{{ Auth::user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.74rem;">{{ Auth::user()->email }}</div>
                        </li>
                        <li><a class="dropdown-menu-item dropdown-item small py-2 rounded mt-1" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-menu-item dropdown-item small py-2 rounded" href="/system-health" target="_blank"><i class="bi bi-shield-check text-info me-2"></i>System Health</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item small py-2 rounded text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Flash Alerts --}}
        <div class="px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs py-2.5 px-3 rounded-3" role="alert" style="background: #ECFDF5; color: #065F46; border-left: 4px solid #10B981 !important;">
                    <i class="bi bi-check-circle-fill me-2 text-success"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs py-2.5 px-3 rounded-3" role="alert" style="background: #FEF2F2; color: #991B1B; border-left: 4px solid #EF4444 !important;">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        {{-- Page Body Content --}}
        <main class="page-content-wrapper">
            {{-- Print Header --}}
            <div class="d-none d-print-block text-center mb-5 border-bottom pb-4">
                <h1 class="fw-bold" style="color:#102B43;">Darul Ifta Kauzariyya</h1>
                <p class="mb-1">Authentic Islamic Guidance - Administrative Record</p>
                <p class="small text-muted mb-0">Confidential Fatwa Documentation</p>
            </div>

            @yield('content')

            </main>

        {{-- Global Fixed Footer --}}
        @include('components.admin-footer')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW Registered', reg))
                    .catch(err => console.log('SW Failed', err));
            });
        }

        // Sidebar Toggle for Mobile
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }

        // Digital Clock Engine
        function updateAdminClock() {
            const clockElement = document.getElementById('admin-live-clock');
            if (!clockElement) return;

            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            clockElement.innerHTML = timeString;
        }
        setInterval(updateAdminClock, 1000);
        updateAdminClock();

        // Universal Single-Click Button Protection & Action Deactivation Engine
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;

                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }

                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                form.dataset.submitting = 'true';

                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"], .btn-submit-action');
                submitButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('disabled');
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.75';

                    if (!btn.querySelector('.spinner-border')) {
                        const originalHtml = btn.innerHTML;
                        btn.setAttribute('data-original-text', originalHtml);
                        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true" style="width: 0.9rem; height: 0.9rem;"></span><span>Processing...</span>`;
                    }
                });

                setTimeout(() => {
                    form.dataset.submitting = 'false';
                    submitButtons.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('disabled');
                        btn.style.pointerEvents = 'auto';
                        btn.style.opacity = '1';
                        if (btn.hasAttribute('data-original-text')) {
                            btn.innerHTML = btn.getAttribute('data-original-text');
                        }
                    });
                }, 15000);
            }, true);

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-single-click, button[type="submit"]');
                if (!btn) return;

                const form = btn.closest('form');
                if (form && form.dataset.submitting === 'true') {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });
    </script>
    
    {{-- Verify Avatar Modal --}}
    <div class="modal fade" id="verifyAvatarModal" tabindex="-1" aria-labelledby="verifyAvatarModalLabel" aria-hidden="true" style="z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header bg-light border-0 py-3" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h6 class="modal-title fw-bold text-dark" id="verifyAvatarModalLabel">Verify Profile Picture</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-4">Review the photo upload request for scholar <b id="modal-user-name">Mufti</b>.</p>
                    
                    <div class="d-flex align-items-center justify-content-center gap-5 mb-4">
                        <div>
                            <span class="d-block text-muted small mb-2 fw-semibold">Current Photo</span>
                            <img id="modal-current-img" src="" class="rounded-circle border" width="90" height="90" style="object-fit: cover;">
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div>
                            <span class="d-block text-muted small mb-2 fw-semibold">New Proposed Photo</span>
                            <img id="modal-pending-img" src="" class="rounded border shadow-sm" width="90" height="110" style="object-fit: cover;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <form id="approve-avatar-form" method="POST" action="">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold small shadow-sm">
                                <i class="bi bi-check-lg me-1"></i>Approve Photo
                            </button>
                        </form>
                        <form id="reject-avatar-form" method="POST" action="">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-4 fw-semibold small">
                                <i class="bi bi-trash3 me-1"></i>Reject & Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- High-Security Fatwa Deletion Modal --}}
    @include('components.fatwa-delete-security-modal')

    {{-- Domain-Wide Universal Live Presence Tracker --}}
    @include('components.universal-presence-tracker')

    @stack('scripts')
</body>
</html>
