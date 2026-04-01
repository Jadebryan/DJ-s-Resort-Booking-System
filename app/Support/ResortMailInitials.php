<?php

namespace App\Support;

final class ResortMailInitials
{
    /**
     * Two-letter style initials from a resort / business name (for HTML email badge).
     */
    public static function from(?string $resortName): string
    {
        $name = trim((string) $resortName);
        if ($name === '') {
            return '';
        }

        $clean = preg_replace('/[^\pL\pN\s\'-]+/u', ' ', $name);
        $parts = preg_split('/\s+/u', (string) $clean, -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) >= 2) {
            $a = mb_substr($parts[0], 0, 1);
            $b = mb_substr($parts[1], 0, 1);

            return mb_strtoupper($a.$b, 'UTF-8');
        }

        return mb_strtoupper(mb_substr($name, 0, min(2, mb_strlen($name))), 'UTF-8');
    }
}
