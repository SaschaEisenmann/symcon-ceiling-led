<?php

interface iMode
{
    public function getName();

    public function initialize(iAdapter $adapter);

    public function start(array $parameters);

    public function stop();
}