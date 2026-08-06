<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\VenuePhoto;
use App\Models\WaterPegPhoto;
use App\Support\Uploads;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

#[Signature('uploads:migrate-stock-images {--dry-run : Show what would be migrated without writing}')]
#[Description('Copy public/images stock assets onto the uploads disk and update database paths')]
class MigrateStockImagesToUploadsCommand extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        $this->info('Uploads disk: '.Uploads::diskName());

        foreach ($this->targets() as $target) {
            try {
                if ($dryRun) {
                    $this->line("Would migrate [{$target['from']}] → [{$target['to']}] ({$target['label']})");
                    $migrated++;

                    continue;
                }

                Uploads::promotePublicPath($target['from'], $target['to']);
                $target['update']($target['to']);
                $this->line("Migrated [{$target['from']}] → [{$target['to']}]");
                $migrated++;
            } catch (Throwable $e) {
                if (str_contains($e->getMessage(), 'Public file missing')) {
                    $this->warn("Skipped missing file [{$target['from']}]");
                    $skipped++;

                    continue;
                }

                $this->error("Failed [{$target['from']}]: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Migrated: {$migrated}, skipped: {$skipped}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<array{label: string, from: string, to: string, update: callable(string): void}>
     */
    private function targets(): array
    {
        $targets = [];

        foreach (VenuePhoto::query()->where('image_path', 'like', 'images/%')->cursor() as $photo) {
            $targets[] = [
                'label' => "venue_photo:{$photo->id}",
                'from' => $photo->image_path,
                'to' => $this->destination('venue-photos', $photo->image_path),
                'update' => function (string $path) use ($photo): void {
                    $photo->update(['image_path' => $path]);
                },
            ];
        }

        foreach (WaterPegPhoto::query()->where('image_path', 'like', 'images/%')->cursor() as $photo) {
            $targets[] = [
                'label' => "peg_photo:{$photo->id}",
                'from' => $photo->image_path,
                'to' => $this->destination('peg-photos', $photo->image_path),
                'update' => function (string $path) use ($photo): void {
                    $photo->update(['image_path' => $path]);
                },
            ];
        }

        foreach (Club::query()->where('logo_path', 'like', 'images/%')->cursor() as $club) {
            $targets[] = [
                'label' => "club:{$club->id}",
                'from' => $club->logo_path,
                'to' => $this->destination('club-logos', $club->logo_path),
                'update' => function (string $path) use ($club): void {
                    $club->update(['logo_path' => $path]);
                },
            ];
        }

        foreach (TackleShop::query()->where('logo_path', 'like', 'images/%')->cursor() as $shop) {
            $targets[] = [
                'label' => "tackle_shop:{$shop->id}",
                'from' => $shop->logo_path,
                'to' => $this->destination('tackle-shop-logos', $shop->logo_path),
                'update' => function (string $path) use ($shop): void {
                    $shop->update(['logo_path' => $path]);
                },
            ];
        }

        return $targets;
    }

    private function destination(string $directory, string $publicRelativePath): string
    {
        $relative = Str::after($publicRelativePath, 'images/');

        return trim($directory.'/stock/'.$relative, '/');
    }
}
