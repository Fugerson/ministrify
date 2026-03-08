@extends('layouts.landing')

@section('title', __('landing.home_title'))
@section('description', __('landing.home_meta'))
@section('keywords', __('landing.home_keywords'))

@section('content')
{{-- Hero Section --}}
<section class="hero-gradient pt-24 pb-16 md:pt-32 md:pb-24 overflow-hidden relative">
    {{-- Animated blobs --}}
    <div class="blob w-96 h-96 bg-blue-400 top-0 -left-20" style="animation: blob-float 8s ease-in-out infinite"></div>
    <div class="blob w-80 h-80 bg-indigo-400 top-20 right-0" style="animation: blob-float 10s ease-in-out infinite 2s"></div>
    <div class="blob w-64 h-64 bg-blue-300 bottom-0 left-1/3" style="animation: blob-float 12s ease-in-out infinite 4s"></div>

    {{-- Grid pattern overlay --}}
    <div class="absolute inset-0 grid-pattern"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            {{-- Left: Text --}}
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-blue-100/80 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-6 backdrop-blur-sm border border-blue-200/50 dark:border-blue-700/30" style="animation: fade-in-up 0.6s ease-out both">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                    {{ __('landing.system_church_management') }}
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6" style="animation: fade-in-up 0.6s ease-out 0.1s both">
                    {{ __('landing.manage_church') }}
                    <span class="gradient-text">{{ __('landing.simple_effective') }}</span>
                </h1>

                <p class="text-lg md:text-xl text-gray-600 dark:text-blue-200/70 mb-8 max-w-2xl mx-auto lg:mx-0" style="animation: fade-in-up 0.6s ease-out 0.2s both">
                    {{ __('landing.modern_platform') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start" style="animation: fade-in-up 0.6s ease-out 0.3s both">
                    <a href="{{ url('/register-church') }}" class="glow-btn inline-flex items-center justify-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all hover:scale-105 z-10">
                        <span>{{ __('landing.start_free') }}</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#demo" class="inline-flex items-center justify-center px-8 py-4 glass-card text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:scale-105 transition-all">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('landing.watch_demo') }}
                    </a>
                </div>

                {{-- Trust badges --}}
                <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-gray-500 dark:text-blue-300/60" style="animation: fade-in-up 0.6s ease-out 0.4s both">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('landing.ssl_protection') }}
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                        </svg>
                        {{ __('landing.cloud_solution') }}
                    </div>
                    <div class="flex items-center">
                        <span class="text-xl mr-2">🇺🇦</span>
                        {{ __('landing.ukrainian_language') }}
                    </div>
                </div>
            </div>

            {{-- Right: Hero Image / Dashboard Preview --}}
            <div class="relative" style="animation: fade-in-up 0.8s ease-out 0.3s both">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl shadow-blue-600/20 border border-white/20 dark:border-blue-500/20 border-gradient" style="animation: float-medium 6s ease-in-out infinite">
                    <div class="bg-gray-100/80 dark:bg-gray-800/80 backdrop-blur-sm px-4 py-3 flex items-center space-x-2 border-b border-gray-200/50 dark:border-gray-700/50">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="ml-4 text-sm text-gray-500 dark:text-gray-400">ministrify.app/dashboard</span>
                    </div>
                    <img src="/icons/demo/Screenshot_7.jpg" alt="Ministrify Dashboard" class="w-full">
                </div>

                {{-- Floating element --}}
                <div class="absolute -bottom-4 -left-4 glass-card rounded-2xl p-4 shadow-xl" style="animation: float-slow 4s ease-in-out infinite">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Telegram</div>
                            <div class="text-xs text-gray-500 dark:text-blue-300/60">{{ __('landing.notifications') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Floating stats card --}}
                <div class="absolute -top-3 -right-3 glass-card rounded-xl p-3 shadow-lg" style="animation: float-slow 5s ease-in-out infinite 1s">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-blue-300/60">{{ __('landing.members') }}</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white">+12%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Logos / Trust Section --}}
<section class="py-12 bg-white dark:bg-gray-950 border-y border-gray-100 dark:border-gray-800/50 relative overflow-hidden">
    <div class="absolute inset-0 grid-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <p class="text-center text-sm text-gray-500 dark:text-gray-500 mb-8 reveal">{{ __('landing.integrated_services') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 reveal reveal-delay-1">
            <div class="flex items-center space-x-2 text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-300">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
                <span class="font-medium">Google Calendar</span>
            </div>
            <div class="flex items-center space-x-2 text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-300">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
                <span class="font-medium">Telegram</span>
            </div>
            <div class="flex items-center space-x-2 text-gray-400 dark:text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-300">
                <span class="text-2xl font-bold">LiqPay</span>
            </div>
            <div class="flex items-center space-x-2 text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors duration-300">
                <span class="text-2xl font-bold">monobank</span>
            </div>
        </div>
    </div>
</section>

{{-- Features Section --}}
<section id="features" class="py-20 bg-white dark:bg-gray-950 relative overflow-hidden">
    <div class="absolute inset-0 grid-pattern"></div>
    {{-- Decorative blobs --}}
    <div class="blob w-72 h-72 bg-blue-300 -top-20 -right-20" style="animation: blob-float 10s ease-in-out infinite"></div>
    <div class="blob w-60 h-60 bg-indigo-300 bottom-0 -left-10" style="animation: blob-float 12s ease-in-out infinite 3s"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="reveal inline-block px-4 py-1.5 rounded-full bg-blue-100/80 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4 backdrop-blur-sm border border-blue-200/50 dark:border-blue-700/30">{{ __('landing.features') }}</span>
            <h2 class="reveal reveal-delay-1 text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('landing.effective_management') }}
            </h2>
            <p class="reveal reveal-delay-2 text-lg text-gray-600 dark:text-blue-200/60 max-w-2xl mx-auto">
                {{ __('landing.features_description') }}
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $features = [
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'landing.manage_members', 'desc' => 'landing.manage_members_desc'],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'landing.online_donations', 'desc' => 'landing.online_donations_desc'],
                    ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'title' => 'landing.events_calendar', 'desc' => 'landing.events_calendar_desc'],
                    ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'title' => 'landing.teams_groups', 'desc' => 'landing.teams_groups_desc'],
                    ['icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3', 'title' => 'landing.songs_library', 'desc' => 'landing.songs_library_desc'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => 'landing.task_tracker', 'desc' => 'landing.task_tracker_desc'],
                ];
            @endphp

            @foreach($features as $i => $feature)
            <div class="reveal reveal-delay-{{ ($i % 3) + 1 }} group glass-card rounded-2xl p-8 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-500 hover:-translate-y-2 cursor-default">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg shadow-blue-500/25">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ __($feature['title']) }}</h3>
                <p class="text-gray-600 dark:text-blue-200/60">
                    {{ __($feature['desc']) }}
                </p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12 reveal">
            <a href="{{ url('/features') }}" class="inline-flex items-center text-blue-600 dark:text-blue-400 font-semibold hover:text-blue-700 dark:hover:text-blue-300 transition-colors group">
                {{ __('landing.all_features') }}
                <svg class="w-5 h-5 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Screenshots Gallery Section --}}
<section id="demo" class="py-20 bg-gray-50 dark:bg-gray-900/50 overflow-hidden relative">
    <div class="absolute inset-0 grid-pattern"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-12">
            <span class="reveal inline-block px-4 py-1.5 rounded-full bg-blue-100/80 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4 backdrop-blur-sm border border-blue-200/50 dark:border-blue-700/30">{{ __('landing.demo') }}</span>
            <h2 class="reveal reveal-delay-1 text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('landing.see_how_it_works') }}
            </h2>
            <p class="reveal reveal-delay-2 text-lg text-gray-600 dark:text-blue-200/60 max-w-2xl mx-auto">
                {{ __('landing.intuitive_interface') }}
            </p>
        </div>

        {{-- Screenshot Gallery --}}
        <div class="reveal reveal-delay-2" x-data="{
            activeSlide: 0,
            slides: 7,
            fullscreen: false,
            images: [
                { src: '/icons/demo/Screenshot_7.jpg', label: @js( __('landing.home') ) },
                { src: '/icons/demo/Screenshot_2.jpg', label: @js( __('landing.people') ) },
                { src: '/icons/demo/Screenshot_5.jpg', label: @js( __('landing.teams') ) },
                { src: '/icons/demo/Screenshot_4.jpg', label: @js( __('landing.schedule') ) },
                { src: '/icons/demo/Screenshot_3.jpg', label: @js( __('landing.finances') ) },
                { src: '/icons/demo/Screenshot_6.jpg', label: @js( __('landing.tasks') ) },
                { src: '/icons/demo/Screenshot_8.jpg', label: @js( __('landing.settings') ) }
            ],
            openFullscreen(index) {
                this.activeSlide = index;
                this.fullscreen = true;
                document.body.style.overflow = 'hidden';
            },
            closeFullscreen() {
                this.fullscreen = false;
                document.body.style.overflow = '';
            },
            next() {
                this.activeSlide = (this.activeSlide + 1) % this.slides;
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.slides) % this.slides;
            }
        }"
        @keydown.escape.window="closeFullscreen()"
        @keydown.arrow-right.window="if(fullscreen) next()"
        @keydown.arrow-left.window="if(fullscreen) prev()">

            {{-- Main Featured Screenshot --}}
            <div class="relative mb-6">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl shadow-blue-600/20 glass-card cursor-pointer group"
                     @click="openFullscreen(activeSlide)">
                    <div class="bg-gray-100/80 dark:bg-gray-800/80 backdrop-blur-sm px-4 py-3 flex items-center space-x-2 border-b border-gray-200/50 dark:border-gray-700/50">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="ml-4 text-sm text-gray-500 dark:text-gray-400">ministrify.app</span>
                    </div>
                    <div class="relative">
                        <template x-for="(img, index) in images" :key="index">
                            <img x-show="activeSlide === index"
                                 :src="img.src"
                                 :alt="img.label"
                                 class="w-full"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                        </template>

                        {{-- Fullscreen hint --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center pointer-events-none">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-black/60 backdrop-blur-sm rounded-full p-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Desktop Navigation Arrows --}}
                <button @click="prev()" class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full shadow-lg items-center justify-center hover:bg-white dark:hover:bg-gray-700 transition-all hover:scale-110 z-10">
                    <svg class="w-6 h-6 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click="next()" class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full shadow-lg items-center justify-center hover:bg-white dark:hover:bg-gray-700 transition-all hover:scale-110 z-10">
                    <svg class="w-6 h-6 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Thumbnail Grid --}}
            <div class="grid grid-cols-4 md:grid-cols-7 gap-2 md:gap-3">
                <template x-for="(img, index) in images" :key="index">
                    <button @click="activeSlide = index"
                            class="relative rounded-lg md:rounded-xl overflow-hidden border-2 transition-all duration-300 hover:scale-105 aspect-video"
                            :class="activeSlide === index ? 'border-blue-500 shadow-lg shadow-blue-500/30 ring-2 ring-blue-500/50' : 'border-gray-200 dark:border-gray-700 hover:border-blue-400'">
                        <img :src="img.src" :alt="img.label" class="w-full h-full object-cover object-top">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end justify-center pb-1">
                            <span class="text-white text-[9px] md:text-xs font-medium truncate px-1" x-text="img.label"></span>
                        </div>
                    </button>
                </template>
            </div>

            {{-- Fullscreen Lightbox --}}
            <div x-show="fullscreen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 bg-black/95 backdrop-blur-sm flex flex-col"
                 @click.self="closeFullscreen()">

                <div class="flex items-center justify-between p-4 text-white">
                    <span class="text-sm font-medium" x-text="images[activeSlide]?.label"></span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-400" x-text="(activeSlide + 1) + ' / ' + slides"></span>
                        <button @click="closeFullscreen()" class="ml-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 flex items-center justify-center px-4 pb-4 overflow-hidden">
                    <img :src="images[activeSlide]?.src"
                         :alt="images[activeSlide]?.label"
                         class="max-w-full max-h-full object-contain select-none"
                         @click.stop>
                </div>

                <button @click.stop="prev()" class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click.stop="next()" class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div class="p-4 bg-black/50">
                    <div class="flex justify-center gap-2 overflow-x-auto scrollbar-hide">
                        <template x-for="(img, index) in images" :key="index">
                            <button @click.stop="activeSlide = index"
                                    class="flex-shrink-0 w-16 h-10 md:w-20 md:h-12 rounded-lg overflow-hidden border-2 transition-all"
                                    :class="activeSlide === index ? 'border-white opacity-100' : 'border-transparent opacity-50 hover:opacity-75'">
                                <img :src="img.src" :alt="img.label" class="w-full h-full object-cover object-top">
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feature highlights --}}
        <div class="mt-16 grid md:grid-cols-3 gap-8">
            <div class="reveal reveal-delay-1 text-center glass-card rounded-2xl p-6 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('landing.mobile_version') }}</h3>
                <p class="text-gray-600 dark:text-blue-200/60 text-sm">{{ __('landing.mobile_version_desc') }}</p>
            </div>
            <div class="reveal reveal-delay-2 text-center glass-card rounded-2xl p-6 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('landing.lightning_fast') }}</h3>
                <p class="text-gray-600 dark:text-blue-200/60 text-sm">{{ __('landing.lightning_fast_desc') }}</p>
            </div>
            <div class="reveal reveal-delay-3 text-center glass-card rounded-2xl p-6 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('landing.dark_theme') }}</h3>
                <p class="text-gray-600 dark:text-blue-200/60 text-sm">{{ __('landing.dark_theme_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Stats Section --}}
<section class="py-20 relative overflow-hidden">
    {{-- Animated gradient background --}}
    <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800" style="background-size: 200% 200%; animation: gradient-shift 6s ease infinite"></div>
    <div class="absolute inset-0 grid-pattern opacity-10"></div>

    {{-- Decorative elements --}}
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="reveal reveal-delay-1">
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 stat-number" data-count="{{ $stats['churches'] ?? 10 }}" data-suffix="+">0</div>
                <div class="text-blue-200/80 font-medium">{{ __('landing.churches') }}</div>
            </div>
            <div class="reveal reveal-delay-2">
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 stat-number" data-count="{{ $stats['members'] ?? 500 }}" data-suffix="+">0</div>
                <div class="text-blue-200/80 font-medium">{{ __('landing.members') }}</div>
            </div>
            <div class="reveal reveal-delay-3">
                <div class="text-4xl md:text-5xl font-bold text-white mb-2 stat-number" data-count="{{ $stats['events'] ?? 100 }}" data-suffix="+">0</div>
                <div class="text-blue-200/80 font-medium">{{ __('landing.events') }}</div>
            </div>
            <div class="reveal reveal-delay-4">
                <div class="text-4xl md:text-5xl font-bold text-white mb-2">99.9%</div>
                <div class="text-blue-200/80 font-medium">{{ __('landing.uptime') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing Preview - HIDDEN --}}
{{-- ... --}}

{{-- CTA Section --}}
<section class="py-24 bg-white dark:bg-gray-950 relative overflow-hidden">
    <div class="absolute inset-0 grid-pattern"></div>
    {{-- Blobs --}}
    <div class="blob w-80 h-80 bg-blue-300 top-0 left-1/4" style="animation: blob-float 10s ease-in-out infinite"></div>
    <div class="blob w-60 h-60 bg-indigo-300 bottom-0 right-1/4" style="animation: blob-float 8s ease-in-out infinite 2s"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h2 class="reveal text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('landing.ready_simplify') }}
        </h2>
        <p class="reveal reveal-delay-1 text-lg text-gray-600 dark:text-blue-200/60 mb-10">
            {{ __('landing.join_churches') }}
        </p>
        <div class="reveal reveal-delay-2 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/register-church') }}" class="glow-btn inline-flex items-center justify-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all hover:scale-105 z-10">
                {{ __('landing.start_free') }}
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center px-8 py-4 glass-card text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:shadow-lg transition-all hover:scale-105">
                {{ __('landing.contacts') }}
            </a>
        </div>
    </div>
</section>
@endsection
