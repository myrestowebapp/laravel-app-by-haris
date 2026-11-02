<!DOCTYPE html>
<html class="h-full bg-surface text-on-surface antialiased" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Mufti Panel — Darul Ifta</title>

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "ycju085szi");
    </script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Literata:ital,opsz,wght@0,7..72,400;0,7..72,500;0,7..72,600;1,7..72,400&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,500;0,8..60,600;1,8..60,400&family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;600;700&family=Noto+Nastaliq+Urdu:wght@400;700&family=Noto+Serif+Malayalam:wght@400;600;700&display=swap" rel="stylesheet"/>

    <!-- Material Symbols & Bootstrap Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

    <!-- Bootstrap 5 CSS (for Summernote & Modals Compatibility) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/fevi.png') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          corePlugins: {
            preflight: false,
          },
          theme: {
            extend: {
              "colors": {
                "tertiary-container": "#193927",
                "on-surface": "#1d1b19",
                "on-secondary-fixed": "#261900",
                "inverse-surface": "#32302e",
                "tertiary": "#022313",
                "secondary-fixed-dim": "#e4c283",
                "surface-container-high": "#ede7e4",
                "outline-variant": "#c2c8c2",
                "tertiary-fixed": "#c6ebd1",
                "on-secondary": "#ffffff",
                "inverse-on-surface": "#f5f0ec",
                "primary-fixed": "#ccead6",
                "secondary-container": "#ffdb9a",
                "on-surface-variant": "#424844",
                "background": "#fef8f5",
                "on-tertiary-container": "#80a38c",
                "on-secondary-container": "#795f2a",
                "inverse-primary": "#b1cdbb",
                "on-tertiary": "#ffffff",
                "surface": "#fef8f5",
                "secondary": "#745a26",
                "surface-variant": "#e7e1de",
                "surface-container-highest": "#e7e1de",
                "on-primary-fixed-variant": "#334c3e",
                "outline": "#727973",
                "surface-container": "#f2ede9",
                "primary-fixed-dim": "#b1cdbb",
                "surface-container-lowest": "#ffffff",
                "surface-container-low": "#f8f2ef",
                "on-background": "#1d1b19",
                "error": "#ba1a1a",
                "primary-container": "#1f382b",
                "on-secondary-fixed-variant": "#5a4310",
                "on-error-container": "#93000a",
                "on-primary-fixed": "#062014",
                "surface-dim": "#ded9d6",
                "on-tertiary-fixed": "#002111",
                "on-primary-container": "#86a291",
                "primary": "#092217",
                "surface-tint": "#4a6455",
                "secondary-fixed": "#ffdea4",
                "on-primary": "#ffffff",
                "error-container": "#ffdad6",
                "on-tertiary-fixed-variant": "#2d4d3a",
                "tertiary-fixed-dim": "#abcfb6",
                "surface-bright": "#fef8f5",
                "on-error": "#ffffff"
              },
              "fontFamily": {
                "headline-md": ["\"Source Serif 4\"", "serif"],
                "display-mobile": ["\"Source Serif 4\"", "serif"],
                "body-lg": ["Literata", "serif"],
                "body-md": ["Literata", "serif"],
                "headline-lg-mobile": ["\"Source Serif 4\"", "serif"],
                "label-md": ["Inter", "sans-serif"],
                "title-md": ["Literata", "serif"],
                "headline-lg": ["\"Source Serif 4\"", "serif"],
                "display": ["\"Source Serif 4\"", "serif"],
                "headline-sm": ["\"Source Serif 4\"", "serif"],
                "label-lg": ["Inter", "sans-serif"],
                "body-sm": ["Literata", "serif"],
                "label-sm": ["Inter", "sans-serif"]
              }
            }
          }
        }
    </script>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 20;
            vertical-align: middle;
        }
        body {
            background-color: #fef8f5;
            color: #1d1b19;
            font-family: 'Literata', serif;
            overflow-x: hidden;
        }
        a {
            text-decoration: none;
        }
        .arabic-text {
            font-family: 'Amiri', 'Noto Naskh Arabic', serif;
            direction: rtl;
        }
        .urdu-text {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
        }
        .malayalam-text {
            font-family: 'Noto Serif Malayalam', serif;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-surface text-on-surface min-h-screen flex flex-col font-body-md text-body-md selection:bg-surface-variant selection:text-primary">

    <!-- Top Navigation Header -->
    <header class="w-full bg-surface border-b border-surface-variant z-40 sticky top-0 shadow-sm backdrop-blur-md bg-opacity-95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 lg:py-4">
            <!-- Upper Header Bar: Brand Logo & Scholar Metadata + Mobile Menu Button -->
            <div class="flex items-center justify-between gap-4 pb-2.5 border-b border-surface-variant/40">
                <!-- Brand Logo & Identity -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('mufti.dashboard') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('assets/logo/logoblueNew.svg') }}" alt="Darul Ifta Kauzariyya Logo" class="h-8 sm:h-10 w-auto object-contain" onerror="this.src='{{ asset('assets/logo/Darul-Ifta-Logo.png') }}'">
                    </a>
                    <div class="h-8 w-[1px] bg-outline-variant/60 hidden sm:block"></div>
                    
                    <!-- Scholar Avatar & Identity -->
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <a href="{{ route('mufti.profile') }}" class="relative group block flex-shrink-0" title="Profile Settings">
                            <img src="{{ Auth::user()->avatar ? asset(ltrim(Auth::user()->avatar, '/')) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=092217&color=fff' }}" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-outline-variant/80 shadow-sm group-hover:border-primary group-hover:scale-105 transition-all">
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full {{ Auth::user()->is_active ? 'bg-emerald-500' : 'bg-stone-400' }} border-2 border-surface" title="{{ Auth::user()->is_active ? 'Active Jurisconsult' : 'Inactive' }}"></span>
                        </a>

                        <div class="flex flex-col">
                            <div class="flex items-baseline gap-1.5 flex-wrap">
                                <span class="font-headline-md text-[17px] sm:text-[20px] font-semibold text-primary tracking-tight">Mufti Panel</span>
                                <span class="text-label-md text-outline">/</span>
                                <a href="{{ route('mufti.profile') }}" class="font-body-md text-body-md text-secondary italic font-semibold hover:text-primary transition-colors">
                                    {{ Auth::user()->name }}
                                </a>
                                @if(Auth::user()->can_check_approve)
                                    <span class="bg-primary/10 text-primary text-[10px] font-bold px-1.5 py-0.2 rounded border border-primary/20 uppercase tracking-wider hidden sm:inline-block">Verifier</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Fatwa Search Box (Desktop / Tablet) -->
                <div class="relative flex-1 max-w-sm lg:max-w-md mx-2 hidden sm:block">
                    <form action="{{ route('mufti.search') }}" method="GET" class="relative m-0" id="globalHeaderSearchForm">
                        <span class="material-symbols-outlined absolute left-3 top-2 text-[18px] text-outline pointer-events-none">search</span>
                        <input type="text" 
                               id="globalFatwaSearchInput" 
                               name="q" 
                               value="{{ request('q') }}"
                               placeholder="Search all fatwas, #ID, kitabs... (Ctrl+K)" 
                               autocomplete="off"
                               class="w-full pl-9 pr-12 py-1.5 text-[13px] bg-surface-container border border-outline-variant/70 rounded-full focus:outline-none focus:ring-1 focus:ring-primary focus:bg-white text-on-surface transition-all placeholder:text-outline">
                        <span class="absolute right-2.5 top-1.5 text-[10px] font-mono bg-surface-container-high px-1.5 py-0.5 rounded text-outline border border-outline-variant/60 hidden md:inline-block pointer-events-none">Ctrl+K</span>
                    </form>

                    <!-- Live Autocomplete Dropdown -->
                    <div id="liveSearchResults" class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-outline-variant rounded-xl shadow-2xl z-50 hidden overflow-hidden divide-y divide-surface-variant/60 max-h-96 overflow-y-auto">
                        <!-- Rendered by liveSearch JS -->
                    </div>
                </div>

                <!-- Desktop Trailing Session & Realtime Timestamp + Active Status -->
                <div class="hidden md:flex flex-wrap items-center gap-2.5 text-on-surface-variant text-[12px] font-label-md">
                    <div class="flex items-center gap-1 text-emerald-800 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 text-[11px]" title="Jurisconsult Status: Active">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        <span>Active Jurisconsult</span>
                    </div>
                    <span class="text-outline-variant">•</span>
                    <div class="flex items-center gap-1 px-2.5 py-1 rounded bg-surface-container border border-outline-variant/50 text-secondary">
                        <span class="material-symbols-outlined text-[15px]">timer</span>
                        <span id="sessionTimerText">Spent: 0m</span>
                    </div>
                    <span class="text-outline-variant">•</span>
                    <div class="flex items-center gap-1.5 text-on-surface-variant font-medium">
                        <span class="material-symbols-outlined text-[16px] text-outline">event_upcoming</span>
                        <span id="liveClockDisplay">{{ \Carbon\Carbon::now()->format('l, d M Y • h:i:s A') }}</span>
                    </div>
                </div>

                <!-- Mobile Hamburger Button -->
                <button type="button" id="mobileMenuToggle" class="lg:hidden p-2 rounded-lg text-primary bg-surface-container hover:bg-surface-container-high border border-outline-variant transition-colors flex items-center justify-center focus:outline-none" aria-label="Toggle Navigation">
                    <span class="material-symbols-outlined text-[24px]" id="menuIcon">menu</span>
                </button>
            </div>

            <!-- Lower Header Bar: Desktop Navigation (Clean Left-Aligned) -->
            <nav class="hidden lg:flex items-center justify-start pt-2.5">
                <ul class="flex items-center gap-6 xl:gap-8 list-none m-0 p-0 text-nowrap">
                    <!-- Dashboard -->
                    <li>
                        <a class="{{ request()->routeIs('mufti.dashboard') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1.5 transition-all"
                           href="{{ route('mufti.dashboard') }}">
                            <span class="material-symbols-outlined text-[17px]">dashboard</span>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Search -->
                    <li>
                        <a class="{{ request()->routeIs('mufti.search') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1.5"
                           href="{{ route('mufti.search') }}">
                            <span class="material-symbols-outlined text-[17px]">search</span>
                            <span>Search</span>
                        </a>
                    </li>

                    <!-- My Questions (Dropdown) -->
                    <li class="relative group">
                        <a href="{{ route('mufti.questions') }}"
                           class="{{ request()->routeIs('mufti.questions') && !request('status') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1 py-1">
                            <span class="material-symbols-outlined text-[17px]">assignment</span>
                            <span>My Questions</span>
                            <span class="material-symbols-outlined text-[16px] transition-transform duration-150 group-hover:rotate-180">expand_more</span>
                        </a>
                        <!-- Dropdown Menu -->
                        <div class="absolute left-0 top-full mt-1 w-52 bg-white border border-outline-variant rounded-lg shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-150 z-50 py-1.5">
                            <a class="block px-4 py-2 text-[13px] text-on-surface hover:bg-surface-container hover:text-primary transition-colors duration-150 {{ request('tab') == 'assigned' || (!request('tab') && request()->routeIs('mufti.questions')) ? 'font-bold text-primary bg-surface-container' : '' }}"
                               href="{{ route('mufti.questions', ['tab' => 'assigned']) }}">
                                Assigned Inquiries
                            </a>
                            <a class="block px-4 py-2 text-[13px] text-on-surface hover:bg-surface-container hover:text-primary transition-colors duration-150 {{ request('tab') == 'verified' ? 'font-bold text-primary bg-surface-container' : '' }}"
                               href="{{ route('mufti.questions', ['tab' => 'verified']) }}">
                                Verified by Me
                            </a>
                            @if(Auth::user()->can_check_approve)
                            <a class="block px-4 py-2 text-[13px] text-on-surface hover:bg-surface-container hover:text-primary transition-colors duration-150 {{ request('tab') == 'approved' ? 'font-bold text-primary bg-surface-container' : '' }}"
                               href="{{ route('mufti.questions', ['tab' => 'approved']) }}">
                                Approved by Me
                            </a>
                            @endif
                            <div class="my-1 border-t border-surface-variant/60"></div>
                            <a class="block px-4 py-2 text-[13px] text-emerald-800 hover:bg-emerald-50 font-medium transition-colors duration-150 {{ request()->routeIs('mufti.questions.published') ? 'font-bold bg-emerald-50' : '' }}"
                               href="{{ route('mufti.questions.published') }}">
                                <span class="material-symbols-outlined text-[15px] align-middle mr-1 text-emerald-700">menu_book</span>
                                Published Fatwas
                            </a>
                        </div>
                    </li>

                    <!-- Published (Top-level) -->
                    <li>
                        <a class="{{ request()->routeIs('mufti.questions.published') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1.5"
                           href="{{ route('mufti.questions.published') }}">
                            <span class="material-symbols-outlined text-[17px]">menu_book</span>
                            <span>Published</span>
                        </a>
                    </li>

                    <!-- Pending Tasks -->
                    <li>
                        <a class="{{ request()->routeIs('mufti.questions.pending') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1.5"
                           href="{{ route('mufti.questions.pending') }}">
                            <span class="material-symbols-outlined text-[17px]">hourglass_top</span>
                            <span>Pending Tasks</span>
                        </a>
                    </li>

                    <!-- Profile -->
                    <li>
                        <a class="{{ request()->routeIs('mufti.profile') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1.5"
                           href="{{ route('mufti.profile') }}">
                            <span class="material-symbols-outlined text-[17px]">badge</span>
                            <span>Profile</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li>
                        <a class="{{ request()->routeIs('mufti.reports*') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary font-medium' }} text-[14px] inline-flex items-center gap-1.5"
                           href="{{ route('mufti.reports') }}">
                            <span class="material-symbols-outlined text-[17px]">bar_chart</span>
                            <span>Reports</span>
                        </a>
                    </li>

                    <!-- Public Site -->
                    <li>
                        <a class="text-on-surface-variant hover:text-primary text-[14px] font-medium inline-flex items-center gap-1"
                           href="{{ route('home') }}" target="_blank" title="View Public Website">
                            <span class="material-symbols-outlined text-[16px]">public</span>
                            <span>Public Site</span>
                            <span class="material-symbols-outlined text-[12px] text-outline">open_in_new</span>
                        </a>
                    </li>

                    <!-- Logout -->
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-on-surface-variant hover:text-red-700 text-[14px] font-medium inline-flex items-center gap-1 bg-transparent border-0 p-0 cursor-pointer" title="Logout from Mufti Panel">
                                <span class="material-symbols-outlined text-[16px]">logout</span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>

            <!-- Mobile Drawer Navigation (Visible on mobile/tablet when toggled) -->
            <div id="mobileDrawer" class="lg:hidden hidden pt-3 mt-2 border-t border-surface-variant/80 transition-all duration-200">
                <!-- Mobile Session & Scholar Profile Box -->
                <div class="flex items-center justify-between bg-surface-container p-3 rounded-lg border border-outline-variant/50 mb-3 text-[12px]">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ Auth::user()->avatar ? asset(ltrim(Auth::user()->avatar, '/')) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=092217&color=fff' }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="w-9 h-9 rounded-full object-cover border border-outline-variant/80">
                        <div>
                            <div class="font-bold text-on-surface text-[13px] leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-[11px] text-outline flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[13px]">timer</span>
                                <span id="sessionTimerTextMobile">Spent: 0m</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 text-emerald-800 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        <span>Active</span>
                    </div>
                </div>

                <!-- Mobile Global Search Box -->
                <div class="mb-3">
                    <form action="{{ route('mufti.search') }}" method="GET" class="relative m-0">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-[18px] text-outline pointer-events-none">search</span>
                        <input type="text" 
                               name="q" 
                               value="{{ request('q') }}"
                               placeholder="Search all fatwas, #ID, keywords..." 
                               class="w-full pl-9 pr-3 py-2 text-[13px] bg-surface-container border border-outline-variant rounded-lg focus:outline-none focus:ring-1 focus:ring-primary focus:bg-white text-on-surface">
                    </form>
                </div>

                <ul class="flex flex-col gap-1 list-none m-0 p-0 text-[15px]">
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.dashboard') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.dashboard') }}">
                            <span class="material-symbols-outlined text-[20px]">dashboard</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.search') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.search') }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">search</span>
                                <span>Search</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.questions') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.questions') }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">assignment</span>
                                <span>My Questions</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.questions.published') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.questions.published') }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">menu_book</span>
                                <span>Published</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.questions.pending') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.questions.pending') }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">hourglass_top</span>
                                <span>Pending Tasks</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.profile') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.profile') }}">
                            <span class="material-symbols-outlined text-[20px]">badge</span>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('mufti.reports*') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface hover:bg-surface-container' }}"
                           href="{{ route('mufti.reports') }}">
                            <span class="material-symbols-outlined text-[20px]">bar_chart</span>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container"
                           href="{{ route('home') }}" target="_blank">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">public</span>
                                <span>Public Site</span>
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-outline">open_in_new</span>
                        </a>
                    </li>
                    <li class="pt-2 border-t border-surface-variant/60 mt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-700 hover:bg-red-50 font-medium text-left border-0 bg-transparent cursor-pointer">
                                <span class="material-symbols-outlined text-[20px]">logout</span>
                                <span>Logout ({{ Auth::user()->name }})</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Global Toast / Notifications -->
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-4 py-3 rounded-lg flex items-center gap-2 text-sm shadow-sm mb-4">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-300 text-red-900 px-4 py-3 rounded-lg flex items-center gap-2 text-sm shadow-sm mb-4">
                <span class="material-symbols-outlined text-red-600">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content Canvas with Standard Max-Width & Matching Padding -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6 flex flex-col gap-6">
        @yield('content')
    </main>

    <!-- Clean Footer Bar -->
    <footer class="w-full border-t border-surface-variant bg-surface py-5 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12px] text-outline font-label-md text-center sm:text-left">
            <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-start">
                <img src="{{ asset('assets/logo/fevi.png') }}" class="h-4 w-4" alt="Darul Ifta Icon">
                <span class="text-primary font-medium">Mufti Panel</span>
                <span>— Verified Islamic Jurisprudence Environment</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap justify-center sm:justify-end">
                <span>App created & developed by <a href="https://www.harisim.site" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">MUFTI Haris I M</a></span>
                <span class="text-surface-variant">|</span>
                <span>Logged in: <strong class="text-on-surface font-medium">{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile Drawer Toggle
        const toggleBtn = document.getElementById('mobileMenuToggle');
        const drawer = document.getElementById('mobileDrawer');
        const menuIcon = document.getElementById('menuIcon');
        if (toggleBtn && drawer) {
            toggleBtn.addEventListener('click', function() {
                drawer.classList.toggle('hidden');
                if (drawer.classList.contains('hidden')) {
                    menuIcon.textContent = 'menu';
                } else {
                    menuIcon.textContent = 'close';
                }
            });
        }

        // Session Time Tracker
        const sessionStartTime = Date.now();
        function updateSessionTimer() {
            const elapsedMins = Math.floor((Date.now() - sessionStartTime) / 60000);
            const timerEl = document.getElementById('sessionTimerText');
            const timerMobileEl = document.getElementById('sessionTimerTextMobile');
            if (timerEl) timerEl.textContent = `Spent: ${elapsedMins}m`;
            if (timerMobileEl) timerMobileEl.textContent = `Spent: ${elapsedMins}m`;
        }
        setInterval(updateSessionTimer, 30000);

        // Realtime Clock
        function updateLiveClock() {
            const now = new Date();
            const clockEl = document.getElementById('liveClockDisplay');
            if (clockEl) {
                const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                clockEl.textContent = now.toLocaleDateString('en-GB', options);
            }
        }
        setInterval(updateLiveClock, 1000);

        // Global Fatwa Search Box & Keyboard Shortcut (Ctrl+K or /)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('globalFatwaSearchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });

        const searchInput = document.getElementById('globalFatwaSearchInput');
        const resultsBox = document.getElementById('liveSearchResults');
        let debounceTimer;

        if (searchInput && resultsBox) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const val = this.value.trim();

                if (val.length < 2) {
                    resultsBox.innerHTML = '';
                    resultsBox.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('mufti.api.search') }}?q=${encodeURIComponent(val)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.results || data.results.length === 0) {
                                resultsBox.innerHTML = `
                                    <div class="p-4 text-center text-[12px] text-on-surface-variant">
                                        No fatwas matched "<strong>${escapeHtml(val)}</strong>".
                                        <div class="mt-1">
                                            <a href="{{ route('mufti.search') }}?q=${encodeURIComponent(val)}" class="text-primary hover:underline font-semibold">Open Full Search</a>
                                        </div>
                                    </div>
                                `;
                                resultsBox.classList.remove('hidden');
                                return;
                            }

                            let html = `
                                <div class="px-3 py-1.5 bg-surface-container-low text-[11px] font-bold uppercase tracking-wider text-outline flex justify-between items-center">
                                    <span>Live Scholarly Results</span>
                                    <span>${data.results.length} found</span>
                                </div>
                            `;

                            data.results.forEach(item => {
                                const statusPill = item.status === 'published' 
                                    ? '<span class="px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-800 text-[10px] font-semibold border border-emerald-200">Published</span>'
                                    : '<span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-800 text-[10px] font-semibold border border-amber-200">' + item.status_label + '</span>';

                                html += `
                                    <a href="${item.read_url}" class="block p-3 hover:bg-surface-container text-left transition-colors duration-100 group">
                                        <div class="flex items-center justify-between gap-2 mb-0.5">
                                            <span class="font-mono text-[11px] font-bold text-primary bg-surface-container px-1.5 py-0.2 rounded border border-outline-variant">
                                                ${escapeHtml(item.question_id)}
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] text-secondary font-medium">${escapeHtml(item.madhab)}</span>
                                                ${statusPill}
                                            </div>
                                        </div>
                                        <div class="font-semibold text-on-surface text-[13px] group-hover:text-primary line-clamp-1">
                                            ${escapeHtml(item.title)}
                                        </div>
                                        <div class="text-[11px] text-on-surface-variant line-clamp-1 mt-0.5">
                                            ${escapeHtml(item.snippet)}
                                        </div>
                                    </a>
                                `;
                            });

                            html += `
                                <div class="p-2 bg-surface-container-low text-center">
                                    <a href="{{ route('mufti.search') }}?q=${encodeURIComponent(val)}" class="text-[12px] text-primary hover:underline font-bold inline-flex items-center gap-1">
                                        <span>View all matching fatwas</span>
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </div>
                            `;

                            resultsBox.innerHTML = html;
                            resultsBox.classList.remove('hidden');
                        })
                        .catch(() => {
                            resultsBox.classList.add('hidden');
                        });
                }, 220);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    resultsBox.classList.add('hidden');
                }
            });

            // Re-open if input focused with existing content
            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2 && resultsBox.innerHTML.trim() !== '') {
                    resultsBox.classList.remove('hidden');
                }
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[m];
            });
        }

        // Domain-Wide Universal Live Presence Tracker
    </script>
    @include('components.universal-presence-tracker')
    @stack('scripts')
</body>
</html>