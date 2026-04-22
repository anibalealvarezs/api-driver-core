<?php

namespace Anibalealvarezs\ApiDriverCore\Helpers;

class FieldsNormalizerHelper
{
    public static function getCleanString(string $string): string {
        return trim($string);
    }

    public static function getCleanArray(array $array): array {
        return $array;
    }
}