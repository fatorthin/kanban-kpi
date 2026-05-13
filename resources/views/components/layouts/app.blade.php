<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: localStorage.getItem('darkMode') === 'true',
          toggleDarkMode() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('darkMode', this.darkMode);
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="KPI App — Internal Task Management & Performance Platform">
    <title>{{ config('app.name', 'RafaTax KPI') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script>
        function applyTheme() {
            if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        applyTheme();
        document.addEventListener('livewire:navigated', applyTheme);
    </script>
</head>

<body>
    <div class="app-layout" x-data="{ mobileNavOpen: false }" @keydown.escape.window="mobileNavOpen = false">

        {{-- ============================================================ Sidebar --}}
        <aside class="sidebar" :class="{ 'mobile-open': mobileNavOpen }">
            <div class="sidebar-logo">
                <h1>RafaTax <span>KPI</span></h1>
                <button type="button" class="mobile-sidebar-close" @click="mobileNavOpen = false" aria-label="Close navigation menu">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="sidebar-nav" @click="if (window.innerWidth <= 768) mobileNavOpen = false">
                <span class="nav-section-label">Operations</span>

                <a href="{{ route('dashboard') }}" wire:navigate class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('kanban') }}" wire:navigate class="nav-link {{ request()->routeIs('kanban') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Kanban Board
                </a>

                <a href="{{ route('recurring-tasks') }}" wire:navigate class="nav-link {{ request()->routeIs('recurring-tasks') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Recurring Tasks
                </a>

                @if (auth()->user()->isManager() || auth()->user()->isDirector())
                    <span class="nav-section-label">Management</span>

                    <a href="{{ route('task-library') }}" wire:navigate class="nav-link {{ request()->routeIs('task-library') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        Task Library
                    </a>

                    <a href="{{ route('clients') }}" wire:navigate class="nav-link {{ request()->routeIs('clients') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        Clients
                    </a>
                @endif

                <span class="nav-section-label">Reports</span>

                <a href="{{ route('kpi-reports') }}" wire:navigate class="nav-link {{ request()->routeIs('kpi-reports') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    KPI Reports
                </a>

                @if (auth()->user()->isDirector())
                    <span class="nav-section-label">Administration</span>
                    <a href="{{ route('staff') }}" wire:navigate class="nav-link {{ request()->routeIs('staff') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        User Management
                    </a>
                    <a href="{{ route('activity-logs') }}" wire:navigate class="nav-link {{ request()->routeIs('activity-logs') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                        </svg>
                        Activity Logs
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <div style="width:32px;height:32px;border-radius:9999px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:13px;font-weight:500;truncate;">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px;color:var(--color-neutral);">{{ auth()->user()->roles->first()?->name }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm" style="width:100%;justify-content:flex-start;">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <div class="sidebar-overlay" x-show="mobileNavOpen" x-transition.opacity @click="mobileNavOpen = false" style="display:none;"></div>

        {{-- ============================================================ Main --}}
        <div class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="mobile-nav-btn" @click="mobileNavOpen = true" aria-label="Open navigation menu">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div class="topbar-date">
                        {{ now()->format('l, d F Y') }}
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-division">
                        {{ auth()->user()->division?->name ?? 'No Division' }}
                    </div>

                    <button type="button" @click="toggleDarkMode" class="btn btn-ghost btn-sm" style="width:38px;height:38px;border-radius:9999px;padding:0;border:1px solid var(--color-border);" title="Toggle Dark Mode">
                        <svg x-show="!darkMode" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                        <svg x-show="darkMode" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9.75-9h-2.25m-13.5 0H3m15.364-6.364l-1.591 1.591M6.756 17.244l-1.591 1.591m12.728 0l-1.591-1.591M6.756 6.756L5.165 5.165M12 7.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9z" />
                        </svg>
                    </button>

                    @livewire('activity-notifications')
                </div>
            </header>

            <main class="page-content">
                {{ $slot }}
            </main>
        </div>

    </div>

    {{-- ============================================================ Toast --}}
    <div class="toast-container" x-data="{ toasts: [] }" @notify.window="
    const t = { id: Date.now(), type: $event.detail.type, message: $event.detail.message };
    toasts.push(t);
    setTimeout(() => toasts = toasts.filter(x => x.id !== t.id), 4000);
">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast" :class="toast.type === 'success' ? 'toast-success' : 'toast-error'">
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>

</html>
