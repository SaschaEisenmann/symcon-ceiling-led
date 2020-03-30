<?php

require_once('IMode.php');

class ColorMode implements IMode
{
    public function Start(ILedAdapter $ledAdapter)
    {
        $ledAdapter->StartLooping(1000);
    }

    public function Trigger(ILedAdapter $ledAdapter)
    {
        $parameters = $ledAdapter->GetParameters();

        $red = $parameters[0];
        $green = $parameters[1];
        $blue = $parameters[2];

        $ledAdapter->SetColorBatch($red, $green, $blue);
    }
}