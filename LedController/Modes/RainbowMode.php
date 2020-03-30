<?php

require_once('IMode.php');
require_once(__DIR__ . '/../Utils/ColorUtils.php');

define('LED_COUNT', 247);
define('SPEED', 3);

class RainbowMode implements IMode
{
    public function Start(ILedAdapter $ledAdapter)
    {
        $ledAdapter->StartLooping(100);
    }

    public function Trigger(ILedAdapter $ledAdapter)
    {
        $ledColors = [];

        $state = $ledAdapter->LoadState();
        $position = property_exists($state, "position") ? $state->step : 0;

        for ($led = 0; $led < LED_COUNT; $led++) {
            $hue = $led + 1 + $position;

            if ($hue >= 359) {
                $hue -= 359;
            }

            if ($hue == 0) {
                $hue = 1;
            }
            $ledColors[$led] = ColorUtils::ConvertHslToRgb($hue, 100, 100);
        }

        $position += SPEED;
        if ($position > 360) {
            $position = 0;
        }

        $ledAdapter->SetColor($ledColors);

        $ledAdapter->SaveState(array(
            'position' => $position
        ));
    }
}