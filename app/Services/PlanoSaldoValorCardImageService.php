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

        $white = imagecolorallocate($image, 255, 255, 255);

        $credito = trim((string) $record->obligacion);
        $fechaPago = $this->formatFechaPago($record->fecha_vigencia);
        $valorCuota = '$' . number_format((float) ($record->valor_reportar ?? 0), 0, ',', '.');

        $fontRegular = $this->resolveFontPath(false);
        $fontBold = $this->resolveFontPath(true) ?? $fontRegular;

        $this->drawText(
            $image,
            $credito !== '' ? $credito : 'N/A',
            52,
            450,
            145,
            $white,
            $fontBold,
        );

        $this->drawText(
            $image,
            $fechaPago,
            40,
            640,
            260,
            $white,
            $fontBold,
        );

        $this->drawText(
            $image,
            $valorCuota,
            54,
            500,
            365,
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

    private function drawText(
        $image,
        string $text,
        int $size,
        int $x,
        int $y,
        int $color,
        ?string $fontPath
    ): void {
        if ($fontPath && is_file($fontPath)) {
            imagettftext($image, $size, 0, $x, $y, $color, $fontPath, $text);

            return;
        }

        imagestring($image, 5, $x, max(0, $y - 18), $text, $color);
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
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            ]
            : [
                public_path('fonts/arial.ttf'),
                resource_path('fonts/arial.ttf'),
                'C:\\Windows\\Fonts\\arial.ttf',
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
}
