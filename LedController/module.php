<?php

require_once('modes/off.php');
require_once('modes/color.php');
require_once('modes/colorChange.php');
require_once('iAdapter.php');

class LedController extends IPSModule implements iAdapter
{
    /** @var array +/ */
    private $modes;

    /** @var iMode +/ */
    private $activeMode;

    public function __construct($instanceId)
    {
        parent::__construct($instanceId);

        $this->modes = [
            new off(),
            new color(),
            new colorChange()
        ];

        foreach ($this->modes as $mode) {
            $mode->initialize($this);
        }
    }

    public function Create()
    {
        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");

        $this->RegisterTimer('Schedule', 0, 'LEDC_Interval($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    public function ForwardData($text)
    {
        IPS_LogMessage("LedController", "Sending Command: " . $text);

        $json = json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => utf8_encode($text)));
        IPS_LogMessage("LedController", "Sending JSON: " . $json);
        if($json === false || is_null($json)){
            $jsonError = json_last_error();
            IPS_LogMessage("LedController", "Received Error: " . $jsonError);
        }


        $response = $this->SendDataToParent($json);

        IPS_LogMessage("LedController", "Received Response: " . $response);

    }

    public function ReceiveData($JSONString)
    {
        IPS_LogMessage("LedController", $JSONString);
    }

    public function Enable()
    {
        $this->SetColor(255, 255, 255);
    }

    public function Disable()
    {
        $this->SetColor(0, 0, 0);
    }

    public function Reset()
    {
        $this->ForwardData("RESET\n");
        IPS_Sleep(1);
    }

    public function SetMode($name, $parameters) {
        $mode = $this->FindMode($name);

        if($this->activeMode) {
            $this->activeMode->stop();
            $this->activeMode = null;
            $this->SetTimerInterval('Schedule', 0);
            $this->scheduleFunction = null;
        }

        $this->activeMode = $mode;
        $this->activeMode->start($parameters);
    }

    private function FindMode($name) {
        foreach ($this->modes as $mode) {
            if($mode->GetName() == $name) {
                return $mode;
            }
        }

        throw new Error("Mode not found");
    }

    public function SetBatch($red, $green, $blue) {
        $this->ForwardData("COMMAND_EXECUTE_SETBATCH\n");
        IPS_Sleep(1);

        $colorBuffer = array($blue, $green, $red);
        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(5);
    }

    public function SetColor($colors) {
        $colorBuffer = [];
        foreach ($colors as $c) {
            $buffer[] = $c['blue'];
            $buffer[] = $c['green'];
            $buffer[] = $c['red'];
        }

        $this->ForwardData("COMMAND_EXECUTE_SETALL\n");
        IPS_Sleep(1);

        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(35);
    }

    private $scheduleFunction;
    public function Schedule($interval, $function)
    {
        $this->scheduleFunction = $function;
        $this->SetTimerInterval('Schedule', $interval);
    }

    public function Interval() {
        if($this->scheduleFunction) {
            $this->scheduleFunction();
        }
    }
}
