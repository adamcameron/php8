<?php

namespace adamcameron\php8\tests\Functional\Image;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Tests PHP image functionality")]
class ImageTest extends TestCase
{
    #[TestDox("It can flip and image with a single statement")]
    public function testImage()
    {
        $fixturesPath = __DIR__ . "/../../Fixtures/Images";
        try {
            $sourceImagePath = imagecreatefrompng($fixturesPath . "/test.png");
            $destinationImagePath = $fixturesPath . "/test-flipped.png";

            $this->assertInstanceOf("GdImage", $sourceImagePath);

            imageflip($sourceImagePath, IMG_FLIP_VERTICAL);

            $this->assertTrue(imagepng($sourceImagePath, $destinationImagePath));
        } finally {
            unlink($destinationImagePath);
        }
    }
}
