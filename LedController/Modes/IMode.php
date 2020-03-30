<?php


interface IMode
{
    public function Start(ILedAdapter $ledAdapter);

    public function Trigger(ILedAdapter $ledAdapter);
}