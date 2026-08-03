<?php

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\Venue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deleteVenueBySlug('beamish-park-lake');

        foreach ($this->venueUrls() as $slug => $url) {
            Venue::query()->where('slug', $slug)->update(['url' => $url]);
        }

        foreach ($this->clubUrls() as $slug => $url) {
            Club::query()->where('slug', $slug)->update(['url' => $url]);
        }

        foreach ($this->tackleShopUrls() as $slug => $url) {
            TackleShop::query()->where('slug', $slug)->update(['url' => $url]);
        }
    }

    public function down(): void
    {
        // Data migration — intentionally not reversed.
    }

    private function deleteVenueBySlug(string $slug): void
    {
        $venue = Venue::query()->where('slug', $slug)->first();

        if (! $venue) {
            return;
        }

        $venueId = $venue->id;

        if (Schema::hasTable('activities')) {
            DB::table('activities')
                ->where('subject_type', Venue::class)
                ->where('subject_id', $venueId)
                ->delete();
        }

        $waterIds = DB::table('waters')->where('venue_id', $venueId)->pluck('id');

        if ($waterIds->isNotEmpty()) {
            if (Schema::hasTable('water_pegs')) {
                $pegIds = DB::table('water_pegs')->whereIn('water_id', $waterIds)->pluck('id');

                if ($pegIds->isNotEmpty() && Schema::hasTable('water_peg_photos')) {
                    DB::table('water_peg_photos')->whereIn('water_peg_id', $pegIds)->delete();
                }

                DB::table('water_pegs')->whereIn('water_id', $waterIds)->delete();
            }

            if (Schema::hasTable('water_species')) {
                DB::table('water_species')->whereIn('water_id', $waterIds)->delete();
            }
        }

        $sessionIds = DB::table('fishing_sessions')->where('venue_id', $venueId)->pluck('id');

        if ($sessionIds->isNotEmpty()) {
            if (Schema::hasTable('session_photos')) {
                DB::table('session_photos')->whereIn('fishing_session_id', $sessionIds)->delete();
            }

            if (Schema::hasTable('session_catches')) {
                DB::table('session_catches')->whereIn('fishing_session_id', $sessionIds)->delete();
            }

            DB::table('fishing_sessions')->whereIn('id', $sessionIds)->delete();
        }

        foreach (['match_reports', 'announcements', 'venue_claims', 'venue_edit_requests', 'venue_tactics', 'venue_photos'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('venue_id', $venueId)->delete();
            }
        }

        DB::table('waters')->where('venue_id', $venueId)->delete();
        DB::table('venues')->where('id', $venueId)->delete();
    }

    /**
     * @return array<string, string>
     */
    private function venueUrls(): array
    {
        return [
            'killingworth-lakes' => 'https://thetaa.co.uk/',
            'eden-grange' => 'https://edengrangefishery.co.uk/',
            'hebron-lake' => 'https://hebronlakes.clubmate.co.uk/home',
            'qe-ii' => 'https://www.wacac.me.uk/',
            'angel-lakes' => 'https://www.angelfishing.com/',
            'brenkley-pond' => 'https://www.wacac.me.uk/',
            'horton-grange-lake' => 'https://www.wacac.me.uk/',
            'milkhope-lake' => 'https://www.wacac.me.uk/',
            'throckley-reigh' => 'https://thetaa.co.uk/',
            'river-tyne-newburn' => 'https://thetaa.co.uk/',
            'wydon-burn' => 'https://thetaa.co.uk/',
            'fontburn-reservoir' => 'https://www.watersideparksuk.com/park/fontburn/fishing/',
            'whittle-dene-reservoir' => 'https://www.watersideparksuk.com/park/whittle-dene/fishing/',
            'big-waters' => 'https://bwac.club/',
            'stargate' => 'https://www.gateshead.gov.uk/article/4505/Angling-permits-and-fishing-licence',
            'meggies-burn-reservoir' => 'https://bfac.forumotion.com/',
            'bolam-lake' => 'https://www.northumberland.gov.uk/parks/country-parks-visitor-centres-coastal-sites/bolam-lake-country-park',
            'derwent-reservoir' => 'https://www.watersideparksuk.com/park/derwent/fishing/',
            'tunstall-reservoir' => 'https://www.fisheryguide.co.uk/tunstall-reservoir-fishing/',
            'aldin-grange' => 'https://aldingrangelakes.co.uk/',
            'batleys-pond' => 'https://find-and-update.company-information.service.gov.uk/company/13183929',
            'leazes-park-lake' => 'https://leazesangling.com/',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function clubUrls(): array
    {
        return [
            'wansbeck-and-cramlington-angling-club' => 'https://www.wacac.me.uk/',
            'tyne-anglers-alliance' => 'https://thetaa.co.uk/',
            'big-waters-angling-club' => 'https://bwac.club/',
            'hexham-angling-association' => 'https://thetaa.co.uk/',
            'ryton-and-district-angling-club' => 'https://www.gateshead.gov.uk/article/4505/Angling-permits-and-fishing-licence',
            'blyth-freshwater-angling-club' => 'https://bfac.forumotion.com/',
            'durham-city-angling-club' => 'https://www.durhamanglers.co.uk/',
            'chester-le-street-district-angling-club' => 'https://www.chesterlestreetangling.org.uk/',
            'willington-district-angling-club' => 'https://willingtondistrictanglingclub.com/',
            'leazes-park-angling-club' => 'https://leazesangling.com/',
            'tunstall-fly-fishers' => 'https://www.fisheryguide.co.uk/tunstall-reservoir-fishing/',
            'batleys-fishing-club' => 'https://find-and-update.company-information.service.gov.uk/company/13183929',
            'felling-fly-fishing-club' => 'https://www.fellingflyfishers.co.uk/',
            'wansbeck-angling-association' => 'https://www.wansbeckanglingassociation.co.uk/',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function tackleShopUrls(): array
    {
        return [
            'billys-fishing-tackle' => 'https://billysfishing.co.uk/',
            'rutherfords-angling' => 'https://www.rutherfordsangling.co.uk/',
            'north-east-tackle' => 'https://www.northeasttackle.co.uk/',
            'bagnall-and-kirkwood' => 'https://bagnallandkirkwood.co.uk/',
            'angling-direct-stockton' => 'https://www.anglingdirect.co.uk/storelocator/stockton',
            'ad-tackle' => 'https://adtackle.co.uk/',
            // Site serves HTTP only (HTTPS does not resolve).
            'wallys-fishing-tackle' => 'http://wallysfishingtackle.co.uk/',
            'the-anglers-lodge' => 'https://www.anglers-lodge.co.uk/',
            'angling-direct' => 'https://www.anglingdirect.co.uk/',
            'fishdeal' => 'https://www.fishdeal.co.uk/',
            'fishing-tackle-and-bait' => 'https://www.fishingtackleandbait.co.uk/',
            'total-fishing-tackle' => 'https://www.total-fishing-tackle.com/',
            'fishing-megastore' => 'https://www.fishingmegastore.com/',
            'nathans-of-derby' => 'https://www.nathansofderby.com/',
        ];
    }
};
