<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Your data (GDPR)
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Download a copy of the personal information we hold about you. This includes your profile, messages, fishing sessions, venue contributions, favourites, tackle reviews, and related records. Passwords are never included.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.data-export') }}">
        @csrf
        <x-primary-button>
            Download my data
        </x-primary-button>
    </form>
</section>
