<?php

namespace App\Service\Helper;

class RandomColorGenerator
{
    public static function generateRandomColor(): string
    {
        return sprintf('#%06X', \mt_rand(0, 0xFFFFFF));
    }
}