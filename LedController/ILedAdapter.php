<?php


interface ILedAdapter
{
    public function StartLooping(int $interval);

    public function GetParameters();

    public function SetColor(array $colors);

    public function SetColorBatch(int $red, int $green, int $blue);
}