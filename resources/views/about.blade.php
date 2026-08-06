<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">About NE Coarse Fishing</h1>
        <p class="text-slate-600 mt-1">Why this site exists, from Chris.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <article class="bg-white border-2 border-slate-300 rounded-xl p-6 sm:p-8 space-y-5 text-slate-800 leading-relaxed">
            <p>For 37 years, coarse angling has been my main passion. Most of that time was spent exploring the waters of North Lincolnshire, with a strong bias toward the lakes and rivers around Grimsby.</p>

            <p>In 2011, I moved north to Newcastle, but my tackle remained firmly planted in Lincolnshire. That all changed when my parents decided their garage desperately needed a clear out. Suddenly, my gear was repatriated—much to the absolute delight of my wife and two kids, who were thrilled to welcome decades of stored fishing equipment into our home.</p>

            <p>Reconnecting with my old kit instantly rekindled my love for the sport. But it also left me facing a blank slate: where do I actually fish around here?</p>

            <p>Starting from scratch in the North East meant spending endless hours piecing together information. I found myself bouncing between outdated club websites, scattered Facebook pages, and satellite views on Google Maps just to locate decent waters.</p>

            <p>As a software engineer, my developer brain kicked into gear. Finding a local venue, checking current rules, or discovering a reliable tackle shop shouldn't feel like a high-stakes detective mission. There had to be a cleaner, more central way for anglers to discover the region's fishing.</p>

            <p>That frustration was the catalyst for NE Coarse Fishing.</p>

            <p>My goal was to build a dedicated hub for pleasure anglers across the region. NE Coarse Fishing brings together venues, clubs, and local tackle shops into one clear, accessible platform. More than just a directory, the site includes session logging—allowing anglers to share real-time catch reports, working tactics, and effective baits so the community stays informed on what’s actually working on the bank.</p>

            <p>Right now, the platform provides the framework, but its real value comes from the community. NE Coarse Fishing is designed to be built by local anglers, venue owners, and tackle shops working together to map out the current North East fishing scene.</p>

            <p>Whether you're a lifelong local or fresh to the region like I was, welcome aboard. Dig around, add your favourite spots, log a session, and help us build the ultimate resource for North East coarse angling.</p>

            <p class="pt-2">
                Tight lines,<br>
                <span class="font-bold text-slate-900">Chris</span>
            </p>
        </article>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('venues.index') }}" class="px-4 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">Browse venues</a>
            <a href="{{ route('refer') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-sm">Refer a friend</a>
            <a href="{{ route('contact.create') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-sm">Contact</a>
        </div>
    </div>
</x-app-layout>
