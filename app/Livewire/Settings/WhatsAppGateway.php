<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WhatsAppGateway extends Component
{
    // Settings Form
    public bool $enabled = true;
    public string $url = '';
    public string $auth = '';
    public string $deviceId = '';
    public bool $verifySsl = true;
    public int $timeout = 30;

    // Health & Status State
    public bool $isHealthy = false;
    public bool $isConnected = false;
    public string $statusMessage = '';

    // Reconnect Tab State
    public string $reconnectTab = 'qr'; // 'qr' or 'pairing'
    public ?string $qrCode = null;
    public string $pairingPhone = '6285641574131';
    public ?string $pairingCode = null;

    // Testing State
    public string $testPhone = '6285641574131';
    public string $testMessage = 'Halo! Ini adalah pesan uji coba dari sistem Kanban KPI RafaTax via WhatsApp Gateway (go-whatsapp-web-multidevice).';
    public bool $isSendingTest = false;

    public function mount()
    {
        if (!Auth::user()->isDirector()) {
            abort(403, 'Hanya Level Director yang berhak mengelola WhatsApp Gateway.');
        }

        $this->enabled   = filter_var(WhatsAppService::getConfig('enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->url       = WhatsAppService::getConfig('url', 'https://wagateway.surakana.my.id');
        $this->auth      = WhatsAppService::getConfig('auth', 'admin:admin');
        $this->deviceId  = WhatsAppService::getConfig('device_id', '8a744703-b90a-4690-b911-b1b8f2523963');
        $this->verifySsl = filter_var(WhatsAppService::getConfig('verify_ssl', true), FILTER_VALIDATE_BOOLEAN);
        $this->timeout   = (int) WhatsAppService::getConfig('timeout', 30);

        $this->testConnection();
    }

    public function saveSettings()
    {
        Setting::set('whatsapp_enabled', $this->enabled ? 'true' : 'false', 'whatsapp');
        Setting::set('whatsapp_url', rtrim($this->url, '/'), 'whatsapp');
        Setting::set('whatsapp_auth', $this->auth, 'whatsapp');
        Setting::set('whatsapp_device_id', $this->deviceId, 'whatsapp');
        Setting::set('whatsapp_verify_ssl', $this->verifySsl ? 'true' : 'false', 'whatsapp');
        Setting::set('whatsapp_timeout', (string) $this->timeout, 'whatsapp');

        session()->flash('success', 'Pengaturan WhatsApp Gateway berhasil disimpan!');
        $this->testConnection();
    }

    public function testConnection()
    {
        $res = WhatsAppService::checkHealth();
        $this->isHealthy = $res['success'];
        $this->isConnected = $res['connected'] ?? false;
        $this->statusMessage = $res['message'];
    }

    public function loadQrCode()
    {
        $res = WhatsAppService::fetchQrCode();
        if ($res['success'] && isset($res['qr'])) {
            $this->qrCode = $res['qr'];
            session()->flash('info', 'QR Code berhasil dimuat. Silakan scan dengan WhatsApp di HP Anda.');
        } else {
            session()->flash('error', $res['message'] ?? 'Gagal mengambil QR Code.');
        }
    }

    public function requestPairingCode()
    {
        if (empty($this->pairingPhone)) {
            session()->flash('error', 'Masukkan nomor HP untuk meminta Pairing Code.');
            return;
        }

        $res = WhatsAppService::requestPairingCode($this->pairingPhone);
        if ($res['success'] && isset($res['code'])) {
            $this->pairingCode = $res['code'];
            session()->flash('success', "Pairing Code berhasil didapatkan: {$this->pairingCode}");
        } else {
            session()->flash('error', $res['message'] ?? 'Gagal meminta Pairing Code.');
        }
    }

    public function sendTestMessage()
    {
        if (empty($this->testPhone) || empty($this->testMessage)) {
            session()->flash('error', 'Nomor telepon dan pesan uji coba tidak boleh kosong.');
            return;
        }

        $this->isSendingTest = true;
        $res = WhatsAppService::sendTextMessage($this->testPhone, $this->testMessage);
        $this->isSendingTest = false;

        if ($res['success']) {
            session()->flash('success', "✓ Pesan uji coba berhasil dikirim ke {$this->testPhone}!");
        } else {
            session()->flash('error', "✕ Gagal mengirim pesan: " . $res['message']);
        }
    }

    public function render()
    {
        return view('livewire.settings.whatsapp-gateway')->layout('components.layouts.app');
    }
}
