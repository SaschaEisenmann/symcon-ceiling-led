<?php

require_once('iMode.php');

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
        $this->adapter->SetBatch(0, 0, 0);
    }

    public function stop()
    {
    }
}