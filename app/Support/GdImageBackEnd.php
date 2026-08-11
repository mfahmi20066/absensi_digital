<?php

namespace App\Support;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Curve;
use BaconQrCode\Renderer\Path\EllipticArc;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;

/**
 * Backend render QR ke PNG memakai ekstensi GD (pengganti ImagickImageBackEnd).
 */
class GdImageBackEnd implements ImageBackEndInterface
{
    /** @var \GdImage|null */
    private $image;

    /** @var array<int, array{float,float,float,float,float,float}> */
    private $matrixStack = [];

    public function new(int $size, ColorInterface $backgroundColor): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('You need to install the GD extension to use this back end');
        }

        $this->image = imagecreatetruecolor($size, $size);
        $rgb = $backgroundColor->toRgb();
        $bg = imagecolorallocate($this->image, $rgb->getRed(), $rgb->getGreen(), $rgb->getBlue());
        imagefill($this->image, 0, 0, $bg);
        $this->matrixStack = [[1.0, 0.0, 0.0, 1.0, 0.0, 0.0]];
    }

    public function scale(float $size): void
    {
        $m = $this->current();
        $this->setCurrent([$m[0] * $size, $m[1] * $size, $m[2] * $size, $m[3] * $size, $m[4], $m[5]]);
    }

    public function translate(float $x, float $y): void
    {
        $m = $this->current();
        $this->setCurrent([$m[0], $m[1], $m[2], $m[3], $m[4] + $m[0] * $x + $m[2] * $y, $m[5] + $m[1] * $x + $m[3] * $y]);
    }

    public function rotate(int $degrees): void
    {
        $rad = deg2rad($degrees);
        $cos = cos($rad);
        $sin = sin($rad);
        $m = $this->current();
        $this->setCurrent([
            $m[0] * $cos + $m[2] * $sin,
            $m[1] * $cos + $m[3] * $sin,
            -$m[0] * $sin + $m[2] * $cos,
            -$m[1] * $sin + $m[3] * $cos,
            $m[4],
            $m[5],
        ]);
    }

    public function push(): void
    {
        $this->matrixStack[] = $this->current();
    }

    public function pop(): void
    {
        if (count($this->matrixStack) <= 1) {
            throw new RuntimeException('Cannot pop the last matrix');
        }
        array_pop($this->matrixStack);
    }

    public function drawPathWithColor(Path $path, ColorInterface $color): void
    {
        $this->assertStarted();
        $rgb = $color->toRgb();
        $fill = imagecolorallocate($this->image, $rgb->getRed(), $rgb->getGreen(), $rgb->getBlue());
        $this->drawPath($path, $fill);
    }

    public function drawPathWithGradient(Path $path, Gradient $gradient, float $x, float $y, float $width, float $height): void
    {
        $start = $gradient->getStartColor()->toRgb();
        $this->assertStarted();
        $fill = imagecolorallocate($this->image, $start->getRed(), $start->getGreen(), $start->getBlue());
        $this->drawPath($path, $fill);
    }

    public function done(): string
    {
        $this->assertStarted();
        ob_start();
        imagepng($this->image);
        $blob = (string) ob_get_clean();
        imagedestroy($this->image);
        $this->image = null;
        $this->matrixStack = [];

        return $blob;
    }

    private function drawPath(Path $path, int $color): void
    {
        $points = [];
        $current = null;
        $start = null;

        foreach ($path as $op) {
            switch (true) {
                case $op instanceof Move:
                    $this->flushPolygon($points, $color);
                    $points = [];
                    $start = [$op->getX(), $op->getY()];
                    $points[] = $this->transform($op->getX(), $op->getY());
                    $current = $start;
                    break;

                case $op instanceof Line:
                    $points[] = $this->transform($op->getX(), $op->getY());
                    $current = [$op->getX(), $op->getY()];
                    break;

                case $op instanceof Close:
                    if ($start) {
                        $points[] = $this->transform($start[0], $start[1]);
                    }
                    $this->flushPolygon($points, $color);
                    $points = [];
                    $current = null;
                    break;

                default:
                    // Kurva/arc tidak dipakai (module kotak); abaikan agar tidak error.
                    if ($current) {
                        $points[] = $this->transform($current[0], $current[1]);
                    }
                    break;
            }
        }

        $this->flushPolygon($points, $color);
    }

    private function flushPolygon(array $points, int $color): void
    {
        if (count($points) < 3) {
            return;
        }

        $flat = [];
        foreach ($points as [$x, $y]) {
            $flat[] = (int) round($x);
            $flat[] = (int) round($y);
        }

        imagefilledpolygon($this->image, $flat, $color);
    }

    /** @return array{float,float,float,float,float,float} */
    private function current(): array
    {
        return end($this->matrixStack);
    }

    /** @param array{float,float,float,float,float,float} $matrix */
    private function setCurrent(array $matrix): void
    {
        $key = array_key_last($this->matrixStack);
        $this->matrixStack[$key] = $matrix;
    }

    /** @return array{float,float} */
    private function transform(float $x, float $y): array
    {
        $m = $this->current();

        return [
            $m[0] * $x + $m[2] * $y + $m[4],
            $m[1] * $x + $m[3] * $y + $m[5],
        ];
    }

    private function assertStarted(): void
    {
        if (null === $this->image) {
            throw new RuntimeException('No image has been started');
        }
    }
}
