<?php

declare(strict_types=1);

namespace App\Services\Media;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class MediaStorage
{
    /**
     * Store an uploaded image and return a storable reference.
     *
     * - local: returns relative path under the public disk
     * - cloudinary: returns the secure URL
     *
     * @return array{path: string, sha256: string|null}
     */
    public function storeImage(UploadedFile $file, string $folder): array
    {
        $sha256 = $this->fileSha256($file);

        $driver = (string) config('media.driver', 'local');
        if ($driver === 'cloudinary') {
            $cloudinary = $this->cloudinary();

            $localPath = $file->getRealPath();
            if (! is_string($localPath) || $localPath === '' || ! is_readable($localPath)) {
                $localPath = $file->getPathname();
            }
            if (! is_readable($localPath)) {
                throw new RuntimeException('Could not read the uploaded file for Cloudinary.');
            }

            $upload = $cloudinary->uploadApi()->upload($localPath, [
                'folder' => trim($folder, '/'),
                'resource_type' => 'image',
            ]);

            $url = $upload['secure_url'] ?? null;
            if (! is_string($url) || $url === '') {
                throw new RuntimeException('Cloudinary upload failed (missing secure_url).');
            }

            return ['path' => $url, 'sha256' => $sha256];
        }

        $path = $file->store(trim($folder, '/'), 'public');

        return ['path' => $path, 'sha256' => $sha256];
    }

    /**
     * Best-effort delete for locally-stored media on the public disk.
     * (Cloudinary URLs are not deleted from here.)
     */
    public function deleteLocalPublicIfExists(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if ($this->looksLikeUrl($path)) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable) {
            // Ignore delete failures.
        }
    }

    private function fileSha256(UploadedFile $file): ?string
    {
        $p = $file->getRealPath();
        if (! is_string($p) || $p === '' || ! is_file($p)) {
            return null;
        }

        try {
            return hash_file('sha256', $p) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function looksLikeUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    private function cloudinary(): Cloudinary
    {
        $url = (string) config('media.cloudinary.url', '');
        if ($url !== '') {
            return new Cloudinary($url);
        }

        $cloudName = (string) config('media.cloudinary.cloud_name', '');
        $apiKey = (string) config('media.cloudinary.api_key', '');
        $apiSecret = (string) config('media.cloudinary.api_secret', '');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Cloudinary is not configured. Set CLOUDINARY_URL or CLOUDINARY_CLOUD_NAME/API_KEY/API_SECRET.');
        }

        return new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);
    }
}

