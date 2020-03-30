<?php

require_once('modes/off.php');
require_once('modes/color.php');
require_once('modes/colorChange.php');
require_once('iAdapter.php');

class LedController extends IPSModule implements iAdapter
{
    public function Create()
    {
        parent::Create();

        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");

        $this->RegisterPropertyString('Mode', 'OFF');
        $this->RegisterPropertyString('PARAMETERS', json_encode([]));
        $this->RegisterTimer('Schedule', 0, 'LEDC_Interval($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->RegisterVariableInteger('MODE', 'Mode', "", 0);
        $this->RegisterVariableString('PARAMETERS', 'Parameters', "", 1);
        // 0 -> Off
        // 1 -> Color
        // 2 -> ColorChange
    }

    public function SetMode($mode, $parameters)
    {
        if($parameters == null) {
            $parameters = [];
        }

        SetValueInteger($this->GetIDForIdent("MODE"), $mode);
        SetValueString($this->GetIDForIdent("PARAMETERS"), json_encode($parameters));

        $this->switchMode();
    }

    private function switchMode() {
        $mode = GetValueInteger($this->GetIDForIdent("MODE"));
        $parameters = json_decode(GetValueString($this->GetIDForIdent("PARAMETERS")));

        switch ($mode) {
            case 1:
                $this->switchToModeColor($parameters);
                return;
            default:
                $this->switchToModeOff($parameters);
                return;
        }
    }

    private function switchToModeOff($parameters) {
        $this->SetBatch(0, 0, 0);
    }

    private function switchToModeColor($parameters) {
        $this->SetBatch($parameters[0], $parameters[1], $parameters[2]);
    }







    private function FindMode($name) {
        foreach ($this->modes as $mode) {
            if($mode->GetName() == $name) {
                return $mode;
            }
        }

        throw new Error("Mode not found");
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
        IPS_LogMessage("LedController", "Schedule Trigger: ");
        $this->scheduleFunction = $function;
        $this->SetTimerInterval('Schedule', $interval);
    }

    public function Interval() {
        if($this->scheduleFunction instanceof Closure) {
            IPS_LogMessage("LedController", "Intervall Trigger: ");
            $this->scheduleFunction();
        }
    }
}
