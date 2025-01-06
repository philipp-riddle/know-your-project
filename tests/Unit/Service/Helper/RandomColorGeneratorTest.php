<?php

namespace App\Tests\Unit\Service\Helper;

use App\Service\Helper\RandomColorGenerator;
use PHPUnit\Framework\TestCase;

class RandomColorGeneratorTest extends TestCase
{
    public function testGenerateRandomColor()
    {
        $randomColor = RandomColorGenerator::generateRandomColor();

        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $randomColor);
        $this->assertTrue(\strlen($randomColor) === 7, 'Random generated color should be 7 characters long');
    }
}