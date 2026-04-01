<x-tenant::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-[11px] leading-snug text-slate-600 sm:text-xs">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-tenant::primary-button class="w-full min-h-10 justify-center py-2.5 text-sm font-semibold sm:w-auto bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 hover:brightness-110 border-0 rounded-lg shadow-sm">
                {{ __('Resend Verification Email') }}
            </x-tenant::primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="w-full text-center text-[11px] font-medium text-slate-600 underline underline-offset-2 hover:text-slate-900 sm:w-auto sm:text-xs">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-tenant::guest-layout>
