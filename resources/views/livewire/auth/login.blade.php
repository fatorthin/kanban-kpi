<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);padding:24px;position:relative;">
    {{-- Theme Toggle --}}
    <div style="position:absolute;top:24px;right:24px;">
        <button type="button" @click="toggleDarkMode" class="btn btn-ghost btn-sm" style="width:38px;height:38px;border-radius:9999px;padding:0;border:1px solid var(--color-border);" title="Toggle Dark Mode">
            <svg x-show="!darkMode" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
            <svg x-show="darkMode" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;display:none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9.75-9h-2.25m-13.5 0H3m15.364-6.364l-1.591 1.591M6.756 17.244l-1.591 1.591m12.728 0l-1.591-1.591M6.756 6.756L5.165 5.165M12 7.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9z" />
            </svg>
        </button>
    </div>
    <div style="width:100%;max-width:420px;">
        {{-- Logo --}}
        <div style="text-align:center;margin-bottom:40px;">
            <h1 style="font-size:28px;font-weight:700;letter-spacing:-0.04em;color:var(--color-text);">
                RafaTax <span style="color:var(--color-primary);">KPI</span>
            </h1>
            <p style="margin-top:6px;font-size:14px;color:var(--color-text-secondary);">
                Task Management & Performance Platform
            </p>
        </div>

        {{-- Card --}}
        <div class="card">
            <div class="card-header" style="border-bottom:1px solid var(--color-border);">
                <h2 style="font-size:16px;font-weight:600;">Sign in to your account</h2>
            </div>
            <div class="card-body">
                <form wire:submit="authenticate">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input id="email" type="email" class="form-input" wire:model="email"
                               placeholder="you@company.com" autocomplete="email">
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" type="password" class="form-input" wire:model="password"
                               placeholder="••••••••" autocomplete="current-password">
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                        <input id="remember" type="checkbox" wire:model="remember"
                               style="width:16px;height:16px;accent-color:var(--color-primary);">
                        <label for="remember" style="font-size:13px;color:var(--color-text-secondary);cursor:pointer;">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" id="btn-login" class="btn btn-primary btn-md" style="width:100%;"
                            wire:loading.attr="disabled" wire:loading.class="opacity-75">
                        <span wire:loading.remove>Sign In</span>
                        <span wire:loading>Signing in...</span>
                    </button>
                </form>

                <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--color-border);text-align:center;">
                    <p style="font-size:12px;color:var(--color-neutral);">
                        Demo: director@kpi.test / manager@kpi.test / staff@kpi.test<br>
                        Password: <strong>password</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
