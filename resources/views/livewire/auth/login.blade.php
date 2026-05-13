<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);padding:24px;">
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
