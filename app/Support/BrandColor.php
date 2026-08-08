<?php

namespace App\Support;

final class BrandColor
{
    public const DEFAULT_HEX = '#3D4FEB';

    /**
     * @return list<string>
     */
    public static function presets(): array
    {
        return [
            self::DEFAULT_HEX,
            '#0F766E',
            '#B45309',
            '#BE123C',
            '#DB2777',
            '#7C3AED',
            '#0369A1',
            '#15803D',
            '#374151',
        ];
    }

    public static function normalize(?string $hex): ?string
    {
        if ($hex === null || trim($hex) === '') {
            return null;
        }

        $hex = strtoupper(ltrim(trim($hex), '#'));

        if (! preg_match('/^[0-9A-F]{6}$/', $hex)) {
            return null;
        }

        return '#'.$hex;
    }

    public static function isValid(?string $hex): bool
    {
        return self::normalize($hex) !== null;
    }

    /**
     * Full CRM palette as CSS custom-property values for hsl(var(--token)).
     *
     * @return array<string, string>|null
     */
    public static function tokens(?string $hex): ?array
    {
        $normalized = self::normalize($hex);

        if ($normalized === null) {
            return null;
        }

        [$h, $s, $l] = self::hexToHsl($normalized);

        // Keep greys readable; clamp saturation for neutrals derived from brand hue.
        $neutralS = (int) max(8, min(28, round($s * 0.35)));
        $sidebarS = (int) max(18, min(40, round($s * 0.55)));

        return [
            'primary' => self::hsl($h, $s, $l),
            'primary_deep' => self::hsl($h, min(82, $s + 4), max(28, $l - 14)),
            'ring' => self::hsl($h, $s, $l),
            'accent' => self::hsl($h, min(90, $s + 6), min(96, max(92, $l + 42))),
            'accent_foreground' => self::hsl($h, min(78, $s), max(32, $l - 10)),

            'background' => self::hsl($h, $neutralS, 97),
            'secondary' => self::hsl($h, $neutralS, 94),
            'secondary_foreground' => self::hsl($h, min(30, $s), 12),
            'muted' => self::hsl($h, $neutralS, 94),
            'muted_foreground' => self::hsl($h, max(8, (int) round($neutralS * 0.7)), 42),
            'border' => self::hsl($h, $neutralS, 89),
            'input' => self::hsl($h, $neutralS, 86),

            'sidebar_background' => self::hsl($h, $sidebarS, 9),
            'sidebar_foreground' => self::hsl($h, max(12, (int) round($sidebarS * 0.55)), 72),
            'sidebar_border' => self::hsl($h, $sidebarS, 16),
            'sidebar_accent' => self::hsl($h, $sidebarS, 14),
            'sidebar_accent_foreground' => '0 0% 100%',
        ];
    }

    private static function hsl(int $h, int $s, int $l): string
    {
        return sprintf('%d %d%% %d%%', $h, max(0, min(100, $s)), max(0, min(100, $l)));
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;
        $l = ($max + $min) / 2;

        if ($delta < 0.00001) {
            return [0, 0, (int) round($l * 100)];
        }

        $s = $delta / (1 - abs(2 * $l - 1));

        if ($max === $r) {
            $h = fmod(($g - $b) / $delta, 6);
        } elseif ($max === $g) {
            $h = (($b - $r) / $delta) + 2;
        } else {
            $h = (($r - $g) / $delta) + 4;
        }

        $h = (int) round($h * 60);
        if ($h < 0) {
            $h += 360;
        }

        return [$h, (int) round($s * 100), (int) round($l * 100)];
    }
}
