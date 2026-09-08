<?php

namespace Tests\Unit;

use App\Support\Weight;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    public function test_it_builds_from_pounds_and_ounces(): void
    {
        $weight = Weight::fromPoundsAndOunces(12, 6);

        $this->assertSame(5613, $weight->grams);
        $this->assertSame(12, $weight->pounds());
        $this->assertSame(6, $weight->ounces());
        $this->assertSame('12lb 6oz', $weight->format(Weight::UNIT_LB_OZ));
    }

    public function test_it_builds_from_kilograms(): void
    {
        $weight = Weight::fromKilograms(5.67);

        $this->assertSame(5670, $weight->grams);
        $this->assertSame('5.67 kg', $weight->format(Weight::UNIT_KG));
        $this->assertSame('12lb 8oz', $weight->format(Weight::UNIT_LB_OZ));
    }

    public function test_either_half_of_the_lb_oz_pair_may_be_blank(): void
    {
        $this->assertSame(227, Weight::fromPoundsAndOunces(null, 8)->grams);
        $this->assertSame(454, Weight::fromPoundsAndOunces(1, null)->grams);
        $this->assertNull(Weight::fromPoundsAndOunces(null, null));
        $this->assertNull(Weight::fromPoundsAndOunces('', ''));
    }

    public function test_rounded_up_ounces_carry_into_the_pounds(): void
    {
        $weight = Weight::fromPoundsAndOunces(3, 15.9);

        // Must never render as "3lb 16oz".
        $this->assertSame(4, $weight->pounds());
        $this->assertSame(0, $weight->ounces());
        $this->assertSame('4lb 0oz', $weight->format(Weight::UNIT_LB_OZ));
    }

    public function test_sub_pound_fish_drop_the_pounds_from_the_label(): void
    {
        $this->assertSame('8oz', Weight::fromPoundsAndOunces(0, 8)->format(Weight::UNIT_LB_OZ));
    }

    public function test_it_round_trips_through_grams_without_drift(): void
    {
        foreach (range(0, 15) as $ounces) {
            $weight = Weight::fromPoundsAndOunces(7, $ounces);

            $this->assertSame(7, $weight->pounds(), "7lb {$ounces}oz lost its pounds");
            $this->assertSame($ounces, $weight->ounces(), "7lb {$ounces}oz lost its ounces");
        }
    }

    public function test_it_converts_the_legacy_decimal_pound_column(): void
    {
        $this->assertSame('12lb 8oz', Weight::fromDecimalPounds(12.5)->format(Weight::UNIT_LB_OZ));
        $this->assertSame('3lb 4oz', Weight::fromDecimalPounds(3.25)->format(Weight::UNIT_LB_OZ));
        $this->assertNull(Weight::fromDecimalPounds(null));
    }

    public function test_it_picks_the_unit_the_angler_used(): void
    {
        $lbOz = Weight::fromInput(Weight::UNIT_LB_OZ, ['pounds' => 2, 'ounces' => 0, 'kilograms' => 99]);
        $kg = Weight::fromInput(Weight::UNIT_KG, ['pounds' => 99, 'ounces' => 99, 'kilograms' => 1]);

        $this->assertSame(907, $lbOz->grams);
        $this->assertSame(1000, $kg->grams);
    }

    public function test_it_falls_back_to_lb_oz_for_unknown_units(): void
    {
        $this->assertSame(Weight::UNIT_LB_OZ, Weight::normaliseUnit(null));
        $this->assertSame(Weight::UNIT_LB_OZ, Weight::normaliseUnit('stones'));
        $this->assertSame(Weight::UNIT_KG, Weight::normaliseUnit('kg'));
    }

    public function test_negative_weights_are_rejected(): void
    {
        $this->assertNull(Weight::fromGrams(-100));
    }
}
