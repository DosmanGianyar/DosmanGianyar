<?php

namespace App\Services;

use App\Models\AttendanceLocation;

class GeofenceService
{
    /**
     * Get the active attendance location for a student's class.
     * Checks for a class-specific override first, then falls back to the school default.
     */
    public static function getLocationForClass(?int $classId): array
    {
        return AttendanceLocation::getForClass($classId);
    }

    /**
     * Check whether coordinates fall within the given location's radius.
     */
    public static function isInsideZone(float $lat, float $lng, array $location): bool
    {
        return self::haversineDistance($location['lat'], $location['lng'], $lat, $lng)
            <= $location['radius'];
    }

    /**
     * Haversine formula — returns distance in meters between two coordinates.
     */
    public static function haversineDistance(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $earthRadius = 6_371_000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
