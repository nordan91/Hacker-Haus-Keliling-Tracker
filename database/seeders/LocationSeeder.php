<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Seed with sample selling locations.
     * Replace coordinates with actual locations near your area.
     */
    public function run(): void
    {
        $locations = [
            [
                'name'          => 'SMAN 71 Jakarta',
                'address'       => 'Jl. Suryo No.71, Kebayoran Baru',
                'latitude'      => -6.2435000,
                'longitude'     => 106.7942000,
                'radius_meters' => 150,
            ],
            [
                'name'          => 'Pasar Minggu',
                'address'       => 'Jl. Raya Pasar Minggu',
                'latitude'      => -6.2845000,
                'longitude'     => 106.8438000,
                'radius_meters' => 200,
            ],
            [
                'name'          => 'Kampus UI Depok',
                'address'       => 'Universitas Indonesia, Depok',
                'latitude'      => -6.3612000,
                'longitude'     => 106.8310000,
                'radius_meters' => 300,
            ],
            [
                'name'          => 'Halte TransJakarta Blok M',
                'address'       => 'Jl. Sultan Hasanuddin, Blok M',
                'latitude'      => -6.2444000,
                'longitude'     => 106.7981000,
                'radius_meters' => 100,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}

