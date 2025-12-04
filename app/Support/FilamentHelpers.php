<?php

namespace App\Support;

class FilamentHelpers
{
    public static function mapsLink(?float $lat, ?float $lng): ?string
    {
        if ($lat === null || $lng === null) return null;
        $q = urlencode("{$lat},{$lng}");
        return "https://maps.google.com/?q={$q}";
    }

    public static function mapsEmbed(?float $lat, ?float $lng, int $zoom = 16, int $height = 260): ?string
    {
        if ($lat === null || $lng === null) return null;
        $q = urlencode("{$lat},{$lng}");
        $src = "https://maps.google.com/maps?q={$q}&z={$zoom}&output=embed";
        return "<iframe src=\"{$src}\" width=\"100%\" height=\"{$height}\" style=\"border:0;border-radius:8px\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>";
    }
}
