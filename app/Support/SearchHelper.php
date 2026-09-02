<?php

namespace App\Support;

class SearchHelper
{
    /**
     * Escape LIKE wildcard characters (% and _) to prevent unintended matches.
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }
}
