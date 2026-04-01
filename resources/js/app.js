import './bootstrap';

import Alpine from 'alpinejs';

function readSidebarRailCollapsed(railKey) {
    try {
        return localStorage.getItem(railKey) === '1';
    } catch {
        return false;
    }
}

document.addEventListener('alpine:init', () => {
    /**
     * Localized form submit state: blocks duplicate POSTs, optional overlay + busyMessage.
     * Use with <x-form-with-busy> and <x-busy-submit> (or wire:loading for Livewire).
     */
    Alpine.data('formWithBusy', (config = {}) => ({
        submitting: false,
        busyMessage: typeof config.busyMessage === 'string' && config.busyMessage !== '' ? config.busyMessage : 'Saving…',
        showOverlay: Boolean(config.showOverlay),
        handleSubmit(e) {
            if (this.submitting) {
                e.preventDefault();
                return;
            }
            this.submitting = true;
        },
    }));
    /** Scroll hints for sidebar nav: fades + chevron only when content overflows. */
    Alpine.data('sidebarNavScroll', () => ({
        showTopFade: false,
        showBottomFade: false,
        _ro: null,
        _onResize: null,
        init() {
            this._onResize = () => this.measure();
            window.addEventListener('resize', this._onResize);
            this.$nextTick(() => {
                this.measure();
                const el = this.$refs.panel;
                if (el && typeof ResizeObserver !== 'undefined') {
                    this._ro = new ResizeObserver(() => this.measure());
                    this._ro.observe(el);
                }
            });
        },
        destroy() {
            window.removeEventListener('resize', this._onResize);
            this._ro?.disconnect();
        },
        measure() {
            const el = this.$refs.panel;
            if (!el) {
                return;
            }
            const { scrollTop, scrollHeight, clientHeight } = el;
            const eps = 3;
            const scrollable = scrollHeight > clientHeight + eps;
            this.showTopFade = scrollable && scrollTop > eps;
            this.showBottomFade = scrollable && scrollTop + clientHeight < scrollHeight - eps;
        },
    }));

    Alpine.data('dashboardShell', (railKey) => ({
        sidebarOpen: false,
        sidebarCollapsed: readSidebarRailCollapsed(railKey),
        sidebarRailKey: railKey,
        autoRefreshMs: 60000,
        init() {
            try {
                document.documentElement.classList.toggle('rail-collapsed', this.sidebarCollapsed);
            } catch (e) {}
            this.initAutoRefresh();
            window.addEventListener('storage', (e) => {
                if (e.key === this.sidebarRailKey && e.storageArea === localStorage) {
                    this.sidebarCollapsed = e.newValue === '1';
                    try {
                        document.documentElement.classList.toggle('rail-collapsed', this.sidebarCollapsed);
                    } catch (err) {}
                }
            });
        },
        initAutoRefresh() {
            setInterval(() => {
                if (document.visibilityState !== 'visible') return;
                const el = document.activeElement;
                const isTyping =
                    el &&
                    (el.tagName === 'INPUT' ||
                        el.tagName === 'TEXTAREA' ||
                        el.tagName === 'SELECT' ||
                        el.isContentEditable);
                if (isTyping) return;
                window.location.reload();
            }, this.autoRefreshMs);
        },
        toggleSidebarRail() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            try {
                localStorage.setItem(this.sidebarRailKey, this.sidebarCollapsed ? '1' : '0');
                document.documentElement.classList.toggle('rail-collapsed', this.sidebarCollapsed);
            } catch (e) {}
        },
    }));
});

window.Alpine = Alpine;

Alpine.start();
