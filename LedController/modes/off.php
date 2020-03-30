<?php


class off implements iMode
{
    private iAdapter $adapter;

    public function getName()
    {
        return "off";
    }

    public function initialize(iAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function start(array $parameters)
    {
        $this->adapter->SetColor(0, 0, 0);
    }

    public function stop()
    {
    }
}