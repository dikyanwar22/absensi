<?php

namespace App\Helpers;

class GeoHelper
{
    /**
     * Hitung jarak Haversine antara 2 titik lat/lng dalam meter
     * app/Helpers/GeoHelper.php:15
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371000; // radius bumi meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    /**
     * Cek apakah lokasi dalam radius geofence
     */
    public static function isWithinRadius(float $lat1, float $lng1, float $lat2, float $lng2, int $radiusMeter): bool
    {
        return self::distance($lat1, $lng1, $lat2, $lng2) <= $radiusMeter;
    }
}
