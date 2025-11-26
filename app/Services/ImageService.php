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
     *
     * @param  UploadedFile  $file  File image yang diupload
     * @param  string  $directory  Direktori tujuan storage
     * @return string Path image yang tersimpan
     */
    public function uploadAndOptimize(UploadedFile $file, string $directory = 'products'): string
    {
        $filename = $this->generateFilename($file);

        // Dapatkan image dimensions
        $imageInfo = getimagesize($file->getRealPath());

        if ($imageInfo === false) {
            // Jika bukan image valid, simpan langsung tanpa processing
            return $file->storeAs($directory, $filename, 'public');
        }

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
     * Generate filename unik untuk image
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

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
