@php
    $googleConfigured = filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
@endphp

<div class="mt-6">
    <div class="relative">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-300 dark:border-slate-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white px-2 text-slate-500 dark:bg-slate-900 dark:text-slate-300">{{ $divider ?? 'Or continue with' }}</span>
        </div>
    </div>

    <div class="mt-4">
        @if ($googleConfigured)
            <a href="{{ route('auth.google') }}"
               class="flex w-full items-center justify-center gap-3 rounded-md border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900">
                <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-.9 2.3-1.9 3l3.1 2.4c1.8-1.7 2.9-4.1 2.9-7 0-.7-.1-1.3-.2-1.9H12z"/>
                    <path fill="#34A853" d="M6.6 14.3l-.6.5-2.1 1.6C5.5 19.1 8.5 21 12 21c2.4 0 4.4-.8 5.9-2.2l-3.1-2.4c-.8.6-1.9.9-2.8.9-2.2 0-4-1.5-4.7-3.5z"/>
                    <path fill="#4A90E2" d="M4 7.6C3.4 8.8 3 10.1 3 11.5s.4 2.7 1 3.9l2.7-2.1c-.2-.5-.3-1.1-.3-1.8 0-.7.1-1.3.3-1.8L4 7.6z"/>
                    <path fill="#FBBC05" d="M12 5.3c1.3 0 2.5.5 3.4 1.3l2.5-2.5C16.4 2.7 14.4 2 12 2 8.5 2 5.5 3.9 4 7.1l2.7 2.1C7.9 6.8 9.8 5.3 12 5.3z"/>
                </svg>
                {{ $label ?? 'Continue with Google' }}
            </a>
        @else
            <p class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:border-amber-500 dark:bg-amber-950 dark:text-amber-100">
                Google sign-in will appear here once Google OAuth credentials are configured.
            </p>
        @endif
    </div>
</div>
