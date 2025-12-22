<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * ImageService untuk mengelola operasi image processing
 * termasuk upload, resize, compress, dan delete dengan optimasi performa
 *
 * @author Zulfikar Hidayatullah
 */
class ImageService
{
    /**
     * Konfigurasi default untuk image processing
     */
    private const MAX_WIDTH = 1200;

    private const MAX_HEIGHT = 1200;

    private const QUALITY = 80;

    private const THUMBNAIL_WIDTH = 400;

    private const THUMBNAIL_HEIGHT = 400;

    /**
     * Upload dan optimasi image produk
     * dengan resize ke max dimension dan compress quality
     * serta validasi extension dan MIME type untuk keamanan
     *
     * @param  UploadedFile  $file  File image yang diupload
     * @param  string  $directory  Direktori tujuan storage
     * @return string Path image yang tersimpan
     *
     * @throws \InvalidArgumentException Jika file extension atau MIME type tidak valid
     */
    public function uploadAndOptimize(UploadedFile $file, string $directory = 'products'): string
    {
        // Validasi extension dengan whitelist untuk mencegah upload file berbahaya
        $this->validateFileExtension($file);

        $filename = $this->generateFilename($file);

        // Dapatkan image dimensions dan validasi MIME type
        $imageInfo = getimagesize($file->getRealPath());

        if ($imageInfo === false) {
            throw new \InvalidArgumentException('File bukan image valid.');
        }

        // Validasi MIME type matches dengan extension untuk mencegah double extension attacks
        $this->validateMimeType($file, $imageInfo['mime']);

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Jika image sudah kecil, simpan langsung dengan compress
        if ($width <= self::MAX_WIDTH && $height <= self::MAX_HEIGHT) {
            $optimizedImage = $this->compressImage($file->getRealPath(), $mimeType);

            if ($optimizedImage !== null) {
                $path = $directory.'/'.$filename;
                Storage::disk('public')->put($path, $optimizedImage);

                return $path;
            }

            return $file->storeAs($directory, $filename, 'public');
        }

        // Resize dan compress image
        $resizedImage = $this->resizeAndCompress($file->getRealPath(), $mimeType, $width, $height);

        if ($resizedImage !== null) {
            $path = $directory.'/'.$filename;
            Storage::disk('public')->put($path, $resizedImage);

            return $path;
        }

        // Fallback: simpan original jika processing gagal
        return $file->storeAs($directory, $filename, 'public');
    }

    /**
     * Validasi file extension dengan whitelist
     * untuk mencegah upload file berbahaya seperti PHP scripts
     *
     * @throws \InvalidArgumentException Jika extension tidak diizinkan
     */
    private function validateFileExtension(UploadedFile $file): void
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException('File extension tidak diizinkan. Hanya JPG, PNG, WEBP, dan GIF yang diperbolehkan.');
        }
    }

    /**
     * Validasi MIME type sesuai dengan extension
     * untuk mencegah double extension attacks (e.g., malicious.php.jpg)
     *
     * @throws \InvalidArgumentException Jika MIME type tidak sesuai
     */
    private function validateMimeType(UploadedFile $file, string $actualMimeType): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $expectedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        if (! isset($expectedMimes[$extension])) {
            throw new \InvalidArgumentException('Extension tidak dikenali.');
        }

        if ($actualMimeType !== $expectedMimes[$extension]) {
            throw new \InvalidArgumentException('MIME type tidak sesuai dengan extension file. Kemungkinan file berbahaya.');
        }
    }

    /**
     * Generate filename unik untuk image dengan extension yang sudah divalidasi
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // Konversi ke webp jika browser support (optional)
        // Untuk simplicity, keep original extension
        return time().'_'.uniqid().'.'.$extension;
    }

    /**
     * Compress image dengan kualitas yang ditentukan
     * menggunakan GD library native PHP
     *
     * @return string|null Binary image yang sudah dicompress
     */
    private function compressImage(string $sourcePath, string $mimeType): ?string
    {
        $sourceImage = $this->createImageFromSource($sourcePath, $mimeType);

        if ($sourceImage === null) {
            return null;
        }

        ob_start();
        $this->outputImage($sourceImage, $mimeType);
        $result = ob_get_clean();

        imagedestroy($sourceImage);

        return $result !== false ? $result : null;
    }

    /**
     * Resize dan compress image ke max dimensions
     *
     * @return string|null Binary image yang sudah diresize dan compress
     */
    private function resizeAndCompress(string $sourcePath, string $mimeType, int $originalWidth, int $originalHeight): ?string
    {
        $sourceImage = $this->createImageFromSource($sourcePath, $mimeType);

        if ($sourceImage === null) {
            return null;
        }

        // Hitung dimensi baru dengan mempertahankan aspect ratio
        $ratio = min(self::MAX_WIDTH / $originalWidth, self::MAX_HEIGHT / $originalHeight);
        $newWidth = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);

        // Buat canvas baru
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($resizedImage === false) {
            imagedestroy($sourceImage);

            return null;
        }

        // Preserve transparency untuk PNG
        if ($mimeType === 'image/png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize dengan resampling untuk kualitas lebih baik
        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        ob_start();
        $this->outputImage($resizedImage, $mimeType);
        $result = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $result !== false ? $result : null;
    }

    /**
     * Create GD image resource dari source file
     */
    private function createImageFromSource(string $sourcePath, string $mimeType): ?\GdImage
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : null,
            default => null,
        };
    }

    /**
     * Output image dengan quality setting
     */
    private function outputImage(\GdImage $image, string $mimeType): void
    {
        match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagejpeg($image, null, self::QUALITY),
            'image/png' => imagepng($image, null, (int) ((100 - self::QUALITY) / 10)),
            'image/gif' => imagegif($image),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, null, self::QUALITY) : imagejpeg($image, null, self::QUALITY),
            default => imagejpeg($image, null, self::QUALITY),
        };
    }

    /**
     * Menghapus image dari storage
     */
    public function deleteImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
