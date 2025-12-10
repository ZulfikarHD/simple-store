<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStoreSettingsRequest;
use App\Services\ImageService;
use App\Services\StoreSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * StoreSettingController untuk mengelola pengaturan toko di admin panel
 * dengan fitur view settings dan update settings
 */
class StoreSettingController extends Controller
{
    /**
     * Constructor dengan dependency injection untuk services
     */
    public function __construct(
        public StoreSettingService $settingService,
        public ImageService $imageService
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

    /**
     * Upload logo toko dengan image processing dan optimasi
     * Mengembalikan JSON response dengan path logo yang tersimpan
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'logo.required' => 'File logo wajib diupload.',
            'logo.image' => 'File harus berupa gambar.',
            'logo.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'logo.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            // Hapus logo lama jika ada
            $oldLogo = $this->settingService->getSetting('store_logo');
            if ($oldLogo) {
                $this->imageService->deleteImage($oldLogo);
            }

            // Upload dan optimasi logo baru ke direktori 'branding'
            $path = $this->imageService->uploadAndOptimize($request->file('logo'), 'branding');

            return response()->json([
                'success' => true,
                'path' => $path,
                'message' => 'Logo berhasil diupload.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload logo: '.$e->getMessage(),
            ], 500);
        }
    }
}
