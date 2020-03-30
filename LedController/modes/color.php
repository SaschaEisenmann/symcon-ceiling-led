<?php

class color implements iMode
{
    private $adapter;

    public function getName()
    {
        return "color";
    }

    public function initialize($adapter)
    {
        $this->adapter = $adapter;
    }

    public function start(array $parameters)
    {
        $this->adapter->SetColor($parameters[0], $parameters[1], $parameters[2]);
    }

    public function stop()
    {
    }
}