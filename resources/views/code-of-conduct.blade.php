<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Code of conduct &amp; privacy</h1>
        <p class="text-slate-600 mt-1">How we expect people to behave online, and how we look after your data.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <article class="bg-white border-2 border-slate-300 rounded-xl p-6 sm:p-8 space-y-5 text-slate-800 leading-relaxed">
            <h2 id="community-standards" class="text-xl font-bold text-slate-900">Community standards</h2>

            <p>
                NE Coarse Fishing is a place for anglers to share venues, sessions, tactics and local knowledge.
                We want the site to stay welcoming, practical and respectful — on the bank and online.
            </p>

            <p class="font-semibold text-slate-900">
                Online abuse of any nature is not permitted.
            </p>

            <p>That includes, but is not limited to:</p>

            <ul class="list-disc pl-5 space-y-2">
                <li>Harassment, threats, intimidation or bullying</li>
                <li>Hate speech, discriminatory language or targeting people for who they are</li>
                <li>Sexual harassment or unwanted sexual comments</li>
                <li>Doxxing, sharing private information without consent, or encouraging others to pile on</li>
                <li>Repeated unwanted contact through messaging or public content</li>
                <li>Trolling, baiting or content posted mainly to upset or provoke others</li>
                <li>Impersonation, spam, or deliberately misleading posts</li>
            </ul>

            <p>
                These rules apply to messages, session write-ups, tactics, reviews, venue suggestions, announcements,
                profile content and any other contribution on the site.
            </p>

            <h3 class="text-lg font-bold text-slate-900 pt-2">What we expect</h3>
            <ul class="list-disc pl-5 space-y-2">
                <li>Be civil, even when you disagree about pegs, venues, clubs or tackle</li>
                <li>Keep contributions accurate and useful for other anglers</li>
                <li>Respect fishery rules, private land and other people’s safety</li>
                <li>Do not post content that is illegal, or that encourages illegal activity</li>
            </ul>

            <h3 class="text-lg font-bold text-slate-900 pt-2">Reporting and enforcement</h3>
            <p>
                If you see abuse or feel unsafe, please
                <a href="{{ route('contact.create') }}" class="font-semibold text-sky-800 underline">contact us</a>
                with links or details. We may remove content, warn accounts, restrict features or permanently ban users
                who break these standards. Serious abuse may also be reported to the relevant authorities.
            </p>
        </article>

        <article class="bg-white border-2 border-slate-300 rounded-xl p-6 sm:p-8 space-y-5 text-slate-800 leading-relaxed">
            <h2 id="your-data" class="text-xl font-bold text-slate-900">How we use your data</h2>

            <p>
                We process personal data so the site can run as a community fishing directory and session log for the
                North East. The main purposes are:
            </p>

            <ul class="list-disc pl-5 space-y-2">
                <li>Creating and managing your account (name, email and password)</li>
                <li>Showing public contributions such as venues, sessions, tactics, reviews and match reports</li>
                <li>Operating messaging, favourites, claims and edit suggestions</li>
                <li>Sending service emails (for example password resets and important account notices)</li>
                <li>Keeping the site secure, moderating abuse and investigating misuse</li>
                <li>Improving the service and understanding how it is used at a high level</li>
            </ul>

            <p>
                We do not sell your personal data. Passwords are stored hashed and are never included in data exports.
                Public content you choose to publish (for example session reports) can be seen by other visitors.
            </p>

            <p>
                Typical information we hold includes your account details, fishing sessions and photos you upload,
                venue or club contributions, favourites, messages, tackle reviews and related moderation records.
            </p>
        </article>

        <article class="bg-white border-2 border-slate-300 rounded-xl p-6 sm:p-8 space-y-5 text-slate-800 leading-relaxed">
            <h2 id="gdpr-rights" class="text-xl font-bold text-slate-900">Your GDPR rights</h2>

            <p>
                If you have an account with us, you can exercise common UK GDPR rights directly from your profile,
                or by contacting us.
            </p>

            <h3 class="text-lg font-bold text-slate-900 pt-2">Download a copy of your data</h3>
            <p>
                Signed-in users can download a JSON export of the personal information we hold from
                @auth
                    <a href="{{ route('profile.edit') }}" class="font-semibold text-sky-800 underline">Profile → Your data (GDPR)</a>.
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-sky-800 underline">your profile</a>
                    (log in first, then open Your data / GDPR).
                @endauth
                The export covers your profile, messages, fishing sessions, venue contributions, favourites, tackle reviews
                and related records. Passwords are never included.
            </p>

            <h3 class="text-lg font-bold text-slate-900 pt-2">Delete your account</h3>
            <p>
                You can delete your account from the same profile page. Once deleted, your account and associated personal
                data are removed as described in the confirmation flow. Some anonymised or legally required records may be
                retained where we need them for security, dispute handling or legal compliance.
            </p>

            <h3 class="text-lg font-bold text-slate-900 pt-2">Other requests</h3>
            <p>
                To ask us to correct inaccurate information, restrict processing, object to certain uses, or raise any other
                privacy request, email us via the
                <a href="{{ route('contact.create') }}" class="font-semibold text-sky-800 underline">contact form</a>
                and include “GDPR request” in the subject. We will respond as soon as we can, and within the timescales
                required by law.
            </p>

            <p class="text-sm text-slate-600 pt-2">
                This page explains our current practice in plain English. It is not legal advice. If our processing changes
                in a material way, we will update this page.
            </p>
        </article>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('contact.create') }}" class="px-4 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">Contact us</a>
            @auth
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-sm">Open profile &amp; data tools</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-sm">Log in to manage your data</a>
            @endauth
        </div>
    </div>
</x-app-layout>
