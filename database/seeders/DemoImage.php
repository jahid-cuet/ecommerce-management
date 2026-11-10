<?php

namespace Database\Seeders;

use App\Support\Thumbnail;
use Illuminate\Support\Facades\Storage;

/**
 * Generates simple SVG thumbnails for demo data, so seeding needs no image files or PHP image extensions.
 */
class DemoImage
{
    /**
     * Writes the image to the public disk and returns its path.
     */
    public static function store(string $path, string $title, string $color, ?string $subtitle = null): string
    {
        $initials = collect(explode(' ', $title))
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        $title = htmlspecialchars($title, ENT_XML1);
        $subtitleText = $subtitle === null ? '' : sprintf(
            '<text x="300" y="530" font-size="26" fill="#ffffff" fill-opacity="0.85" text-anchor="middle">%s</text>',
            htmlspecialchars($subtitle, ENT_XML1),
        );

        $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600" font-family="Helvetica, Arial, sans-serif">
              <defs>
                <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0" stop-color="{$color}"/>
                  <stop offset="1" stop-color="{$color}" stop-opacity="0.65"/>
                </linearGradient>
              </defs>
              <rect width="600" height="600" fill="url(#bg)"/>
              <circle cx="300" cy="260" r="130" fill="#ffffff" fill-opacity="0.18"/>
              <text x="300" y="300" font-size="110" font-weight="700" fill="#ffffff" text-anchor="middle">{$initials}</text>
              <text x="300" y="480" font-size="36" font-weight="600" fill="#ffffff" text-anchor="middle">{$title}</text>
              {$subtitleText}
            </svg>
            SVG;

        Storage::disk(Thumbnail::DISK)->put($path, $svg);

        return $path;
    }
}
