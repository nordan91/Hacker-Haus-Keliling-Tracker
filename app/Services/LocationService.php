<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;

class LocationService
{
    /**
     * Find the nearest known location to the given GPS coordinates
     * that falls within its geofence radius.
     *
     * @return Location|null The matched location, or null if none within range.
     */
    public function findNearestLocation(float $latitude, float $longitude): ?Location
    {
        $locations = Location::all();

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = $location->distanceTo($latitude, $longitude);

            if ($distance <= $location->radius_meters && $distance < $minDistance) {
                $nearest = $location;
                $minDistance = $distance;
            }
        }

        return $nearest;
    }
}

