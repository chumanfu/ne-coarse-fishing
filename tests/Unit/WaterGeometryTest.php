<?php

namespace Tests\Unit;

use App\Support\WaterGeometry;
use PHPUnit\Framework\TestCase;

class WaterGeometryTest extends TestCase
{
    public function test_normalizes_valid_linestring(): void
    {
        $normalized = WaterGeometry::normalize([
            'type' => 'LineString',
            'coordinates' => [
                [-1.58001234, 54.78009876],
                [-1.57, 54.79],
            ],
        ]);

        $this->assertSame('LineString', $normalized['type']);
        $this->assertSame(-1.5800123, $normalized['coordinates'][0][0]);
        $this->assertSame(54.7800988, $normalized['coordinates'][0][1]);
        $this->assertCount(2, $normalized['coordinates']);
    }

    public function test_rejects_single_point_and_out_of_gb(): void
    {
        $this->assertNull(WaterGeometry::normalize([
            'type' => 'LineString',
            'coordinates' => [[-1.58, 54.78]],
        ]));

        $this->assertNull(WaterGeometry::normalize([
            'type' => 'LineString',
            'coordinates' => [
                [10.0, 54.78],
                [10.1, 54.79],
            ],
        ]));

        $this->assertNull(WaterGeometry::normalize([
            'type' => 'Polygon',
            'coordinates' => [[[-1.58, 54.78], [-1.57, 54.79], [-1.58, 54.78]]],
        ]));
    }
}
