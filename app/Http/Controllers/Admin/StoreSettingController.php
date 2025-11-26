<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStoreSettingsRequest;
use App\Services\StoreSettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * StoreSettingController untuk mengelola pengaturan toko di admin panel
 * dengan fitur view settings dan update settings
 */
class StoreSettingController extends Controller
{
    /**
     * Constructor dengan dependency injection StoreSettingService
     */
    public function __construct(
        public StoreSettingService $settingService
    ) {}

    /**
     * Menampilkan halaman pengaturan toko dengan form settings, yaitu:
     * - Informasi umum toko (nama, alamat, telepon)
     * - Konfigurasi WhatsApp bisnis
     * - Jam operasional toko per hari
     * - Pengaturan delivery (area, biaya, minimum order)
     */
    public function index(): Response
    {
        $settings = $this->settingService->getAllSettings();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update pengaturan toko dengan validasi request
     * dan redirect kembali ke halaman settings dengan flash message
     */
    public function update(UpdateStoreSettingsRequest $request): RedirectResponse
    {
        $result = $this->settingService->updateSettings($request->validated());

        return redirect()
            ->back()
            ->with('success', $result['message']);
    }
}
