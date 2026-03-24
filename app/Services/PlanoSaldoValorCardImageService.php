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
    private const CREDIT_MAX_WIDTH = 760;
    private const DATE_MAX_WIDTH = 260;
    private const AMOUNT_MAX_WIDTH = 620;

    public function generate(PlanoSaldoValor $record): string
    {
        $image = $this->createBaseCanvas();
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $white = imagecolorallocate($image, 255, 255, 255);
        $shadow = imagecolorallocatealpha($image, 8, 25, 63, 45);

        $credito = trim((string) $record->obligacion);
        $fechaPago = $this->formatFechaPago($record->fecha_vigencia);
        $valorCuotaFuente = $record->valor_cuota ?? $record->valor_reportar ?? 0;
        $valorCuota = '$' . number_format((float) $valorCuotaFuente, 0, ',', '.');

        $fontRegular = $this->resolveFontPath(false);
        $fontBold = $this->resolveFontPath(true) ?? $fontRegular;

        $this->drawText(
            $image,
            $credito !== '' ? $credito : 'N/A',
            74,
            448,
            162,
            $white,
            $fontBold,
            self::CREDIT_MAX_WIDTH,
            $shadow,
            4,
        );

        $this->drawText(
            $image,
            $fechaPago,
            48,
            640,
            254,
            $white,
            $fontRegular ?? $fontBold,
            self::DATE_MAX_WIDTH,
            $shadow,
            3,
        );

        $this->drawText(
            $image,
            $valorCuota,
            78,
            500,
            382,
            $white,
            $fontBold,
            self::AMOUNT_MAX_WIDTH,
            $shadow,
            4,
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
                imagettftext($image, $fitSize, 0, $x + $shadowOffset, $y + $shadowOffset, $shadowColor, $fontPath, $text);
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

    private function fitTextSize(string $text, int $preferredSize, string $fontPath, ?int $maxWidth): int
    {
        if ($maxWidth === null || $maxWidth <= 0) {
            return $preferredSize;
        }

        $size = $preferredSize;

        while ($size > 20) {
            $box = imagettfbbox($size, 0, $fontPath, $text);

            if ($box === false) {
                return $preferredSize;
            }

            $width = (int) abs($box[2] - $box[0]);

            if ($width <= $maxWidth) {
                return $size;
            }

            $size -= 2;
        }

        return max(20, $size);
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
