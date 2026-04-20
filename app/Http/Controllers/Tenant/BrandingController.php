<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\InputRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BrandingController extends Controller
{
    protected function getTenant(Request $request): Tenant
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        return $tenant;
    }

    public function edit(Request $request): View|RedirectResponse
    {
        if ($this->userIsStaff($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Only resort owners can manage branding.');
        }
        $tenant = $this->getTenant($request);
        return view('Tenant.branding.edit', compact('tenant'));
    }

    protected function userIsStaff(Request $request): bool
    {
        $user = $request->user('tenant');
        return $user && $user->role !== 'admin';
    }

    public function update(Request $request): RedirectResponse
    {
        if ($this->userIsStaff($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Only resort owners can manage branding.');
        }
        $tenant = $this->getTenant($request);

        $validated = $request->validate([
            'tenant_name' => InputRules::title(255, false),
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'landing_page_bg' => ['nullable', 'string', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'logo_cropped' => ['nullable', 'string'],
            'remove_logo' => ['nullable', 'boolean'],
            'hero_media' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm', 'max:1900'],
            'remove_hero_media' => ['nullable', 'boolean'],
            'hero_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:90'],
            'sections.hero_enabled' => ['nullable', 'boolean'],
            'sections.rooms_enabled' => ['nullable', 'boolean'],
            'sections.cta_enabled' => ['nullable', 'boolean'],
            'section_order.hero' => ['nullable', 'integer', 'min:1', 'max:3'],
            'section_order.rooms' => ['nullable', 'integer', 'min:1', 'max:3'],
            'section_order.cta' => ['nullable', 'integer', 'min:1', 'max:3'],
            'hero_title' => InputRules::title(120, false),
            'hero_subtitle' => ['nullable', 'string', 'max:220'],
            'hero_badge' => InputRules::title(80, false),
            'hero_note' => ['nullable', 'string', 'max:160'],
            'cta_primary_text' => InputRules::title(40, false),
            'cta_secondary_text' => InputRules::title(40, false),
            'cta_primary_url' => ['nullable', 'string', 'max:255'],
            'cta_secondary_url' => ['nullable', 'string', 'max:255'],
            'contact_phone' => InputRules::phone(25, false),
            'contact_email' => ['nullable', 'email:rfc,dns', 'max:120'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            'seo_meta_title' => ['nullable', 'string', 'max:70'],
            'seo_meta_description' => ['nullable', 'string', 'max:170'],
            'seo_og_image' => ['nullable', 'image', 'max:1900'],
            'remove_seo_og_image' => ['nullable', 'boolean'],
            'seo_favicon' => ['nullable', 'file', 'mimetypes:image/x-icon,image/vnd.microsoft.icon,image/png,image/svg+xml,image/webp', 'max:1024'],
            'remove_seo_favicon' => ['nullable', 'boolean'],
            'promo_enabled' => ['nullable', 'boolean'],
            'promo_text' => ['nullable', 'string', 'max:180'],
            'promo_image' => ['nullable', 'image', 'max:1900'],
            'promo_image_cropped' => ['nullable', 'string'],
            'remove_promo_image' => ['nullable', 'boolean'],
            'promo_start_date' => ['nullable', 'date'],
            'promo_end_date' => ['nullable', 'date', 'after_or_equal:promo_start_date'],
            'promo_dismissible' => ['nullable', 'boolean'],
            'promo_cta_text' => InputRules::title(40, false),
            'promo_cta_url' => ['nullable', 'string', 'max:255'],
            'promo_frequency_days' => ['nullable', 'integer', 'min:0', 'max:365'],
        ], [
            'hero_media.uploaded' => 'Hero media failed to upload. Your current PHP upload limit is 2MB. Please upload a smaller file.',
            'hero_media.max' => 'Hero media must be 1.9MB or smaller on this server.',
            'seo_og_image.uploaded' => 'OG image failed to upload. Your current PHP upload limit is 2MB.',
            'seo_og_image.max' => 'OG image must be 1.9MB or smaller on this server.',
            'seo_favicon.uploaded' => 'Favicon failed to upload. Please try a smaller file.',
        ]);

        if ($request->boolean('remove_logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $tenant->logo_path = null;
        } elseif (!empty($validated['logo_cropped'])) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $tenant->logo_path = $this->storeCroppedImage((string) $validated['logo_cropped'], 'tenant-logos', 'logo');
        } elseif ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $path = $request->file('logo')->store('tenant-logos', 'public');
            $tenant->logo_path = $path;
        }

        if (!empty($validated['tenant_name'])) {
            $tenant->tenant_name = $validated['tenant_name'];
        }

        $tenant->primary_color = $validated['primary_color'] ?: null;
        $tenant->secondary_color = $validated['secondary_color'] ?: null;

        // Store additional branding settings in metadata JSON
        $meta = $tenant->metadata ?? [];
        if (!is_array($meta)) {
            $meta = [];
        }

        if ($request->boolean('remove_hero_media')) {
            if (!empty($meta['hero_media_path'])) {
                Storage::disk('public')->delete($meta['hero_media_path']);
            }
            $meta['hero_media_path'] = null;
            $meta['hero_media_type'] = null;
        } elseif ($request->hasFile('hero_media')) {
            if (!empty($meta['hero_media_path'])) {
                Storage::disk('public')->delete($meta['hero_media_path']);
            }

            $heroMediaPath = $request->file('hero_media')->store('tenant-hero-media', 'public');
            $heroMime = (string) $request->file('hero_media')->getMimeType();
            $meta['hero_media_path'] = $heroMediaPath;
            $meta['hero_media_type'] = str_starts_with($heroMime, 'video/') ? 'video' : 'image';
        }

        // Legacy hero carousel files removed; landing hero slideshow uses available rooms.
        if (! empty($meta['hero_media_gallery_paths']) && is_array($meta['hero_media_gallery_paths'])) {
            foreach ($meta['hero_media_gallery_paths'] as $oldPath) {
                if (is_string($oldPath) && $oldPath !== '' && ! str_starts_with($oldPath, 'http://') && ! str_starts_with($oldPath, 'https://')) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }
        unset($meta['hero_media_gallery_paths']);

        if ($request->boolean('remove_seo_og_image')) {
            if (!empty($meta['seo_og_image_path'])) {
                Storage::disk('public')->delete($meta['seo_og_image_path']);
            }
            $meta['seo_og_image_path'] = null;
        } elseif ($request->hasFile('seo_og_image')) {
            if (!empty($meta['seo_og_image_path'])) {
                Storage::disk('public')->delete($meta['seo_og_image_path']);
            }
            $meta['seo_og_image_path'] = $request->file('seo_og_image')->store('tenant-seo', 'public');
        }

        if ($request->boolean('remove_seo_favicon')) {
            if (!empty($meta['seo_favicon_path'])) {
                Storage::disk('public')->delete($meta['seo_favicon_path']);
            }
            $meta['seo_favicon_path'] = null;
        } elseif ($request->hasFile('seo_favicon')) {
            if (!empty($meta['seo_favicon_path'])) {
                Storage::disk('public')->delete($meta['seo_favicon_path']);
            }
            $meta['seo_favicon_path'] = $request->file('seo_favicon')->store('tenant-seo', 'public');
        }

        if ($request->boolean('remove_promo_image')) {
            if (!empty($meta['promo_image_path'])) {
                Storage::disk('public')->delete($meta['promo_image_path']);
            }
            $meta['promo_image_path'] = null;
        } elseif (!empty($validated['promo_image_cropped'])) {
            if (!empty($meta['promo_image_path'])) {
                Storage::disk('public')->delete($meta['promo_image_path']);
            }
            $meta['promo_image_path'] = $this->storeCroppedImage((string) $validated['promo_image_cropped'], 'tenant-promo', 'promo_image');
        } elseif ($request->hasFile('promo_image')) {
            if (!empty($meta['promo_image_path'])) {
                Storage::disk('public')->delete($meta['promo_image_path']);
            }
            $meta['promo_image_path'] = $request->file('promo_image')->store('tenant-promo', 'public');
        }

        $landingBg = trim((string) ($validated['landing_page_bg'] ?? ''));
        $meta['landing_page_bg'] = $landingBg !== '' ? $landingBg : null;

        $meta['hero_overlay_opacity'] = isset($validated['hero_overlay_opacity']) && $validated['hero_overlay_opacity'] !== ''
            ? (int) $validated['hero_overlay_opacity']
            : 55;
        $meta['hero_title'] = $validated['hero_title'] ?: null;
        $meta['hero_subtitle'] = $validated['hero_subtitle'] ?: null;
        $meta['hero_badge'] = $validated['hero_badge'] ?: null;
        $meta['hero_note'] = $validated['hero_note'] ?: null;
        $meta['cta_primary_text'] = $validated['cta_primary_text'] ?: null;
        $meta['cta_secondary_text'] = $validated['cta_secondary_text'] ?: null;
        $meta['cta_primary_url'] = $this->normalizeCtaUrl($validated['cta_primary_url'] ?? null, 'cta_primary_url');
        $meta['cta_secondary_url'] = $this->normalizeCtaUrl($validated['cta_secondary_url'] ?? null, 'cta_secondary_url');
        $meta['contact_phone'] = trim((string) ($validated['contact_phone'] ?? '')) ?: null;
        $meta['contact_email'] = trim((string) ($validated['contact_email'] ?? '')) ?: null;
        $meta['contact_address'] = trim((string) ($validated['contact_address'] ?? '')) ?: null;
        $meta['social_facebook'] = $this->normalizeSocialUrl($validated['social_facebook'] ?? null, 'social_facebook');
        $meta['social_instagram'] = $this->normalizeSocialUrl($validated['social_instagram'] ?? null, 'social_instagram');
        $meta['social_tiktok'] = $this->normalizeSocialUrl($validated['social_tiktok'] ?? null, 'social_tiktok');
        $meta['seo_meta_title'] = trim((string) ($validated['seo_meta_title'] ?? '')) ?: null;
        $meta['seo_meta_description'] = trim((string) ($validated['seo_meta_description'] ?? '')) ?: null;
        $meta['promo_enabled'] = $request->boolean('promo_enabled', false);
        $meta['promo_text'] = trim((string) ($validated['promo_text'] ?? '')) ?: null;
        $meta['promo_start_date'] = trim((string) ($validated['promo_start_date'] ?? '')) ?: null;
        $meta['promo_end_date'] = trim((string) ($validated['promo_end_date'] ?? '')) ?: null;
        $meta['promo_dismissible'] = $request->boolean('promo_dismissible', true);
        $meta['promo_cta_text'] = trim((string) ($validated['promo_cta_text'] ?? '')) ?: null;
        $meta['promo_cta_url'] = $this->normalizeCtaUrl($validated['promo_cta_url'] ?? null, 'promo_cta_url');
        $meta['promo_frequency_days'] = isset($validated['promo_frequency_days']) && $validated['promo_frequency_days'] !== ''
            ? (int) $validated['promo_frequency_days']
            : 7;

        $meta['section_visibility'] = [
            'hero' => $request->boolean('sections.hero_enabled', true),
            'rooms' => $request->boolean('sections.rooms_enabled', true),
            'cta' => $request->boolean('sections.cta_enabled', true),
        ];

        $orderInput = [
            'hero' => (int) ($validated['section_order']['hero'] ?? 1),
            'rooms' => (int) ($validated['section_order']['rooms'] ?? 2),
            'cta' => (int) ($validated['section_order']['cta'] ?? 3),
        ];
        asort($orderInput);
        $meta['section_order'] = array_keys($orderInput);

        $tenant->metadata = $meta;

        $tenant->save();

        return redirect()
            ->route('tenant.branding.edit')
            ->with('success', 'Branding updated.');
    }

    protected function normalizeCtaUrl(?string $url, string $field): ?string
    {
        $value = trim((string) $url);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '#')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
            throw ValidationException::withMessages([$field => 'Enter a valid http/https URL.']);
        }

        if (str_starts_with($value, 'mailto:')) {
            $email = substr($value, 7);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
            throw ValidationException::withMessages([$field => 'Enter a valid mailto link (example: mailto:name@example.com).']);
        }

        if (str_starts_with($value, 'tel:')) {
            $phone = substr($value, 4);
            if ($phone !== '' && preg_match('/^[0-9+\-\s()]+$/', $phone)) {
                return $value;
            }
            throw ValidationException::withMessages([$field => 'Enter a valid tel link (example: tel:+639171234567).']);
        }

        throw ValidationException::withMessages([$field => 'Allowed links: /path, #anchor, http(s), mailto, or tel.']);
    }

    protected function normalizeSocialUrl(?string $url, string $field): ?string
    {
        $value = trim((string) $url);
        if ($value === '') {
            return null;
        }

        if (! (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
            throw ValidationException::withMessages([$field => 'Social links must start with http:// or https://']);
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([$field => 'Enter a valid URL.']);
        }

        return $value;
    }

    protected function storeCroppedImage(string $dataUrl, string $folder, string $errorField): string
    {
        if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/', $dataUrl, $matches)) {
            throw ValidationException::withMessages([$errorField => 'Invalid cropped image format.']);
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            throw ValidationException::withMessages([$errorField => 'Failed to decode cropped image.']);
        }

        if (strlen($binary) > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([$errorField => 'Cropped image is too large.']);
        }

        $path = $folder . '/' . uniqid('crop_', true) . '.' . $extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
