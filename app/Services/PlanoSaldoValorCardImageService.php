<?php

namespace App\Services;

use App\Models\PlanoSaldoValor;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use RuntimeException;

class PlanoSaldoValorCardImageService
{
    private const FALLBACK_WIDTH = 1667;
    private const FALLBACK_HEIGHT = 833;

    public function generate(PlanoSaldoValor $record): string
    {
        $image = $this->createBaseCanvas();
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $white = imagecolorallocate($image, 255, 255, 255);
        $panelFill = imagecolorallocatealpha($image, 8, 36, 94, 64);
        $panelBorder = imagecolorallocatealpha($image, 196, 236, 255, 92);

        $credito = trim((string) $record->obligacion);
        $fechaPago = $this->formatFechaPago($record->fecha_pago);
        $valorCuotaFuente = $record->valor_cuota ?? $record->valor_reportar ?? 0;
        $valorCuota = '$' . number_format((float) $valorCuotaFuente, 0, ',', '.');

        $fontRegular = $this->resolveFontPath(false);
        $fontBold = $this->resolveFontPath(true) ?? $fontRegular;

        $this->drawPanel($image, 452, 92, 784, 120, $panelFill, $panelBorder);
        $this->drawPanel($image, 644, 212, 360, 74, $panelFill, $panelBorder);
        $this->drawPanel($image, 484, 316, 732, 110, $panelFill, $panelBorder);

        $this->drawTextInBox(
            $image,
            $credito !== '' ? $credito : 'N/A',
            118,
            452,
            92,
            784,
            120,
            $white,
            $fontBold,
        );

        $this->drawTextInBox(
            $image,
            $fechaPago,
            70,
            644,
            212,
            360,
            74,
            $white,
            $fontBold ?? $fontRegular,
        );

        $this->drawTextInBox(
            $image,
            $valorCuota,
            118,
            484,
            316,
            732,
            110,
            $white,
            $fontBold,
        );

        ob_start();
        imagepng($image, null, 9);
        $binary = ob_get_clean();
        imagedestroy($image);

        if (!is_string($binary) || $binary === '') {
            throw new RuntimeException('No se pudo generar la imagen de la tarjeta.');
        }

        return $binary;
    }

    private function createBaseCanvas()
    {
        $templatePath = public_path('images/tarjeta-digital-template.jpg');

        if (is_file($templatePath)) {
            $template = @imagecreatefromjpeg($templatePath);

            if ($template !== false) {
                return $template;
            }
        }

        $image = imagecreatetruecolor(self::FALLBACK_WIDTH, self::FALLBACK_HEIGHT);

        for ($x = 0; $x < self::FALLBACK_WIDTH; $x++) {
            $ratio = $x / self::FALLBACK_WIDTH;
            $red = 12;
            $green = (int) round(24 + (144 * $ratio));
            $blue = (int) round(170 + (70 * $ratio));

            $color = imagecolorallocate($image, $red, $green, $blue);
            imageline($image, $x, 0, $x, self::FALLBACK_HEIGHT, $color);
        }

        return $image;
    }

    private function drawPanel($image, int $x, int $y, int $width, int $height, int $fillColor, ?int $borderColor = null): void
    {
        imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $fillColor);

        if ($borderColor !== null) {
            imagerectangle($image, $x, $y, $x + $width, $y + $height, $borderColor);
        }
    }

    private function drawTextInBox(
        $image,
        string $text,
        int $size,
        int $x,
        int $y,
        int $width,
        int $height,
        int $color,
        ?string $fontPath,
        ?int $shadowColor = null,
        int $strokeSize = 0
    ): void {
        $text = trim($text);

        if ($text === '') {
            return;
        }

        if ($fontPath && is_file($fontPath) && function_exists('imagettftext')) {
            $paddingX = 14;
            $paddingY = 6;
            $fitSize = $this->fitTextSize(
                $text,
                $size,
                $fontPath,
                max(1, $width - ($paddingX * 2)),
                max(1, $height - ($paddingY * 2)),
            );
            [, $textHeight, $minX, $minY] = $this->measureTtfText($text, $fitSize, $fontPath);

            $drawX = $x + $paddingX - $minX;
            $drawY = $y + (int) round(($height - $textHeight) / 2) - $minY;

            $this->drawText(
                $image,
                $text,
                $fitSize,
                $drawX,
                $drawY,
                $color,
                $fontPath,
                $width,
                $shadowColor,
                $strokeSize,
            );

            return;
        }

        $this->drawText(
            $image,
            $text,
            $size,
            $x + 16,
            $y + $height - 8,
            $color,
            $fontPath,
            $width,
            $shadowColor,
            $strokeSize,
        );
    }

    private function drawText(
        $image,
        string $text,
        int $size,
        int $x,
        int $y,
        int $color,
        ?string $fontPath,
        ?int $maxWidth = null,
        ?int $shadowColor = null,
        int $shadowOffset = 0
    ): void {
        $text = trim($text);

        if ($text === '') {
            return;
        }

        if ($fontPath && is_file($fontPath) && function_exists('imagettftext')) {
            $fitSize = $this->fitTextSize($text, $size, $fontPath, $maxWidth);

            if ($shadowColor !== null && $shadowOffset > 0) {
                for ($offsetX = -$shadowOffset; $offsetX <= $shadowOffset; $offsetX++) {
                    for ($offsetY = -$shadowOffset; $offsetY <= $shadowOffset; $offsetY++) {
                        if ($offsetX === 0 && $offsetY === 0) {
                            continue;
                        }

                        imagettftext($image, $fitSize, 0, $x + $offsetX, $y + $offsetY, $shadowColor, $fontPath, $text);
                    }
                }
            }

            imagettftext($image, $fitSize, 0, $x, $y, $color, $fontPath, $text);

            return;
        }

        $this->drawScaledBitmapText(
            $image,
            $text,
            $size,
            $x,
            $y,
            $color,
            $maxWidth,
            $shadowColor,
            $shadowOffset,
        );
    }

    private function formatFechaPago(mixed $fechaPago): string
    {
        if (empty($fechaPago)) {
            return 'NO DEFINIDA';
        }

        if ($fechaPago instanceof CarbonInterface) {
            return $fechaPago->format('d/m/Y');
        }

        try {
            return Carbon::parse($fechaPago)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $fechaPago;
        }
    }

    private function resolveFontPath(bool $bold): ?string
    {
        $candidates = $bold
            ? [
                public_path('fonts/arialbd.ttf'),
                resource_path('fonts/arialbd.ttf'),
                'C:\\Windows\\Fonts\\arialbd.ttf',
                'C:\\Windows\\Fonts\\segoeuib.ttf',
                'C:\\Windows\\Fonts\\calibrib.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            ]
            : [
                public_path('fonts/arial.ttf'),
                resource_path('fonts/arial.ttf'),
                'C:\\Windows\\Fonts\\arial.ttf',
                'C:\\Windows\\Fonts\\segoeui.ttf',
                'C:\\Windows\\Fonts\\calibri.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/dejavu/DejaVuSans.ttf',
            ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function fitTextSize(
        string $text,
        int $preferredSize,
        string $fontPath,
        ?int $maxWidth,
        ?int $maxHeight = null
    ): int
    {
        if (($maxWidth === null || $maxWidth <= 0) && ($maxHeight === null || $maxHeight <= 0)) {
            return $preferredSize;
        }

        $size = $preferredSize;

        while ($size > 20) {
            [$width, $height] = $this->measureTtfText($text, $size, $fontPath);
            $fitsWidth = $maxWidth === null || $maxWidth <= 0 || $width <= $maxWidth;
            $fitsHeight = $maxHeight === null || $maxHeight <= 0 || $height <= $maxHeight;

            if ($fitsWidth && $fitsHeight) {
                return $size;
            }

            $size -= 2;
        }

        return max(20, $size);
    }

    private function measureTtfText(string $text, int $size, string $fontPath): array
    {
        $box = imagettfbbox($size, 0, $fontPath, $text);

        if ($box === false) {
            return [0, 0, 0, 0];
        }

        $xs = [$box[0], $box[2], $box[4], $box[6]];
        $ys = [$box[1], $box[3], $box[5], $box[7]];
        $minX = min($xs);
        $maxX = max($xs);
        $minY = min($ys);
        $maxY = max($ys);

        return [
            (int) ($maxX - $minX),
            (int) ($maxY - $minY),
            (int) $minX,
            (int) $minY,
        ];
    }

    private function drawScaledBitmapText(
        $image,
        string $text,
        int $size,
        int $x,
        int $y,
        int $color,
        ?int $maxWidth = null,
        ?int $shadowColor = null,
        int $shadowOffset = 0
    ): void {
        $font = 5;
        $baseWidth = max(1, imagefontwidth($font) * strlen($text));
        $baseHeight = imagefontheight($font);
        $scale = max(1, (int) round($size / max(1, $baseHeight)));

        if ($maxWidth !== null && $maxWidth > 0) {
            $scale = min($scale, max(1, (int) floor($maxWidth / $baseWidth)));
        }

        $tmpWidth = $baseWidth + ($shadowColor !== null ? $shadowOffset : 0) + 2;
        $tmpHeight = $baseHeight + ($shadowColor !== null ? $shadowOffset : 0) + 2;
        $tmp = imagecreatetruecolor($tmpWidth, $tmpHeight);

        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);

        $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
        imagefill($tmp, 0, 0, $transparent);

        if ($shadowColor !== null && $shadowOffset > 0) {
            imagestring($tmp, $font, $shadowOffset, 1, $text, $shadowColor);
        }

        imagestring($tmp, $font, 0, 0, $text, $color);

        imagecopyresampled(
            $image,
            $tmp,
            $x,
            max(0, $y - ($baseHeight * $scale)),
            0,
            0,
            $tmpWidth * $scale,
            $tmpHeight * $scale,
            $tmpWidth,
            $tmpHeight
        );

        imagedestroy($tmp);
    }
}
