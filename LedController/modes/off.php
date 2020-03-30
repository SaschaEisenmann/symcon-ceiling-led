<?php


class off implements iMode
{
    private $adapter;

    public function getName()
    {
        return "off";
    }

    public function initialize($adapter)
    {
        $this->adapter = $adapter;
    }

    public function start($parameters)
    {
        $this->adapter->SetColor(0, 0, 0);
    }

    public function stop()
    {
    }
}