<?php

interface iAdapter
{
    public function SetBatch($red, $green, $blue);

    public function SetColor($colors);

    public function Schedule($interval, $function);
}