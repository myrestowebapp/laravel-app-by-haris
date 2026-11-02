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

    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Darul Ifta Portal') — Kauzariyya</title>
    <meta name="description" content="@yield('meta_description', 'Darul Ifta Kauzariyya is an authoritative Islamic jurisprudence and fatwa archive platform providing authentic scholarly rulings according to the Quran and Sunnah.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Darul Ifta, Kauzariyya, Islamic Fatwa, Fiqh, Hanafi, Shafi, Islamic Jurisprudence, Ask Mufti, Islamic Rulings, Sharia')">
    <meta name="author" content="@yield('meta_author', 'Darul Ifta Kauzariyya')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn, AI Preview) -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Darul Ifta Kauzariyya">
    <meta property="og:title" content="@yield('og_title', View::hasSection('title') ? View::getSection('title') . ' — Kauzariyya' : 'Darul Ifta Portal — Kauzariyya')">
    <meta property="og:description" content="@yield('meta_description', 'Darul Ifta Kauzariyya is an authoritative Islamic jurisprudence and fatwa archive platform.')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('assets/img/slider/center.png'))">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@DarulIftaKz">
    <meta name="twitter:title" content="@yield('og_title', View::hasSection('title') ? View::getSection('title') . ' — Kauzariyya' : 'Darul Ifta Portal — Kauzariyya')">
    <meta name="twitter:description" content="@yield('meta_description', 'Darul Ifta Kauzariyya is an authoritative Islamic jurisprudence and fatwa archive platform.')">
    <meta name="twitter:image" content="@yield('og_image', asset('assets/img/slider/center.png'))">

    @stack('meta')
    @if(isset($globalJsonLd))
    <script type="application/ld+json">
    {!! json_encode($globalJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endif
    @stack('schema')

    <script type="text/javascript">
        (function (c, l, a, r, i, t, y) {
            c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments) };
            t = l.createElement(r); t.async = 1; t.src = "https://www.clarity.ms/tag/" + i;
            y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
        })(window, document, "clarity", "script", "ycju085szi");
    </script>
    
    {{-- High-Priority Preloads --}}
    @if(request()->routeIs('home'))
        <link rel="preload" as="image" href="{{ asset('assets/img/slider/center.png') }}" fetchpriority="high">
    @endif

    {{-- PWA Manifest & Theme Color --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#295A75">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Darul Ifta">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo/Darul-Ifta-Logo.png') }}">
    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Scheherazade+New:wght@400;600;700&family=Gulzar&family=Noto+Naskh+Arabic:wght@400;600;700&family=Noto+Serif+Malayalam:wght@400;500;600;700;800&family=Noto+Sans+Malayalam:wght@400;500;600;700;800&family=Manjari:wght@400;700&family=Gayathri:wght@400;700&display=swap"
        rel="stylesheet">
    
    {{-- Inline Critical CSS (Variables & Fonts) --}}
    <style>
        :root {
            --brand-blue: #295A75;
            --brand-blue-dark: #1D4054;
            --brand-red: #701F18;
            --brand-gold: #c9a84c;
            --brand-gold-dark: #a8883a;
            --primary-color: var(--brand-blue);
            --secondary-color: var(--brand-gold);
            --accent-color: var(--brand-red);
            --bg-light: #F8F4F2;
            --bg-white: #ffffff;
            --surface-light: #ffffff;
            --text-main: #1a1a1a;
            --text-muted: #6b7280;
            --text-on-brand: #ffffff;
            --header-height: 90px;
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.08);
            --transition-base: all 0.3s ease;
        }

        /* Font Registration */
        @font-face {
            font-family: 'Google Sans Eng';
            src: url('{{ asset("assets/font/Google_Sans_eng/GoogleSansFlex-VariableFont_GRAD,ROND,opsz,slnt,wdth,wght.ttf") }}') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Noto Naskh Arabic';
            src: url('{{ asset("assets/font/Noto_Naskh_Arabic/NotoNaskhArabic-VariableFont_wght.ttf") }}') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Noto Nastaliq Urdu';
            src: url('{{ asset("assets/font/Noto_Nastaliq_Urdu/NotoNastaliqUrdu-VariableFont_wght.ttf") }}') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Noto Serif Malayalam';
            src: url('{{ asset("assets/font/Noto_Serif_Malayalam/NotoSerifMalayalam-VariableFont_wght.ttf") }}') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }

        /* Typography Utilities */
        .font-eng       { font-family: 'Google Sans Eng', 'Inter', sans-serif !important; }
        .font-arabic    { font-family: 'Amiri', 'Noto Naskh Arabic', 'Scheherazade New', serif !important; direction: rtl; text-align: right; }
        .font-urdu      { font-family: 'Noto Nastaliq Urdu', 'Gulzar', serif !important; direction: rtl; text-align: right; }
        .font-malayalam { font-family: 'Noto Serif Malayalam', serif !important; }
        .ar-text        { font-family: 'Amiri', 'Noto Naskh Arabic', 'Scheherazade New', serif !important; direction: rtl; text-align: right; line-height: 2; }
        .ur-text        { font-family: 'Noto Nastaliq Urdu', 'Gulzar', serif !important; direction: rtl; text-align: right; line-height: 2.4; }
        .ml-text        { font-family: 'Noto Serif Malayalam', serif !important; line-height: 1.95; }
        .en-text        { font-family: 'Google Sans Eng', 'Inter', sans-serif !important; }
        .inter-font     { font-family: 'Google Sans Eng', 'Inter', sans-serif !important; }
        .noto-serif-malayalam-font { font-family: 'Noto Serif Malayalam', serif !important; }
        .alyamama-font  { font-family: 'Amiri', 'Noto Naskh Arabic', 'Scheherazade New', serif !important; direction: rtl; text-align: right; line-height: 2.1; }
        .gulzar-font    { font-family: 'Noto Nastaliq Urdu', 'Gulzar', serif !important; direction: rtl; text-align: right; line-height: 2.4; }
        .font-urdu p, .ur-text p  { line-height: 2.4 !important; }
        .font-arabic p, .ar-text p { line-height: 2 !important; }
    </style>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/fevi.png') }}">

    {{-- PWA Meta --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#073a69ff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo/fevi.png') }}">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
        }

        main {
            flex: 1 0 auto;
        }

        /* Unified Main Area Layout with Increased Side Padding (68% Width on Header, Content & Footer) */
        @media (min-width: 992px) {
            .container,
            .container-lg,
            .container-xl,
            .container-xxl {
                max-width: 1280px !important;
                width: 68% !important;
                padding-left: 15px !important;
                padding-right: 15px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
        }

        @media (min-width: 1600px) {
            .container,
            .container-lg,
            .container-xl,
            .container-xxl {
                max-width: 1360px !important;
                width: 68% !important;
            }
        }

        /* Typography Utility Classes */
        .ar-text {
            font-family: 'Noto Naskh Arabic', serif;
            direction: rtl;
            text-align: right;
        }

        .ur-text {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
            text-align: right;
        }

        .ml-text {
            font-family: 'Noto Serif Malayalam', serif;
        }

        /* Premium Header Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            min-height: var(--header-height);
            height: var(--header-height);
            padding: 0 !important;
            border-bottom: 2px solid var(--brand-blue);
            box-shadow: var(--shadow-md);
            z-index: 1050;
        }

        .navbar-sticky {
            position: sticky;
            top: 0;
        }

        .navbar-brand img {
            height: 100%;
            max-height: 65px;
            width: auto;
            transition: var(--transition-base);
        }

        .navbar-brand:hover img {
            transform: scale(1.02);
        }

        .nav-link {
            color: var(--brand-blue) !important;
            font-weight: 600;
            padding: 0.5rem 1rem !important;
            font-size: 0.95rem;
            transition: var(--transition-base);
            border-radius: 6px;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--brand-gold) !important;
            background: rgba(41, 90, 117, 0.05);
        }

        .btn-gold {
            background: var(--brand-gold);
            border-color: var(--brand-gold);
            color: #fff;
            font-weight: 600;
        }

        .btn-gold:hover {
            background: var(--brand-gold-dark);
            border-color: var(--brand-gold-dark);
            color: #fff;
        }

        .text-brand {
            color: var(--brand-blue);
        }

        .text-gold {
            color: var(--brand-gold);
        }

        /* ========================================================
           Standardized Unified Fatwa Display Card Component Styles
           ======================================================== */
        .fatwa-card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease, border-color 0.28s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .fatwa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 36px rgba(10, 35, 64, 0.09);
            border-color: #cbd5e1;
        }

        .fatwa-thumb {
            position: relative;
            width: 100%;
            height: 180px;
            min-height: 180px;
            max-height: 180px;
            overflow: hidden;
            background: #0d263e;
        }

        .fatwa-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .fatwa-card:hover .fatwa-thumb img {
            transform: scale(1.05);
        }

        .madhab-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #1a7a4a;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .fatwa-body {
            padding: 1.25rem 1.35rem 1.15rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
        }

        .fatwa-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.76rem;
            color: #94a3b8;
            margin-bottom: 0.75rem;
            font-weight: 500;
            gap: 6px;
        }

        .fatwa-meta-cat {
            color: #1a7a4a;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 48%;
        }

        .fatwa-meta-stars {
            font-size: 0.72rem;
            letter-spacing: 1px;
        }

        .fatwa-meta-date {
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Universal Multi-Language Support Font Stacks */
        .fatwa-title, .fatwa-excerpt {
            font-family: 'Noto Sans Malayalam', 'Noto Serif Malayalam', 'Amiri', 'Noto Naskh Arabic', 'Gulzar', 'Inter', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .fatwa-title {
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.5;
            margin-bottom: 0.65rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.1rem;
        }

        .fatwa-title.lang-malayalam, .fatwa-excerpt.lang-malayalam {
            font-family: 'Noto Sans Malayalam', 'Noto Serif Malayalam', 'Manjari', sans-serif !important;
            line-height: 1.65;
        }

        .fatwa-title.lang-arabic, .fatwa-excerpt.lang-arabic {
            font-family: 'Amiri', 'Noto Naskh Arabic', 'Scheherazade New', serif !important;
            line-height: 1.85;
            direction: rtl;
            text-align: right;
        }

        .fatwa-title.lang-urdu, .fatwa-excerpt.lang-urdu {
            font-family: 'Gulzar', 'Noto Nastaliq Urdu', 'Noto Naskh Arabic', serif !important;
            line-height: 2.0;
            direction: rtl;
            text-align: right;
        }

        .fatwa-excerpt {
            font-size: 0.86rem;
            color: #475569;
            line-height: 1.65;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 4.2rem;
        }

        .fatwa-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #f1f5f9;
            padding-top: 0.85rem;
            margin-top: auto;
            gap: 8px;
        }

        .fatwa-author-box {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .fatwa-author {
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fatwa-actions-box {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .read-more-btn {
            font-size: 0.78rem;
            font-weight: 700;
            color: #1a7a4a;
            border: 1.5px solid #1a7a4a;
            padding: 3px 12px;
            border-radius: 20px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .fatwa-card:hover .read-more-btn {
            background: #1a7a4a;
            color: #ffffff;
        }

        /* Modern Deep Dark Footer Styling */
        footer {
            background: linear-gradient(180deg, #09121a 0%, #03060a 100%);
            color: #94a3b8;
            padding: 4.5rem 0 2rem;
            position: relative;
            font-size: 0.92rem;
            border-top: 1px solid rgba(201, 168, 76, 0.2);
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, #c9a84c 20%, #f6e09e 50%, #c9a84c 80%, transparent 100%);
            box-shadow: 0 0 16px rgba(201, 168, 76, 0.4);
        }

        .footer-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.3px;
            margin-bottom: 1.35rem;
            position: relative;
            padding-bottom: 0.6rem;
            display: inline-block;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 30px;
            height: 2.5px;
            background: linear-gradient(90deg, #c9a84c, #f6e09e);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.65rem;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .footer-links a i {
            color: #c9a84c;
            font-size: 0.75rem;
            margin-right: 0.5rem;
            transition: transform 0.25s ease;
        }

        .footer-links a:hover {
            color: #ffffff;
            transform: translateX(5px);
        }

        .footer-links a:hover i {
            color: #f6e09e;
            transform: translateX(2px);
        }

        .footer-logo {
            max-height: 88px;
            width: auto;
            filter: brightness(0) invert(1);
            margin-bottom: 1.25rem;
            transition: transform 0.3s ease;
        }

        .footer-logo:hover {
            transform: scale(1.02);
        }

        .footer-social {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .footer-social a {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }

        .footer-social a:hover {
            background: #c9a84c;
            border-color: #c9a84c;
            color: #060b10;
            transform: translateY(-3px);
            box-shadow: 0 4px 14px rgba(201, 168, 76, 0.35);
        }

        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            margin-bottom: 0.95rem;
        }

        .footer-contact-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 8px;
            background: rgba(201, 168, 76, 0.12);
            border: 1px solid rgba(201, 168, 76, 0.25);
            color: #e6c875;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            margin-top: 2px;
        }

        .footer-contact-item a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-contact-item a:hover {
            color: #f6e09e;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 3.5rem;
            padding-top: 1.5rem;
        }

            {
                {
                -- Mobile Logo Fix & Styling --
            }
        }

        @media screen and (max-width: 991px) {
            .navbar {
                height: 80px !important;
                min-height: 80px;
                padding: 0 10px !important;
            }

            .navbar-brand img {
                max-height: 55px !important;
                opacity: 1 !important;
                visibility: visible !important;
                display: block !important;
            }

            .navbar-brand {
                display: flex !important;
                align-items: center !important;
            }
        }

        /* Offcanvas Sidebar Styling (L-to-R) */
        .offcanvas {
            width: 75% !important;
            border-right: 5px solid var(--brand-gold) !important;
            border-left: none !important;
        }

        .offcanvas-header {
            background: #ffffff;
            color: var(--brand-blue);
            border-bottom: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
        }

        .offcanvas-body {
            padding: 0 !important;
            background: #f8fafc;
        }

        .mobile-nav-list {
            list-style: none;
            padding: 2.5rem 1.25rem 1.25rem 1.25rem !important;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .mobile-nav-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.65rem;
            padding: 0.85rem 1.25rem;
            color: var(--brand-blue);
            background: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            background: var(--brand-blue);
            color: #ffffff;
            border-color: var(--brand-blue);
            box-shadow: 0 4px 14px rgba(5, 41, 97, 0.16);
            transform: translateY(-1px);
        }

        .mobile-nav-link.active i {
            color: var(--brand-gold);
        }

        .mobile-nav-link i {
            font-size: 1.15rem;
            width: auto;
        }

        .mobile-auth-section {
            padding: 1.5rem 1.25rem;
            background: #ffffff;
            margin-top: auto;
            border-top: 1px solid #eef2f7;
        }

        @media print {

            .navbar,
            footer,
            .btn-print,
            .btn,
            .no-print {
                display: none !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                background: #fff !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NBPW3VHH" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light navbar-sticky shadow-sm">
        <div class="container h-100">
            <a class="navbar-brand h-100 d-none d-lg-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('assets/logo/logoblueNew.svg') }}" alt="Darul Ifta Kauzariyya Logo">
            </a>

            {{-- Mobile Hamburger --}}
            <button class="navbar-toggler border-0 shadow-none d-lg-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Desktop Navigation --}}
            <div class="collapse navbar-collapse h-100" id="navMain">
                <ul class="navbar-nav mx-auto gap-3">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('fatwa.*') ? 'active' : '' }}"
                            href="{{ route('fatwa.archive') }}">Fatwa Archive</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('question.*') ? 'active' : '' }}"
                            href="{{ route('question.create') }}">Ask a Question</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('team') ? 'active' : '' }}"
                            href="{{ route('team') }}">Meet the Muftis</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                            href="{{ route('contact') }}">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-gold btn-sm fw-bold px-3">Join / Sign In</a>
                    @else
                        <div class="dropdown">
                            <button class="btn btn-outline-brand btn-sm dropdown-toggle fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}" class="rounded-circle me-1" width="24" height="24" style="object-fit: cover;">
                                @else
                                    <i class="bi bi-person-circle me-1 text-brand"></i>
                                @endif
                                <span>{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" style="min-width: 230px; border-radius: 12px;">
                                <li class="px-3 py-2 bg-light border-bottom mb-1">
                                    <div class="fw-bold text-dark text-truncate">{{ Auth::user()->name }}</div>
                                    <span class="badge bg-brand-subtle text-brand border border-success-subtle text-capitalize" style="font-size: 0.7rem;">
                                        <i class="bi bi-person-badge me-1"></i>{{ Auth::user()->role?->name ?? 'User' }}
                                    </span>
                                </li>
                                @if(Auth::user()->hasRole('admin'))
                                    <li>
                                        <a class="dropdown-item py-2 fw-semibold text-brand" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-speedometer2 me-2"></i>Admin Panel
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->hasRole('mufti'))
                                    <li>
                                        <a class="dropdown-item py-2 fw-semibold text-brand" href="{{ route('mufti.dashboard') }}">
                                            <i class="bi bi-person-workspace me-2"></i>Mufti Portal
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->hasRole('user') || Auth::user()->hasRole('questioner') || !in_array(Auth::user()->role?->name, ['admin', 'mufti']))
                                    <li>
                                        <a class="dropdown-item py-2 fw-semibold" href="{{ route('user.dashboard') }}">
                                            <i class="bi bi-grid-fill me-2 text-primary"></i>User Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('user.settings') }}">
                                            <i class="bi bi-gear-fill me-2 text-secondary"></i>Account Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('question.create') }}">
                                            <i class="bi bi-pencil-square me-2 text-success"></i>Ask a Question
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger py-2"><i
                                                class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    {{-- Mobile Offcanvas Menu (L-to-R) --}}
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header d-flex align-items-center justify-content-between">
            <a href="{{ route('home') }}" class="d-inline-flex align-items-center">
                <img src="{{ asset('assets/logo/logoblueNew.svg') }}" alt="Darul Ifta Kauzariyya" style="max-height: 38px; width: auto;">
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column h-100">
            <div class="mobile-nav-list">
                <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i> Home
                </a>
                <a href="{{ route('fatwa.archive') }}"
                    class="mobile-nav-link {{ request()->routeIs('fatwa.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-bookmark"></i> Fatwa Archive
                </a>
                <a href="{{ route('question.create') }}"
                    class="mobile-nav-link {{ request()->routeIs('question.*') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square"></i> Ask a Question
                </a>
                <a href="{{ route('team') }}" class="mobile-nav-link {{ request()->routeIs('team') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Meet the Muftis
                </a>
                <a href="{{ route('contact') }}"
                    class="mobile-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                    <i class="bi bi-envelope"></i> Contact Us
                </a>

            </div>

            <div class="mobile-auth-section mt-auto">
                @guest
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-gold fw-bold py-2">Join / Sign In</a>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-light rounded-3 border">
                        <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=1a7a4a&color=fff' }}"
                            class="rounded-circle" width="45" height="45" style="object-fit: cover;">
                        <div class="overflow-hidden">
                            <div class="fw-bold text-truncate">{{ Auth::user()->name }}</div>
                            <small class="badge bg-brand-subtle text-brand border border-success-subtle text-capitalize" style="font-size: 0.65rem;">
                                {{ Auth::user()->role?->name ?? 'User' }}
                            </small>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-brand btn-sm text-start"><i
                                    class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a>
                        @endif
                        @if(Auth::user()->hasRole('mufti'))
                            <a href="{{ route('mufti.dashboard') }}" class="btn btn-brand btn-sm text-start"><i
                                    class="bi bi-person-workspace me-2"></i>Mufti Portal</a>
                        @endif
                        @if(Auth::user()->hasRole('user') || Auth::user()->hasRole('questioner') || !in_array(Auth::user()->role?->name, ['admin', 'mufti']))
                            <a href="{{ route('user.dashboard') }}" class="btn btn-brand btn-sm text-start"><i
                                    class="bi bi-grid-fill me-2"></i>User Dashboard</a>
                            <a href="{{ route('user.settings') }}" class="btn btn-outline-secondary btn-sm text-start"><i
                                    class="bi bi-gear-fill me-2"></i>Account Settings</a>
                            <a href="{{ route('question.create') }}" class="btn btn-outline-success btn-sm text-start"><i
                                    class="bi bi-pencil-square me-2"></i>Ask a Question</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm text-start w-100 mt-1"><i
                                    class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </div>

    @if(session('success') || session('error'))
        <div class="container mt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Main content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-5">
        <div class="container">
            <div class="row g-4 justify-content-between">
                {{-- Column 1: Brand & Mission --}}
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <img src="{{ asset('assets/logo/logoblueNew.svg') }}" class="footer-logo" alt="Darul Ifta"
                        style="filter: brightness(0) invert(1); max-height: 88px;">
                    <p class="small text-white-50 pe-lg-4 lh-lg mt-2 mb-3">
                        Darul Ifta Kauzariyya is dedicated to providing authentic Islamic guidance based on the Quran
                        and Sunnah. Our mission is to illuminate the path of knowledge for the global community.
                    </p>
                    <div class="footer-social mt-3">
                        <a href="https://www.youtube.com/@Kauzariyya" target="_blank" rel="noopener noreferrer" title="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="https://www.instagram.com/kauzariyya" target="_blank" rel="noopener noreferrer" title="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://x.com/kauzariyya" target="_blank" rel="noopener noreferrer" title="X (Twitter)"><i class="bi bi-twitter-x"></i></a>
                        <a href="https://www.linkedin.com/company/kauzariyya/" target="_blank" rel="noopener noreferrer" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                {{-- Column 2: Our Services --}}
                <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                    <div class="footer-title">Our Services</div>
                    <ul class="footer-links">
                        <li><a href="{{ route('question.create') }}"><i class="bi bi-chevron-right"></i>Ask a Fatwa</a></li>
                        <li><a href="{{ route('fatwa.archive') }}"><i class="bi bi-chevron-right"></i>Browse Archive</a></li>
                        <li><a href="{{ route('fatwa.archive') }}"><i class="bi bi-chevron-right"></i>Categories</a></li>
                        <li><a href="{{ route('team') }}"><i class="bi bi-chevron-right"></i>Scholar Directory</a></li>
                    </ul>
                </div>

                {{-- Column 3: Quick Links --}}
                <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                    <div class="footer-title">Quick Links</div>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}"><i class="bi bi-chevron-right"></i>Home</a></li>
                        <li><a href="{{ route('team') }}"><i class="bi bi-chevron-right"></i>Meet the Muftis</a></li>
                        <li><a href="{{ route('contact') }}"><i class="bi bi-chevron-right"></i>Contact Us</a></li>
                        <li><a href="{{ route('privacy') }}"><i class="bi bi-chevron-right"></i>Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}"><i class="bi bi-chevron-right"></i>Terms of Service</a></li>
                        <li><a href="{{ route('login') }}"><i class="bi bi-chevron-right"></i>Portal Login</a></li>
                    </ul>
                </div>

                {{-- Column 4: Contact Information --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-title">Contact Information</div>
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <p class="small mb-0 text-white-50">
                            <strong class="text-white d-block">Darul Ifta Kauzariyya</strong>
                            Kauzariyya Compound, Edathala North, Aluva, Kerala, India
                        </p>
                    </div>
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <p class="small mb-0">
                            <a href="mailto:darulifta.kauzariyya@gmail.com">darulifta.kauzariyya@gmail.com</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <p class="text-white-50 small mb-0">
                        &copy; {{ date('Y') }} <span class="text-white fw-semibold">Darul Ifta Kauzariyya</span>. Designed for Enlightenment.
                    </p>
                    <div class="small">
                        <span class="text-white-50">App created & developed by</span>
                        <a href="https://www.harisim.site" target="_blank" rel="noopener noreferrer" class="text-gold fw-bold px-2 py-0.5 rounded text-decoration-none" style="background: rgba(201, 168, 76, 0.12); border: 1px solid rgba(201, 168, 76, 0.25); transition: 0.2s;">MUFTI Haris I M</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('SW registration error:', err);
                });
            });
        }
    </script>
    @include('components.universal-presence-tracker')
    @stack('scripts')
</body>

</html>