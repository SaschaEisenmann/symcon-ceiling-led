<?php

require_once('IMode.php');

class OffMode implements IMode
{
    public function Start(ILedAdapter $ledAdapter)
    {
        $ledAdapter->StartLooping(1000);
    }

    public function Trigger(ILedAdapter $ledAdapter)
    {
        $ledAdapter->SetColorBatch(0, 0, 0);
    }
}