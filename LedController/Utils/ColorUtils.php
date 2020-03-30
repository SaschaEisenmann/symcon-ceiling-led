<?php


class ColorUtils
{
    public static function ConvertHslToRgb($hue, $saturation, $lightness) {
        if($hue < 0)   $hue = 0;
        if($hue > 360) $hue = 360;

        if($saturation < 0)   $saturation = 0;
        if($saturation > 100) $saturation = 100;

        if($lightness < 0)   $lightness = 0;
        if($lightness > 100) $lightness = 100;

        $saturationFloat = $saturation / 100.0;
        $lightnessFloat = $lightness / 100.0;
        $chromaFloat = $lightnessFloat * $saturationFloat;
        $huePrime = $hue / 60.0;
        $huePrimeTemp = $huePrime;

        while($huePrimeTemp >= 2.0) $huePrimeTemp -= 2.0;
        $dX = $chromaFloat * (1 - abs($huePrimeTemp-1));

        switch(floor($huePrime)) {
            case 0:
                $red = $chromaFloat; $green = $dX; $blue = 0.0; break;
            case 1:
                $red = $dX; $green = $chromaFloat; $blue = 0.0; break;
            case 2:
                $red = 0.0; $green = $chromaFloat; $blue = $dX; break;
            case 3:
                $red = 0.0; $green = $dX; $blue = $chromaFloat; break;
            case 4:
                $red = $dX; $green = 0.0; $blue = $chromaFloat; break;
            case 5:
                $red = $chromaFloat; $green = 0.0; $blue = $dX; break;
            default:
                $red = 0.0; $green = 0.0; $blue = 0.0; break;
        }

        $dM  = $lightnessFloat - $chromaFloat;
        $red += $dM; $green += $dM; $blue += $dM;
        $red *= 255; $green *= 255; $blue *= 255;

        return array(
            'red' => round($red),
            'green' => round($green),
            'blue' => round($blue)
        );
    }
}