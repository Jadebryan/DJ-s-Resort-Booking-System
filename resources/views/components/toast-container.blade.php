@php
    $rawStatus = session('status');
    $knownKeys = ['profile-updated', 'password-updated', 'verification-link-sent'];
    $statusAsMessage = null;
    if (is_string($rawStatus) && $rawStatus !== '') {
        if ($rawStatus === 'profile-updated' || $rawStatus === 'password-updated') {
            $statusAsMessage = __('Saved.');
        } elseif ($rawStatus === 'verification-link-sent') {
            $statusAsMessage = __('A new verification link has been sent to your email address.');
        } elseif (! in_array($rawStatus, $knownKeys, true)) {
            $statusAsMessage = $rawStatus;
        }
    }

    $validationMessage = null;
    if (!session('success') && !session('error') && !session('info') && !$statusAsMessage && $errors->any()) {
        $validationMessage = __('Please review the form and fix the highlighted fields.');
    }
    $message = session('success') ?: session('error') ?: session('info') ?: $statusAsMessage ?: $validationMessage;
    $type = session('error') || $validationMessage ? 'error' : ((session('success') || $statusAsMessage) ? 'success' : 'info');
@endphp
@if($message)
<div x-data="{
    show: true,
    init() {
        const self = this;
        setTimeout(() => { self.show = false; }, 5000);
    }
}"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="fixed top-20 right-4 z-[200] flex max-w-sm items-start gap-3 rounded-xl border shadow-lg
            @if($type === 'success') border-green-200 bg-green-50 text-green-800
            @elseif($type === 'error') border-red-200 bg-red-50 text-red-800
            @else border-blue-200 bg-blue-50 text-blue-800 @endif"
     role="alert">
    @if($type === 'success')
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </span>
    @elseif($type === 'error')
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </span>
    @else
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
    @endif
    <p class="flex-1 py-2.5 pr-2 text-sm font-medium">{{ $message }}</p>
    <button type="button"
            @click="show = false"
            class="rounded-lg p-1.5 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-1 @if($type === 'success') focus:ring-green-500 @elseif($type === 'error') focus:ring-red-500 @else focus:ring-blue-500 @endif"
            aria-label="Close">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
@endif
