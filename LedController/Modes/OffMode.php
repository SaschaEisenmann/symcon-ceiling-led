<?php

require_once('IMode.php');

class OffMode implements IMode
{
    public function Start(ILedAdapter $ledAdapter)
    {
        $ledAdapter->StartLooping(500);
    }

    public function Trigger(ILedAdapter $ledAdapter)
    {
        $ledAdapter->SetColorBatch(0, 0, 0);
    }
}