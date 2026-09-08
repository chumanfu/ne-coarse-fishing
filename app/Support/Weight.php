<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * A fish weight, stored canonically as whole grams.
 *
 * Grams keep the arithmetic exact: anglers enter lb/oz or kg, and round-tripping
 * through a decimal pound would drift (12lb 6oz is 12.375lb, but 1oz is a
 * recurring fraction of a pound). Integer grams convert cleanly both ways.
 */
final class Weight
{
    public const UNIT_LB_OZ = 'lb_oz';

    public const UNIT_KG = 'kg';

    public const UNITS = [self::UNIT_LB_OZ, self::UNIT_KG];

    private const GRAMS_PER_POUND = 453.59237;

    private const GRAMS_PER_OUNCE = 28.349523125;

    private const OUNCES_PER_POUND = 16;

    private function __construct(public readonly int $grams) {}

    public static function fromGrams(int|float|string|null $grams): ?self
    {
        if ($grams === null || $grams === '') {
            return null;
        }

        $value = (int) round((float) $grams);

        return $value < 0 ? null : new self($value);
    }

    /**
     * Build from the lb + oz pair the form collects. Either part may be blank,
     * so "8oz" on its own is valid.
     */
    public static function fromPoundsAndOunces(int|float|string|null $pounds, int|float|string|null $ounces): ?self
    {
        $hasPounds = $pounds !== null && $pounds !== '';
        $hasOunces = $ounces !== null && $ounces !== '';

        if (! $hasPounds && ! $hasOunces) {
            return null;
        }

        $totalGrams = ((float) ($hasPounds ? $pounds : 0)) * self::GRAMS_PER_POUND
            + ((float) ($hasOunces ? $ounces : 0)) * self::GRAMS_PER_OUNCE;

        return self::fromGrams($totalGrams);
    }

    public static function fromKilograms(int|float|string|null $kilograms): ?self
    {
        if ($kilograms === null || $kilograms === '') {
            return null;
        }

        return self::fromGrams((float) $kilograms * 1000);
    }

    /** Used to migrate the legacy `weight_lb` decimal column. */
    public static function fromDecimalPounds(int|float|string|null $pounds): ?self
    {
        if ($pounds === null || $pounds === '') {
            return null;
        }

        return self::fromGrams((float) $pounds * self::GRAMS_PER_POUND);
    }

    /**
     * Build from whichever unit the angler was typing in.
     *
     * @param  array{pounds?: mixed, ounces?: mixed, kilograms?: mixed}  $input
     */
    public static function fromInput(string $unit, array $input): ?self
    {
        return match ($unit) {
            self::UNIT_LB_OZ => self::fromPoundsAndOunces($input['pounds'] ?? null, $input['ounces'] ?? null),
            self::UNIT_KG => self::fromKilograms($input['kilograms'] ?? null),
            default => throw new InvalidArgumentException("Unknown weight unit [{$unit}]."),
        };
    }

    public function kilograms(): float
    {
        return round($this->grams / 1000, 3);
    }

    public function decimalPounds(): float
    {
        return round($this->grams / self::GRAMS_PER_POUND, 3);
    }

    /**
     * Whole pounds, after ounces have been rounded. Rounding 15.6oz up to 16oz
     * has to carry into the pounds or we would render "3lb 16oz".
     */
    public function pounds(): int
    {
        return intdiv($this->totalOunces(), self::OUNCES_PER_POUND);
    }

    public function ounces(): int
    {
        return $this->totalOunces() % self::OUNCES_PER_POUND;
    }

    private function totalOunces(): int
    {
        return (int) round($this->grams / self::GRAMS_PER_OUNCE);
    }

    public function format(string $unit = self::UNIT_LB_OZ): string
    {
        if ($unit === self::UNIT_KG) {
            return rtrim(rtrim(number_format($this->kilograms(), 2, '.', ''), '0'), '.').' kg';
        }

        // Sub-pound fish read as "8oz", not "0lb 8oz".
        return $this->pounds() === 0
            ? $this->ounces().'oz'
            : $this->pounds().'lb '.$this->ounces().'oz';
    }

    /** The other unit, for the live conversion hint under the input. */
    public function formatAlternate(string $unit = self::UNIT_LB_OZ): string
    {
        return $this->format($unit === self::UNIT_KG ? self::UNIT_LB_OZ : self::UNIT_KG);
    }

    public static function normaliseUnit(?string $unit): string
    {
        return in_array($unit, self::UNITS, true) ? $unit : self::UNIT_LB_OZ;
    }
}
