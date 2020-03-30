<?php


class colorChange implements iMode
{
    private $adapter;

    public function getName()
    {
        return "colorChange";
    }

    public function initialize(iAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function start(array $parameters)
    {
        $this->adapter->Schedule(1, function () {

        });
    }

    public function stop()
    {
    }
}