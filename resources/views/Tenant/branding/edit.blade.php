<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Site Branding') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Public landing page look, hero, CTAs, and SEO.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6" x-data="{
        primary: '{{ old('primary_color', $tenant->primary_color ?? '#0f6b7e') }}',
        secondary: '{{ old('secondary_color', $tenant->secondary_color ?? '#0b3d4f') }}',
        heroOverlay: '{{ old('hero_overlay_opacity', (int)($tenant->metadata['hero_overlay_opacity'] ?? 55)) }}',
        previewMode: 'desktop',
        section: 'identity',
        logoPreview: '{{ $tenant->logo_path ? Storage::url($tenant->logo_path) : '' }}',
        logoImageRaw: '',
        logoCropOpen: false,
        logoCropReady: false,
        logoCropScale: 1,
        logoCropX: 0,
        logoCropY: 0,
        openLogoCropper(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.logoImageRaw = e.target?.result || '';
                this.logoCropScale = 1;
                this.logoCropX = 0;
                this.logoCropY = 0;
                this.logoCropReady = false;
                this.logoCropOpen = true;
                setTimeout(() => this.drawLogoCropCanvas(), 0);
            };
            reader.readAsDataURL(file);
        },
        drawLogoCropCanvas() {
            const canvas = this.$refs.logoCropCanvas;
            if (!canvas || !this.logoImageRaw) return;
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = () => {
                const size = canvas.width;
                ctx.clearRect(0, 0, size, size);
                const minScale = Math.max(size / img.width, size / img.height);
                const scale = minScale * this.logoCropScale;
                const drawW = img.width * scale;
                const drawH = img.height * scale;
                const maxX = Math.max(0, (drawW - size) / 2);
                const maxY = Math.max(0, (drawH - size) / 2);
                const dx = (size - drawW) / 2 + Math.max(-maxX, Math.min(maxX, this.logoCropX));
                const dy = (size - drawH) / 2 + Math.max(-maxY, Math.min(maxY, this.logoCropY));
                ctx.drawImage(img, dx, dy, drawW, drawH);
                this.logoCropReady = true;
            };
            img.src = this.logoImageRaw;
        },
        applyLogoCrop() {
            const hidden = this.$refs.logoCroppedInput;
            const hiddenById = document.getElementById('logo_cropped_input');
            const target = hidden || hiddenById;
            if (!target) return;
            let data = this.logoImageRaw || '';
            if (this.logoCropReady && this.$refs.logoCropCanvas) {
                data = this.$refs.logoCropCanvas.toDataURL('image/png');
            }
            if (!data) return;
            this.logoPreview = data;
            target.value = data;
            this.logoCropOpen = false;
        },
        promoImagePreview: '{{ !empty($tenant->metadata['promo_image_path']) ? Storage::url($tenant->metadata['promo_image_path']) : '' }}',
        promoImageRaw: '',
        promoCropOpen: false,
        promoCropReady: false,
        cropScale: 1,
        cropX: 0,
        cropY: 0,
        openPromoCropper(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.promoImageRaw = e.target?.result || '';
                this.cropScale = 1;
                this.cropX = 0;
                this.cropY = 0;
                this.promoCropReady = false;
                this.promoCropOpen = true;
                setTimeout(() => this.drawPromoCropCanvas(), 0);
            };
            reader.readAsDataURL(file);
        },
        drawPromoCropCanvas() {
            const canvas = this.$refs.promoCropCanvas;
            if (!canvas || !this.promoImageRaw) return;
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = () => {
                const size = canvas.width;
                ctx.clearRect(0, 0, size, size);
                const minScale = Math.max(size / img.width, size / img.height);
                const scale = minScale * this.cropScale;
                const drawW = img.width * scale;
                const drawH = img.height * scale;
                const maxX = Math.max(0, (drawW - size) / 2);
                const maxY = Math.max(0, (drawH - size) / 2);
                const dx = (size - drawW) / 2 + Math.max(-maxX, Math.min(maxX, this.cropX));
                const dy = (size - drawH) / 2 + Math.max(-maxY, Math.min(maxY, this.cropY));
                ctx.drawImage(img, dx, dy, drawW, drawH);
                this.promoCropReady = true;
            };
            img.src = this.promoImageRaw;
        },
        applyPromoCrop() {
            const hidden = this.$refs.promoImageCroppedInput;
            const hiddenById = document.getElementById('promo_image_cropped_input');
            const target = hidden || hiddenById;
            if (!target) return;
            let data = this.promoImageRaw || '';
            if (this.promoCropReady && this.$refs.promoCropCanvas) {
                data = this.$refs.promoCropCanvas.toDataURL('image/jpeg', 0.92);
            }
            if (!data) return;
            this.promoImagePreview = data;
            target.value = data;
            this.promoCropOpen = false;
        }
    }">
        <div class="rounded-xl border border-gray-200/80 bg-white p-6 shadow-sm">
            <p class="mb-6 text-gray-600">Customize how your resort appears on the public landing page.</p>

            <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)]">
                <aside class="hidden xl:block">
                    <div class="sticky top-24 rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <p class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Sections</p>
                        <nav class="space-y-1 text-sm">
                            <button type="button" @click="section = 'identity'" :class="section === 'identity' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">Identity</button>
                            <button type="button" @click="section = 'colors'" :class="section === 'colors' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">Colors & Preview</button>
                            <button type="button" @click="section = 'sections'" :class="section === 'sections' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">Landing Sections</button>
                            <button type="button" @click="section = 'hero'" :class="section === 'hero' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">Hero</button>
                            <button type="button" @click="section = 'cta'" :class="section === 'cta' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">CTAs</button>
                            <button type="button" @click="section = 'contact'" :class="section === 'contact' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">Contact & Social</button>
                            <button type="button" @click="section = 'seo'" :class="section === 'seo' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">SEO</button>
                            <button type="button" @click="section = 'promo'" :class="section === 'promo' ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'text-gray-700 hover:bg-white'" class="block w-full rounded-lg px-2 py-1.5 text-left">Promo</button>
                        </nav>
                    </div>
                </aside>

            <x-form-with-busy method="POST" action="{{ tenant_url('branding') }}" enctype="multipart/form-data" class="space-y-6" :overlay="true" busy-message="{{ __('Saving your branding…') }}">
                @csrf
                @method('PATCH')

                <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('Section preview') }}</h2>
                        <span class="text-[11px] uppercase tracking-wide text-gray-500" x-text="section"></span>
                    </div>

                    <div x-show="section === 'identity'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="flex items-center gap-3">
                            <img x-show="logoPreview" x-cloak :src="logoPreview" alt="Logo preview" class="h-10 w-10 rounded-lg object-contain bg-white border border-gray-200">
                            <div x-show="!logoPreview" x-cloak>
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg text-xs font-semibold text-slate-900"
                                     :style="`background: linear-gradient(135deg, ${primary}33, ${secondary}66);`">
                                    {{ strtoupper(substr($tenant->tenant_name ?? 'R', 0, 2)) }}
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ old('tenant_name', $tenant->tenant_name) }}</p>
                                <p class="text-xs text-gray-500">{{ __('Brand mark shown in headers/navigation.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="section === 'colors'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white"
                                  :style="`background: linear-gradient(135deg, ${primary}, ${secondary});`">{{ __('Primary action') }}</span>
                            <span class="inline-flex items-center rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-700">{{ __('Secondary action') }}</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                  :style="`background-color:${primary}22;color:${primary};`">{{ __('Accent badge') }}</span>
                        </div>
                    </div>

                    <div x-show="section === 'sections'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-xs text-gray-500">{{ __('Visible landing blocks in order:') }}</p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-gray-700">{{ __('Hero') }}</span>
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-gray-700">{{ __('Rooms') }}</span>
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-gray-700">{{ __('CTA') }}</span>
                        </div>
                    </div>

                    <div x-show="section === 'hero'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-[11px] uppercase tracking-wide text-gray-500">{{ old('hero_badge', $tenant->metadata['hero_badge'] ?? 'Rooms & cottages') }}</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">{{ old('hero_title', $tenant->metadata['hero_title'] ?? 'Book your perfect stay') }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ old('hero_subtitle', $tenant->metadata['hero_subtitle'] ?? 'Explore rooms and reserve in a few clicks.') }}</p>
                    </div>

                    <div x-show="section === 'cta'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white"
                                  :style="`background: linear-gradient(135deg, ${primary}, ${secondary});`">{{ old('cta_primary_text', $tenant->metadata['cta_primary_text'] ?? 'View rooms') }}</span>
                            <span class="inline-flex items-center rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-700">{{ old('cta_secondary_text', $tenant->metadata['cta_secondary_text'] ?? 'Create account') }}</span>
                        </div>
                    </div>

                    <div x-show="section === 'contact'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700">
                        <p>{{ __('Phone: :v', ['v' => old('contact_phone', $tenant->metadata['contact_phone'] ?? '—')]) }}</p>
                        <p>{{ __('Email: :v', ['v' => old('contact_email', $tenant->metadata['contact_email'] ?? '—')]) }}</p>
                        <p>{{ __('Address: :v', ['v' => old('contact_address', $tenant->metadata['contact_address'] ?? '—')]) }}</p>
                    </div>

                    <div x-show="section === 'seo'" x-cloak class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-sm font-semibold text-indigo-700">{{ old('seo_meta_title', $tenant->metadata['seo_meta_title'] ?? ($tenant->tenant_name . ' | Book your stay')) }}</p>
                        <p class="mt-1 text-xs text-gray-600">{{ old('seo_meta_description', $tenant->metadata['seo_meta_description'] ?? 'Search preview description for your landing page.') }}</p>
                    </div>

                    <div x-show="section === 'promo'" x-cloak class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <p class="text-xs font-medium text-amber-900">{{ old('promo_text', $tenant->metadata['promo_text'] ?? __('Your promo banner text appears here.')) }}</p>
                        <p class="mt-1 text-[11px] text-amber-800">{{ old('promo_cta_text', $tenant->metadata['promo_cta_text'] ?? __('Optional CTA button')) }}</p>
                        @if(!empty($tenant->metadata['promo_image_path']))
                            <img src="{{ Storage::url($tenant->metadata['promo_image_path']) }}" alt="Promo preview" class="mt-2 h-20 w-20 rounded-xl object-cover border border-amber-200">
                        @endif
                    </div>
                </div>

                <div id="branding-identity" x-show="section === 'identity'" x-cloak class="grid gap-6 scroll-mt-24">
                    <div>
                        <label for="tenant_name" class="block text-sm font-medium text-gray-700">Resort / site name</label>
                        <input id="tenant_name" name="tenant_name" type="text"
                               value="{{ old('tenant_name', $tenant->tenant_name) }}"
                               {{ \App\Support\InputHtmlAttributes::title(255) }}
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                               placeholder="e.g. Sunrise Beach Resort">
                        <p class="mt-1 text-xs text-gray-500">Shown on your landing page, browser title, and navigation.</p>
                        @error('tenant_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700">Logo (optional)</label>
                    <div class="mt-2 mb-2 flex items-center gap-3">
                        <img x-show="logoPreview" x-cloak :src="logoPreview" alt="Current logo" class="h-16 w-16 rounded-xl object-contain border border-gray-200 bg-white">
                        <span x-show="!logoPreview" x-cloak class="inline-flex h-16 w-16 items-center justify-center rounded-xl border border-dashed border-gray-300 text-[11px] text-gray-500">No logo</span>
                        <button type="button"
                                @click="logoPreview=''; if ($refs.logoInput) { $refs.logoInput.value=''; } if ($refs.logoCroppedInput) { $refs.logoCroppedInput.value=''; }"
                                class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50">
                            Clear selected
                        </button>
                    </div>
                    <input id="logo_cropped_input" type="hidden" name="logo_cropped" x-ref="logoCroppedInput">
                    <input id="logo" name="logo" type="file" accept="image/*" x-ref="logoInput"
                           @change="
                                const file = $event.target.files && $event.target.files[0] ? $event.target.files[0] : null;
                                if (!file) { return; }
                                if ($refs.logoCroppedInput) { $refs.logoCroppedInput.value = ''; }
                                openLogoCropper(file);
                           "
                           class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700">
                    @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                </div>

                <div id="branding-colors" x-show="section === 'colors'" x-cloak class="grid gap-6 scroll-mt-24">
                    <div>
                        <label for="primary_color" class="block text-sm font-medium text-gray-700">Primary color</label>
                        <div class="mt-1 flex items-center gap-3">
                            <input id="primary_color" name="primary_color" type="text" x-model="primary"
                                   class="block w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                   placeholder="#0f6b7e">
                            <input type="color" x-model="primary"
                                   class="h-9 w-9 cursor-pointer rounded border border-gray-300 bg-white p-0">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Used for buttons and accents on your tenant landing page.</p>
                        @error('primary_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="secondary_color" class="block text-sm font-medium text-gray-700">Secondary color</label>
                        <div class="mt-1 flex items-center gap-3">
                            <input id="secondary_color" name="secondary_color" type="text" x-model="secondary"
                                   class="block w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                   placeholder="#0b3d4f">
                            <input type="color" x-model="secondary"
                                   class="h-9 w-9 cursor-pointer rounded border border-gray-300 bg-white p-0">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Used for gradients and hover states.</p>
                        @error('secondary_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="landing_page_bg" class="block text-sm font-medium text-gray-700">{{ __('Page background') }}</label>
                        <div class="mt-1 flex items-center gap-3">
                            <input id="landing_page_bg" name="landing_page_bg" type="text"
                                   value="{{ old('landing_page_bg', $tenant->metadata['landing_page_bg'] ?? '') }}"
                                   class="block w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                   placeholder="#f4f2ee">
                            <input type="color"
                                   value="{{ old('landing_page_bg', $tenant->metadata['landing_page_bg'] ?? '#f4f2ee') }}"
                                   class="h-9 w-9 cursor-pointer rounded border border-gray-300 bg-white p-0"
                                   title="{{ __('Pick background') }}"
                                   oninput="document.getElementById('landing_page_bg').value = this.value">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Base color behind your public landing (hex). Leave empty for the default warm neutral. Radial accents still use your primary and secondary colors.') }}</p>
                        @error('landing_page_bg') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div x-show="section === 'colors'" x-cloak class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 scroll-mt-24">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Preview</p>
                        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                            <button type="button" @click="previewMode = 'desktop'"
                                    :class="previewMode === 'desktop' ? 'bg-teal-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                                    class="rounded-md px-3 py-1 text-xs font-medium transition">
                                Desktop
                            </button>
                            <button type="button" @click="previewMode = 'mobile'"
                                    :class="previewMode === 'mobile' ? 'bg-teal-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                                    class="rounded-md px-3 py-1 text-xs font-medium transition">
                                Mobile
                            </button>
                        </div>
                    </div>

                    <div x-show="previewMode === 'desktop'" x-cloak class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <img x-show="logoPreview" x-cloak :src="logoPreview" alt="Logo preview" class="h-10 w-auto rounded shadow-sm object-contain bg-white">
                            <div x-show="!logoPreview" x-cloak>
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-semibold text-slate-900 shadow"
                                     :style="`background: linear-gradient(135deg, ${primary}33, ${secondary}66);`">
                                    {{ strtoupper(substr($tenant->tenant_name ?? 'R', 0, 2)) }}
                                </div>
                            </div>
                            <div class="text-sm">
                                <div class="font-semibold text-gray-900">{{ $tenant->tenant_name }}</div>
                                <div class="text-xs text-gray-500">Buttons & accents use your colors.</div>
                            </div>
                        </div>
                        <div class="ml-auto flex gap-2">
                            <button type="button"
                                    class="inline-flex items-center rounded-full px-4 py-1.5 text-xs font-semibold text-white shadow"
                                    :style="`background: linear-gradient(135deg, ${primary}, ${secondary});`">
                                Primary button
                            </button>
                            <button type="button"
                                    class="inline-flex items-center rounded-full border px-4 py-1.5 text-xs font-semibold text-gray-700 border-gray-300 bg-white">
                                Secondary
                            </button>
                        </div>
                    </div>

                    <div x-show="previewMode === 'mobile'" x-cloak class="mx-auto w-[260px] rounded-[28px] border-4 border-gray-800 bg-white p-3 shadow-lg">
                        <div class="mx-auto mb-2 h-1.5 w-14 rounded-full bg-gray-300"></div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3">
                            <div class="flex items-center gap-2">
                                <img x-show="logoPreview" x-cloak :src="logoPreview" alt="Logo preview" class="h-8 w-8 rounded-xl object-contain bg-white">
                                <div x-show="!logoPreview" x-cloak>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-xl text-[10px] font-semibold text-slate-900 shadow"
                                         :style="`background: linear-gradient(135deg, ${primary}33, ${secondary}66);`">
                                        {{ strtoupper(substr($tenant->tenant_name ?? 'R', 0, 2)) }}
                                    </div>
                                </div>
                                <div class="text-xs font-semibold text-gray-900 truncate">{{ $tenant->tenant_name }}</div>
                            </div>
                            <div class="mt-3 space-y-2">
                                <div class="h-2 w-4/5 rounded bg-gray-200"></div>
                                <div class="h-2 w-3/5 rounded bg-gray-200"></div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="button" class="flex-1 rounded-full px-2 py-1 text-[10px] font-semibold text-white"
                                        :style="`background: linear-gradient(135deg, ${primary}, ${secondary});`">
                                    Primary
                                </button>
                                <button type="button" class="flex-1 rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-semibold text-gray-700">
                                    Secondary
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 mt-6 space-y-6">
                    <div id="branding-sections" x-show="section === 'sections'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">Landing sections</h2>
                        <p class="text-xs text-gray-500 mb-3">Choose which sections appear and arrange the display order on your public landing page.</p>

                        @php
                            $sectionVisibility = $tenant->metadata['section_visibility'] ?? ['hero' => true, 'rooms' => true, 'cta' => true];
                            $sectionOrder = $tenant->metadata['section_order'] ?? ['hero', 'rooms', 'cta'];
                            $sectionPositionMap = array_flip($sectionOrder);
                            $heroOrder = (int) (($sectionPositionMap['hero'] ?? 0) + 1);
                            $roomsOrder = (int) (($sectionPositionMap['rooms'] ?? 1) + 1);
                            $ctaOrder = (int) (($sectionPositionMap['cta'] ?? 2) + 1);
                        @endphp

                        <div class="rounded-xl border border-gray-200 p-4 space-y-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="sections[hero_enabled]" value="1"
                                           @checked(old('sections.hero_enabled', (bool)($sectionVisibility['hero'] ?? true)))
                                           class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                    <span>Show Hero</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="sections[rooms_enabled]" value="1"
                                           @checked(old('sections.rooms_enabled', (bool)($sectionVisibility['rooms'] ?? true)))
                                           class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                    <span>Show Rooms</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="sections[cta_enabled]" value="1"
                                           @checked(old('sections.cta_enabled', (bool)($sectionVisibility['cta'] ?? true)))
                                           class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                    <span>Show CTA</span>
                                </label>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label for="section_order_hero" class="block text-sm font-medium text-gray-700">Hero order</label>
                                    <select id="section_order_hero" name="section_order[hero]"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @for($pos = 1; $pos <= 3; $pos++)
                                            <option value="{{ $pos }}" @selected((int)old('section_order.hero', $heroOrder) === $pos)>{{ $pos }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="section_order_rooms" class="block text-sm font-medium text-gray-700">Rooms order</label>
                                    <select id="section_order_rooms" name="section_order[rooms]"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @for($pos = 1; $pos <= 3; $pos++)
                                            <option value="{{ $pos }}" @selected((int)old('section_order.rooms', $roomsOrder) === $pos)>{{ $pos }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="section_order_cta" class="block text-sm font-medium text-gray-700">CTA order</label>
                                    <select id="section_order_cta" name="section_order[cta]"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @for($pos = 1; $pos <= 3; $pos++)
                                            <option value="{{ $pos }}" @selected((int)old('section_order.cta', $ctaOrder) === $pos)>{{ $pos }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">If duplicate positions are selected, the system applies a stable fallback order.</p>
                        </div>
                    </div>

                    <div id="branding-hero" x-show="section === 'hero'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">Hero media</h2>
                        <p class="text-xs text-gray-500 mb-3">The public landing hero is a <strong>slideshow of available rooms</strong> (each room’s photo, name, and short text). Optional media below is used only when there are no room slides to show (for example, no photos yet or every room is fully booked).</p>
                        <div class="grid gap-4">
                            <div>
                                <label for="hero_media" class="block text-sm font-medium text-gray-700">Hero image/video (optional)</label>
                                @if(!empty($tenant->metadata['hero_media_path']))
                                    <p class="mt-1 text-xs text-gray-500">Current file: <span class="font-medium text-gray-700">{{ basename($tenant->metadata['hero_media_path']) }}</span></p>
                                    <label class="mt-1 inline-flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox" name="remove_hero_media" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        Remove current hero media
                                    </label>
                                @endif
                                <input id="hero_media" name="hero_media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                                       class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700">
                                <p class="mt-1 text-xs text-gray-500">Accepted: JPG, PNG, WebP, MP4, WebM (max 1.9MB on current local PHP setup). Shown only when the room slideshow is not available.</p>
                                @error('hero_media') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="hero_overlay_opacity" class="block text-sm font-medium text-gray-700">Hero overlay opacity</label>
                                <input id="hero_overlay_opacity" name="hero_overlay_opacity" type="range" min="0" max="90"
                                       x-model="heroOverlay"
                                       class="mt-2 w-full accent-teal-600">
                                <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                                    <span>Lighter</span>
                                    <span><span x-text="heroOverlay"></span>%</span>
                                    <span>Darker</span>
                                </div>
                                @error('hero_overlay_opacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div x-show="section === 'hero'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">Hero content</h2>
                        <p class="text-xs text-gray-500 mb-3">Control the main text your guests see first on your landing page.</p>
                        <div class="space-y-4">
                            <div>
                                <label for="hero_title" class="block text-sm font-medium text-gray-700">Headline</label>
                                <input id="hero_title" name="hero_title" type="text" value="{{ old('hero_title', $tenant->metadata['hero_title'] ?? '') }}"
                                       {{ \App\Support\InputHtmlAttributes::title(120) }}
                                       class="mt-1 block w-full max-w-lg rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Book your perfect stay">
                                @error('hero_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="hero_subtitle" class="block text-sm font-medium text-gray-700">Subheadline</label>
                                <textarea id="hero_subtitle" name="hero_subtitle" rows="2"
                                          {{ \App\Support\InputHtmlAttributes::textarea(220) }}
                                          class="mt-1 block w-full max-w-xl rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                          placeholder="Short description inviting guests to book.">{{ old('hero_subtitle', $tenant->metadata['hero_subtitle'] ?? '') }}</textarea>
                                @error('hero_subtitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid gap-4">
                                <div>
                                    <label for="hero_badge" class="block text-sm font-medium text-gray-700">Small badge text</label>
                                    <input id="hero_badge" name="hero_badge" type="text" value="{{ old('hero_badge', $tenant->metadata['hero_badge'] ?? '') }}"
                                           {{ \App\Support\InputHtmlAttributes::title(80) }}
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                           placeholder="Rooms & cottages">
                                    @error('hero_badge') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="hero_note" class="block text-sm font-medium text-gray-700">Small note under buttons</label>
                                    <input id="hero_note" name="hero_note" type="text" value="{{ old('hero_note', $tenant->metadata['hero_note'] ?? '') }}"
                                           {{ \App\Support\InputHtmlAttributes::title(160) }}
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                           placeholder="No account needed to browse · Create one to manage your bookings">
                                    @error('hero_note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="branding-cta" x-show="section === 'cta'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">Button texts</h2>
                        <p class="text-xs text-gray-500 mb-3">Adjust labels and links for your main call‑to‑action buttons.</p>
                        <div class="grid gap-4">
                            <div>
                                <label for="cta_primary_text" class="block text-sm font-medium text-gray-700">Primary button</label>
                                <input id="cta_primary_text" name="cta_primary_text" type="text" value="{{ old('cta_primary_text', $tenant->metadata['cta_primary_text'] ?? '') }}"
                                       {{ \App\Support\InputHtmlAttributes::title(40) }}
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="View rooms">
                                @error('cta_primary_text') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <label for="cta_primary_url" class="mt-3 block text-sm font-medium text-gray-700">Primary button link</label>
                                <input id="cta_primary_url" name="cta_primary_url" type="text" value="{{ old('cta_primary_url', $tenant->metadata['cta_primary_url'] ?? '') }}"
                                       maxlength="255"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="#rooms or /book">
                                @error('cta_primary_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="cta_secondary_text" class="block text-sm font-medium text-gray-700">Secondary button</label>
                                <input id="cta_secondary_text" name="cta_secondary_text" type="text" value="{{ old('cta_secondary_text', $tenant->metadata['cta_secondary_text'] ?? '') }}"
                                       {{ \App\Support\InputHtmlAttributes::title(40) }}
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Create account">
                                @error('cta_secondary_text') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <label for="cta_secondary_url" class="mt-3 block text-sm font-medium text-gray-700">Secondary button link</label>
                                <input id="cta_secondary_url" name="cta_secondary_url" type="text" value="{{ old('cta_secondary_url', $tenant->metadata['cta_secondary_url'] ?? '') }}"
                                       maxlength="255"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="/user/register">
                                @error('cta_secondary_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Allowed links: `/path`, `#anchor`, `https://...`, `mailto:...`, `tel:...`</p>
                    </div>

                    <div id="branding-contact" x-show="section === 'contact'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">Contact & social links</h2>
                        <p class="text-xs text-gray-500 mb-3">Show guest-facing contact details on your landing page footer.</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $tenant->metadata['contact_phone'] ?? '') }}"
                                       {{ \App\Support\InputHtmlAttributes::phone(25) }}
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="+63 917 123 4567">
                                @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $tenant->metadata['contact_email'] ?? '') }}"
                                       {{ \App\Support\InputHtmlAttributes::email()->merge(['maxlength' => '120']) }}
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="reservations@example.com">
                                @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="contact_address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input id="contact_address" name="contact_address" type="text" value="{{ old('contact_address', $tenant->metadata['contact_address'] ?? '') }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Barangay, City, Province">
                                @error('contact_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="social_facebook" class="block text-sm font-medium text-gray-700">Facebook URL</label>
                                <input id="social_facebook" name="social_facebook" type="url" value="{{ old('social_facebook', $tenant->metadata['social_facebook'] ?? '') }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="https://facebook.com/yourpage">
                                @error('social_facebook') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="social_instagram" class="block text-sm font-medium text-gray-700">Instagram URL</label>
                                <input id="social_instagram" name="social_instagram" type="url" value="{{ old('social_instagram', $tenant->metadata['social_instagram'] ?? '') }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="https://instagram.com/yourresort">
                                @error('social_instagram') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="social_tiktok" class="block text-sm font-medium text-gray-700">TikTok URL</label>
                                <input id="social_tiktok" name="social_tiktok" type="url" value="{{ old('social_tiktok', $tenant->metadata['social_tiktok'] ?? '') }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="https://tiktok.com/@yourresort">
                                @error('social_tiktok') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div id="branding-seo" x-show="section === 'seo'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">SEO</h2>
                        <p class="text-xs text-gray-500 mb-3">Set search preview text and social sharing images for your landing page.</p>
                        <div class="grid gap-4">
                            <div>
                                <label for="seo_meta_title" class="block text-sm font-medium text-gray-700">Meta title</label>
                                <input id="seo_meta_title" name="seo_meta_title" type="text" value="{{ old('seo_meta_title', $tenant->metadata['seo_meta_title'] ?? '') }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Sunrise Beach Resort | Book Your Stay">
                                @error('seo_meta_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="seo_meta_description" class="block text-sm font-medium text-gray-700">Meta description</label>
                                <textarea id="seo_meta_description" name="seo_meta_description" rows="2"
                                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                          placeholder="Discover rooms, check availability, and book your stay online.">{{ old('seo_meta_description', $tenant->metadata['seo_meta_description'] ?? '') }}</textarea>
                                @error('seo_meta_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="seo_og_image" class="block text-sm font-medium text-gray-700">Open Graph image</label>
                                @if(!empty($tenant->metadata['seo_og_image_path']))
                                    <p class="mt-1 text-xs text-gray-500">Current file: <span class="font-medium text-gray-700">{{ basename($tenant->metadata['seo_og_image_path']) }}</span></p>
                                    <label class="mt-1 inline-flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox" name="remove_seo_og_image" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        Remove current OG image
                                    </label>
                                @endif
                                <input id="seo_og_image" name="seo_og_image" type="file" accept="image/*"
                                       class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700">
                                @error('seo_og_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="seo_favicon" class="block text-sm font-medium text-gray-700">Favicon (.ico/.png/.svg/.webp)</label>
                                @if(!empty($tenant->metadata['seo_favicon_path']))
                                    <p class="mt-1 text-xs text-gray-500">Current file: <span class="font-medium text-gray-700">{{ basename($tenant->metadata['seo_favicon_path']) }}</span></p>
                                    <label class="mt-1 inline-flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox" name="remove_seo_favicon" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        Remove current favicon
                                    </label>
                                @endif
                                <input id="seo_favicon" name="seo_favicon" type="file" accept=".ico,image/png,image/svg+xml,image/webp"
                                       class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700">
                                @error('seo_favicon') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div id="branding-promo" x-show="section === 'promo'" x-cloak class="scroll-mt-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">Promo / announcement banner</h2>
                        <p class="text-xs text-gray-500 mb-3">Show a temporary message above the landing page navigation.</p>
                        <div class="rounded-xl border border-gray-200 p-4 space-y-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="promo_enabled" value="1"
                                       @checked(old('promo_enabled', (bool)($tenant->metadata['promo_enabled'] ?? false)))
                                       class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span>Enable promo banner</span>
                            </label>

                            <div>
                                <label for="promo_text" class="block text-sm font-medium text-gray-700">Banner text</label>
                                <input id="promo_text" name="promo_text" type="text" value="{{ old('promo_text', $tenant->metadata['promo_text'] ?? '') }}"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Summer promo: 10% off for weekday stays!">
                                @error('promo_text') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="promo_cta_text" class="block text-sm font-medium text-gray-700">Promo CTA text (optional)</label>
                                    <input id="promo_cta_text" name="promo_cta_text" type="text" value="{{ old('promo_cta_text', $tenant->metadata['promo_cta_text'] ?? '') }}"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                           placeholder="Book now">
                                    @error('promo_cta_text') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="promo_cta_url" class="block text-sm font-medium text-gray-700">Promo CTA link (optional)</label>
                                    <input id="promo_cta_url" name="promo_cta_url" type="text" value="{{ old('promo_cta_url', $tenant->metadata['promo_cta_url'] ?? '') }}"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                           placeholder="#rooms or /user/register">
                                    @error('promo_cta_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="promo_image" class="block text-sm font-medium text-gray-700">Promo image (optional)</label>
                                <div class="mt-2 mb-2 flex items-center gap-3">
                                    <img x-show="promoImagePreview" x-cloak :src="promoImagePreview" alt="Promo image preview" class="h-20 w-20 rounded-xl object-cover border border-gray-200">
                                    <span x-show="!promoImagePreview" x-cloak class="inline-flex h-20 w-20 items-center justify-center rounded-xl border border-dashed border-gray-300 text-[11px] text-gray-500">No image</span>
                                    <button type="button"
                                            @click="promoImagePreview = ''; if ($refs.promoImageInput) { $refs.promoImageInput.value = ''; } if ($refs.promoImageCroppedInput) { $refs.promoImageCroppedInput.value = ''; }"
                                            class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50">
                                        Clear selected
                                    </button>
                                </div>
                                <input id="promo_image_cropped_input" type="hidden" name="promo_image_cropped" x-ref="promoImageCroppedInput">
                                <input id="promo_image" name="promo_image" type="file" accept="image/*" x-ref="promoImageInput"
                                       @change="
                                            const file = $event.target.files && $event.target.files[0] ? $event.target.files[0] : null;
                                            if (!file) { return; }
                                            if ($refs.promoImageCroppedInput) { $refs.promoImageCroppedInput.value = ''; }
                                            openPromoCropper(file);
                                       "
                                       class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700">
                                @error('promo_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="promo_start_date" class="block text-sm font-medium text-gray-700">Start date (optional)</label>
                                    <input id="promo_start_date" name="promo_start_date" type="date"
                                           value="{{ old('promo_start_date', $tenant->metadata['promo_start_date'] ?? '') }}"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @error('promo_start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="promo_end_date" class="block text-sm font-medium text-gray-700">End date (optional)</label>
                                    <input id="promo_end_date" name="promo_end_date" type="date"
                                           value="{{ old('promo_end_date', $tenant->metadata['promo_end_date'] ?? '') }}"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @error('promo_end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="promo_dismissible" value="1"
                                       @checked(old('promo_dismissible', (bool)($tenant->metadata['promo_dismissible'] ?? true)))
                                       class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span>Allow visitors to dismiss banner</span>
                            </label>

                            <div>
                                <label for="promo_frequency_days" class="block text-sm font-medium text-gray-700">Dismiss frequency (days)</label>
                                <input id="promo_frequency_days" name="promo_frequency_days" type="number" min="0" max="365"
                                       value="{{ old('promo_frequency_days', (int)($tenant->metadata['promo_frequency_days'] ?? 7)) }}"
                                       class="mt-1 block w-full max-w-[180px] rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                <p class="mt-1 text-xs text-gray-500">0 means show again on next visit after dismiss.</p>
                                @error('promo_frequency_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-3 z-10 flex items-center gap-4 rounded-xl border border-gray-200 bg-white/95 p-3 backdrop-blur">
                        <x-busy-submit class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700" busy-text="{{ __('Saving…') }}">{{ __('Update branding') }}</x-busy-submit>
                        <a href="{{ tenant_url('dashboard') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                    </div>

                </div>
            </x-form-with-busy>
            </div>
        </div>

        <template x-teleport="body">
            <div x-show="promoCropOpen" x-cloak class="fixed inset-0 z-[140] flex items-center justify-center p-4 pointer-events-none">
                <div class="absolute inset-0 z-0 bg-slate-900/55 backdrop-blur-sm" @click="promoCropOpen = false"></div>
                <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-4 shadow-2xl pointer-events-auto">
                    <h3 class="text-sm font-semibold text-gray-900">Crop promo image</h3>
                    <p class="mt-1 text-xs text-gray-500">Square crop for the floating promo card.</p>
                    <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 p-2">
                        <canvas x-ref="promoCropCanvas" width="320" height="320" class="h-72 w-full rounded-lg bg-white"></canvas>
                    </div>
                    <div class="mt-3 space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Zoom</label>
                        <input type="range" min="1" max="2.4" step="0.01" x-model.number="cropScale" @input="drawPromoCropCanvas()" class="w-full">
                        <label class="block text-xs font-medium text-gray-700">Horizontal</label>
                        <input type="range" min="-220" max="220" step="1" x-model.number="cropX" @input="drawPromoCropCanvas()" class="w-full">
                        <label class="block text-xs font-medium text-gray-700">Vertical</label>
                        <input type="range" min="-220" max="220" step="1" x-model.number="cropY" @input="drawPromoCropCanvas()" class="w-full">
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button type="button" @click.prevent.stop="promoCropOpen = false" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700">Cancel</button>
                        <button type="button" @click.prevent.stop="applyPromoCrop()" class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-medium text-white">Apply crop</button>
                    </div>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="logoCropOpen" x-cloak class="fixed inset-0 z-[140] flex items-center justify-center p-4 pointer-events-none">
                <div class="absolute inset-0 z-0 bg-slate-900/55 backdrop-blur-sm" @click="logoCropOpen = false"></div>
                <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-4 shadow-2xl pointer-events-auto">
                    <h3 class="text-sm font-semibold text-gray-900">Crop logo</h3>
                    <p class="mt-1 text-xs text-gray-500">Square crop for brand logo.</p>
                    <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 p-2">
                        <canvas x-ref="logoCropCanvas" width="320" height="320" class="h-72 w-full rounded-lg bg-white"></canvas>
                    </div>
                    <div class="mt-3 space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Zoom</label>
                        <input type="range" min="1" max="2.4" step="0.01" x-model.number="logoCropScale" @input="drawLogoCropCanvas()" class="w-full">
                        <label class="block text-xs font-medium text-gray-700">Horizontal</label>
                        <input type="range" min="-220" max="220" step="1" x-model.number="logoCropX" @input="drawLogoCropCanvas()" class="w-full">
                        <label class="block text-xs font-medium text-gray-700">Vertical</label>
                        <input type="range" min="-220" max="220" step="1" x-model.number="logoCropY" @input="drawLogoCropCanvas()" class="w-full">
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button type="button" @click.prevent.stop="logoCropOpen = false" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700">Cancel</button>
                        <button type="button" @click.prevent.stop="applyLogoCrop()" class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-medium text-white">Apply crop</button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-tenant::app-layout>
