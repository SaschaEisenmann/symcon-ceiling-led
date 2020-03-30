<?php

require_once('IMode.php');
require_once('./../Utils/ColorUtils.php');

class ColorChangeMode implements IMode
{
    public function Start(ILedAdapter $ledAdapter)
    {
        $ledAdapter->StartLooping(100);
    }

    public function Trigger(ILedAdapter $ledAdapter)
    {
        $state = $ledAdapter->LoadState();
        $hue = property_exists($state, "hue") ? $state->hue : 1;

        $color = ColorUtils::ConvertHslToRgb($hue, 100, 100);
        $ledAdapter->SetColorBatch($color['red'], $color['green'], $color['blue']);

        $hue += 2;
        if($hue > 359) {
            $hue = 1;
        }

        $ledAdapter->SaveState(array(
            'hue' => $hue
        ));
    }
}