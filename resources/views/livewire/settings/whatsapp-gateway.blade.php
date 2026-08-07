<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
        <div>
            <h1 class="page-title" style="font-size:24px;font-weight:700;letter-spacing:-0.03em;">WhatsApp Gateway Settings</h1>
            <p class="text-secondary text-sm" style="color:var(--color-text-secondary);margin-top:4px;">
                Pengaturan server WhatsApp Gateway (Engine: <code>go-whatsapp-web-multidevice</code> v9.0.0) khusus Director.
            </p>
        </div>

        <div>
            <button wire:click="testConnection" class="btn btn-secondary btn-md">
                🔄 Cek Status Koneksi Server
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="card" style="background:color-mix(in srgb, var(--color-success) 10%, transparent);border-color:var(--color-success);padding:14px 20px;margin-bottom:20px;color:var(--color-success);font-weight:500;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session()->has('info'))
        <div class="card" style="background:color-mix(in srgb, var(--color-primary) 10%, transparent);border-color:var(--color-primary);padding:14px 20px;margin-bottom:20px;color:var(--color-primary);font-weight:500;">
            ℹ️ {{ session('info') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="card" style="background:color-mix(in srgb, var(--color-error) 10%, transparent);border-color:var(--color-error);padding:14px 20px;margin-bottom:20px;color:var(--color-error);font-weight:500;">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Server Health Status Card --}}
    <div class="card" style="padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:48px;height:48px;border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:24px;background:{{ $isHealthy ? 'color-mix(in srgb, var(--color-success) 15%, transparent)' : 'color-mix(in srgb, var(--color-error) 15%, transparent)' }};">
                    {{ $isHealthy ? '🟢' : '🔴' }}
                </div>
                <div>
                    <h3 style="font-size:15px;font-weight:700;color:var(--color-text);">Status Server WhatsApp Gateway</h3>
                    <p style="font-size:13px;color:var(--color-text-secondary);margin-top:2px;">
                        {{ $statusMessage }}
                    </p>
                </div>
            </div>

            <div>
                @if($isHealthy)
                    <span class="badge badge-completed" style="font-size:13px;padding:6px 14px;">Server Online & Terhubung</span>
                @else
                    <span class="badge badge-revision" style="font-size:13px;padding:6px 14px;">Server Offline / Error</span>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;margin-bottom:24px;">
        
        {{-- Configuration Form Card --}}
        <div class="card" style="padding:24px;">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">
                ⚙️ Parameter Server Gateway
            </h3>

            <form wire:submit.prevent="saveSettings" style="display:flex;flex-direction:column;gap:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" wire:model="enabled" style="width:16px;height:16px;">
                        <span>Aktifkan Fitur WhatsApp Gateway</span>
                    </label>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Gateway Server URL</label>
                    <input type="url" class="form-input" wire:model="url" placeholder="https://wagateway.surakana.my.id">
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">HTTP Basic Auth (Username:Password)</label>
                    <input type="text" class="form-input" wire:model="auth" placeholder="admin:admin">
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">WhatsApp Device ID</label>
                    <input type="text" class="form-input" wire:model="deviceId" placeholder="8a744703-b90a-4690-b911-b1b8f2523963">
                </div>

                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <div class="form-group" style="margin:0;flex:1;">
                        <label class="form-label">Timeout (detik)</label>
                        <input type="number" class="form-input" wire:model="timeout" min="5" max="120">
                    </div>

                    <div class="form-group" style="margin:0;flex:1;display:flex;align-items:center;">
                        <label class="form-label" style="display:flex;align-items:center;gap:8px;margin-top:20px;">
                            <input type="checkbox" wire:model="verifySSL" style="width:16px;height:16px;">
                            <span>Verifikasi SSL</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top:8px;">
                    <button type="submit" class="btn btn-primary btn-md" style="width:100%;">
                        💾 Simpan Pengaturan Gateway
                    </button>
                </div>
            </form>
        </div>

        {{-- Reconnect & Pairing Section Card --}}
        <div class="card" style="padding:24px;">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">
                🔗 Reconnect Perangkat WhatsApp
            </h3>

            {{-- Tabs --}}
            <div style="display:flex;gap:8px;margin-bottom:16px;">
                <button type="button" wire:click="$set('reconnectTab', 'qr')" class="btn {{ $reconnectTab === 'qr' ? 'btn-primary' : 'btn-secondary' }} btn-sm" style="flex:1;">
                    📷 Scan QR Code
                </button>
                <button type="button" wire:click="$set('reconnectTab', 'pairing')" class="btn {{ $reconnectTab === 'pairing' ? 'btn-primary' : 'btn-secondary' }} btn-sm" style="flex:1;">
                    🔢 Pairing Code
                </button>
            </div>

            @if($reconnectTab === 'qr')
                <div style="text-align:center;padding:16px;background:var(--color-bg);border-radius:var(--radius-lg);border:1px solid var(--color-border);">
                    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px;">
                        Muat QR Code dan scan menggunakan fitur <strong>Perangkat Tertaut (Linked Devices)</strong> di aplikasi WhatsApp HP Anda.
                    </p>

                    @if($qrCode)
                        <div style="margin-bottom:12px;">
                            @if(str_starts_with($qrCode, 'data:image') || str_starts_with($qrCode, 'http'))
                                <img src="{{ $qrCode }}" alt="WhatsApp QR Code" style="max-width:200px;margin:0 auto;border-radius:8px;border:1px solid var(--color-border);">
                            @else
                                <div style="font-size:11px;word-break:break-all;background:#fff;padding:10px;border-radius:6px;border:1px solid var(--color-border);">
                                    {{ $qrCode }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <button wire:click="loadQrCode" class="btn btn-secondary btn-sm">
                        🔄 Fetch / Refresh QR Code
                    </button>
                </div>
            @else
                <div style="padding:16px;background:var(--color-bg);border-radius:var(--radius-lg);border:1px solid var(--color-border);">
                    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px;">
                        Masukkan nomor telepon WA Anda untuk mendapatkan 8-digit Pairing Code yang bisa dimasukkan di HP (*Tautkan dengan nomor telepon*).
                    </p>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label">Nomor WhatsApp Director</label>
                        <input type="text" class="form-input" wire:model="pairingPhone" placeholder="6285641574131">
                    </div>

                    <button wire:click="requestPairingCode" class="btn btn-primary btn-sm" style="width:100%;margin-bottom:12px;">
                        🔑 Dapatkan Pairing Code
                    </button>

                    @if($pairingCode)
                        <div style="text-align:center;padding:16px;background:var(--color-surface);border:2px dashed var(--color-primary);border-radius:var(--radius-lg);">
                            <span style="font-size:11px;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Kode Penautan WhatsApp Anda:</span>
                            <strong style="font-size:28px;letter-spacing:0.2em;color:var(--color-primary);font-family:monospace;">{{ $pairingCode }}</strong>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Testing Section Card --}}
    <div class="card" style="padding:24px;">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">
            🧪 Tes Pengiriman Pesan WhatsApp
        </h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Nomor Tujuan WhatsApp</label>
                <input type="text" class="form-input" wire:model="testPhone" placeholder="6285641574131">
                <span style="font-size:11px;color:var(--color-neutral);margin-top:4px;display:block;">Format dapat berupa 08xx atau 628xx</span>
            </div>

            <div class="form-group" style="margin:0;grid-column:span 2;">
                <label class="form-label">Isi Pesan Uji Coba</label>
                <textarea class="form-textarea" wire:model="testMessage" rows="3"></textarea>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:16px;">
            <button wire:click="sendTestMessage" wire:loading.attr="disabled" class="btn btn-primary btn-md">
                <span wire:loading.remove>🚀 Kirim Pesan Uji Coba</span>
                <span wire:loading>Pengiriman sedang berlangsung...</span>
            </button>
        </div>
    </div>
</div>
