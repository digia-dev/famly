<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Famly') — {{ config('app.name', 'Institutional Orchestration') }}</title>

        <!-- PWA Meta Tags -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#006d36">
        <link rel="apple-touch-icon" href="{{ asset('logo-192.png') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <!-- New Premium Typography: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
        
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- NProgress Configuration -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
        <style>
            #nprogress .bar { 
                background: #006d36 !important; 
                height: 4px !important; 
                position: fixed;
                z-index: 1031;
                bottom: 0 !important;
                top: auto !important;
                left: 0;
                width: 100%;
                box-shadow: 0 -2px 10px rgba(0, 109, 54, 0.3);
            }
            #nprogress .peg { 
                display: none !important;
            }
            #nprogress .spinner {
                display: none !important;
            }

            [x-cloak] { display: none !important; }

            /* Advanced Overlay for Glassmorphism */
            .glass-overlay {
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                background-color: rgba(255, 255, 255, 0.7);
            }

            /* Background Pattern Global */
            .global-bg-pattern {
                position: fixed;
                inset: 0;
                z-index: -1;
                opacity: 0.03;
                background-image: url('{{ asset("images/patterns/header-pattern.svg") }}');
                background-size: 100px 100px;
                pointer-events: none;
            }
        </style>
    </head>
    <body class="antialiased text-slate-900 bg-[#F6F7F8] selection:bg-primary/20">
        
        <div class="global-bg-pattern"></div>
        
        <div class="min-h-screen">
            @auth
                @include('layouts.navigation')
            @endauth
            
            <!-- Main Content Wrapper (With Bottom Nav Safe Area) -->
            <div class="{{ (request()->is('admin/dashboard*') || request()->routeIs(['ai.assistant', 'reports.*'])) ? 'sm:ml-64 pb-32' : 'sm:ml-64 pt-14 pb-24' }} transition-all duration-300">
                <main>
                    @if(session('success'))
                        <x-toast type="success" :message="session('success')" />
                    @endif
                    @if(session('error'))
                        <x-toast type="error" :message="session('error')" />
                    @endif
                    @if(session('status'))
                        <x-toast type="info" :message="session('status')" />
                    @endif

                    @yield('content')
                    {{ $slot ?? '' }}
                </main>
            </div>
        </div>

        <x-search-overlay />
        @stack('scripts')

        <!-- NProgress Logic -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
        <script>
            // Initialization
            NProgress.configure({ 
                showSpinner: false, 
                speed: 300, 
                minimum: 0.2,
                easing: 'ease'
            });

            // Start on click for internal links
            document.addEventListener('click', function(e) {
                const target = e.target.closest('a');
                if (target && 
                    target.href && 
                    target.href.startsWith(window.location.origin) && 
                    !target.getAttribute('target') &&
                    !target.href.includes('#') &&
                    !target.hasAttribute('download')) {
                    NProgress.start();
                }
            });

            // Start on form submission
            document.addEventListener('submit', function() {
                NProgress.start();
            });

            // Initial load
            NProgress.start();
            window.addEventListener('load', function() {
                NProgress.done();
            });

            // On navigation away
            window.onbeforeunload = function() {
                NProgress.start();
            };
        </script>
    </body>
</html>