<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resolves the platform "latest schema version" for tenant migrations.
 *
 * Priority:
 * 1. GitHub latest release tag (when GITHUB_REPO is set and the API succeeds)
 * 2. APP_VERSION from config / .env
 *
 * Release tags should be semver-friendly (e.g. v1.0.0). The GitHub release
 * *title* can be human-readable ("Version 1 Meta"); the *tag* is what we parse.
 */
final class PlatformReleaseVersionService
{
    public function latestSchemaVersion(): string
    {
        $repo = trim((string) config('github.repo', ''));
        if ($repo === '') {
            return (string) config('app.version', '1.0.0');
        }

        $cacheKey = 'platform.github.latest_schema_version.'.md5($repo);

        return Cache::remember($cacheKey, (int) config('github.cache_seconds', 900), function () use ($repo) {
            $fromApi = $this->fetchLatestReleaseVersion($repo);
            if ($fromApi !== null) {
                return $fromApi;
            }

            return (string) config('app.version', '1.0.0');
        });
    }

    /**
     * Metadata for UI (best-effort; never throws).
     *
     * @return array{source: string, version: string, tag_name: ?string, release_name: ?string, html_url: ?string, published_at: ?string, error: ?string}
     */
    public function latestReleaseSummary(): array
    {
        $fallback = (string) config('app.version', '1.0.0');
        $repo = trim((string) config('github.repo', ''));
        if ($repo === '') {
            return [
                'source' => 'env',
                'version' => $fallback,
                'tag_name' => null,
                'release_name' => null,
                'html_url' => null,
                'published_at' => null,
                'error' => null,
            ];
        }

        try {
            $data = $this->fetchLatestReleasePayload($repo);
            if ($data === null) {
                return [
                    'source' => 'env_fallback',
                    'version' => $fallback,
                    'tag_name' => null,
                    'release_name' => null,
                    'html_url' => null,
                    'published_at' => null,
                    'error' => __('Could not reach GitHub (check repo name, token, or SSL/CA on Windows — see .env.example).'),
                ];
            }

            $tag = (string) ($data['tag_name'] ?? '');
            $normalized = $this->normalizeTagToSchemaVersion($tag);

            return [
                'source' => 'github',
                'version' => $normalized,
                'tag_name' => $tag !== '' ? $tag : null,
                'release_name' => isset($data['name']) ? (string) $data['name'] : null,
                'html_url' => isset($data['html_url']) ? (string) $data['html_url'] : null,
                'published_at' => isset($data['published_at']) ? (string) $data['published_at'] : null,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('platform.github.release_summary_failed', ['message' => $e->getMessage()]);

            return [
                'source' => 'env_fallback',
                'version' => $fallback,
                'tag_name' => null,
                'release_name' => null,
                'html_url' => null,
                'published_at' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Details for the latest GitHub release (notes + assets), best-effort.
     *
     * @return array{source: string, version: string, tag_name: ?string, release_name: ?string, html_url: ?string, published_at: ?string, body: ?string, assets: array<int, array{name: string, size: int, content_type: string, download_url: string}>, error: ?string}
     */
    public function latestReleaseDetails(): array
    {
        $fallback = (string) config('app.version', '1.0.0');
        $repo = trim((string) config('github.repo', ''));
        if ($repo === '') {
            return [
                'source' => 'env',
                'version' => $fallback,
                'tag_name' => null,
                'release_name' => null,
                'html_url' => null,
                'published_at' => null,
                'body' => null,
                'assets' => [],
                'error' => null,
            ];
        }

        $cacheKey = 'platform.github.latest_release_details.'.md5($repo);

        return Cache::remember($cacheKey, (int) config('github.cache_seconds', 900), function () use ($repo, $fallback) {
            try {
                $data = $this->fetchLatestReleasePayload($repo);
                if ($data === null) {
                    return [
                        'source' => 'env_fallback',
                        'version' => $fallback,
                        'tag_name' => null,
                        'release_name' => null,
                        'html_url' => null,
                        'published_at' => null,
                        'body' => null,
                        'assets' => [],
                        'error' => __('Could not reach GitHub (check repo name, token, or SSL/CA on Windows — see .env.example).'),
                    ];
                }

                $tag = (string) ($data['tag_name'] ?? '');
                $normalized = $this->normalizeTagToSchemaVersion($tag);
                $body = isset($data['body']) ? (string) $data['body'] : null;
                $assets = [];
                if (isset($data['assets']) && is_array($data['assets'])) {
                    foreach ($data['assets'] as $a) {
                        if (! is_array($a)) {
                            continue;
                        }
                        $name = (string) ($a['name'] ?? '');
                        $downloadUrl = (string) ($a['browser_download_url'] ?? '');
                        if ($name === '' || $downloadUrl === '') {
                            continue;
                        }
                        $assets[] = [
                            'name' => $name,
                            'size' => (int) ($a['size'] ?? 0),
                            'content_type' => (string) ($a['content_type'] ?? ''),
                            'download_url' => $downloadUrl,
                        ];
                    }
                }

                return [
                    'source' => 'github',
                    'version' => $normalized,
                    'tag_name' => $tag !== '' ? $tag : null,
                    'release_name' => isset($data['name']) ? (string) $data['name'] : null,
                    'html_url' => isset($data['html_url']) ? (string) $data['html_url'] : null,
                    'published_at' => isset($data['published_at']) ? (string) $data['published_at'] : null,
                    'body' => $body !== '' ? $body : null,
                    'assets' => $assets,
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                Log::warning('platform.github.release_details_failed', ['message' => $e->getMessage()]);

                return [
                    'source' => 'env_fallback',
                    'version' => $fallback,
                    'tag_name' => null,
                    'release_name' => null,
                    'html_url' => null,
                    'published_at' => null,
                    'body' => null,
                    'assets' => [],
                    'error' => $e->getMessage(),
                ];
            }
        });
    }

    private function fetchLatestReleaseVersion(string $repo): ?string
    {
        $data = $this->fetchLatestReleasePayload($repo);
        if ($data === null) {
            return null;
        }

        $tag = (string) ($data['tag_name'] ?? '');
        if ($tag === '') {
            return null;
        }

        return $this->normalizeTagToSchemaVersion($tag);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchLatestReleasePayload(string $repo): ?array
    {
        [$owner, $name] = $this->parseRepo($repo);
        if ($owner === null || $name === null) {
            Log::warning('platform.github.invalid_repo', ['repo' => $repo]);

            return null;
        }

        $url = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $owner, $name);

        $request = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ]);

        $token = trim((string) config('github.token', ''));
        if ($token !== '') {
            $request = $request->withToken($token);
        }

        try {
            $response = $request->get($url);
        } catch (\Throwable $e) {
            Log::warning('platform.github.connection_failed', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('platform.github.latest_release_http', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        /** @var array<string, mixed> $json */
        $json = $response->json();

        return $json;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function parseRepo(string $repo): array
    {
        $repo = str_replace(['https://github.com/', 'http://github.com/'], '', $repo);
        $repo = trim($repo, '/');
        $parts = explode('/', $repo, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return [null, null];
        }

        return [$parts[0], $parts[1]];
    }

    private function normalizeTagToSchemaVersion(string $tag): string
    {
        $tag = trim($tag);
        if (preg_match('/^v?(\d+\.\d+\.\d+)/i', $tag, $m)) {
            return $m[1];
        }
        if (preg_match('/(\d+\.\d+\.\d+)/', $tag, $m)) {
            return $m[1];
        }

        return $tag;
    }
}
