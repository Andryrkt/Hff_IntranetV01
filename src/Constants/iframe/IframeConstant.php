<?php

namespace App\Constants\iframe;

class IframeConstant
{
    public const LINK = [
        "rental-rollout" => "/RentalRollout/dashboard_rental.html",
        "ge-rollout"     => "/RentalRollout/dashboard_energie.html"
    ];

    public static function getLinkIframe(string $slug, string $username): string
    {
        $link = self::LINK[$slug] ?? "";

        return $link ? "{$link}?login={$username}" : "";
    }
}
