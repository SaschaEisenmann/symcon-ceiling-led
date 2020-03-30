<?php

define("MODES", array(
    new off()
));

class LedController extends IPSModule implements iAdapter
{
    private array $modes;
    private iMode $activeMode;

    public function __construct($instanceId)
    {
        parent::__construct($instanceId);

        $this->modes = [
            new off()
        ];

        foreach (MODES as $mode) {
            $mode->initialize($this);
        }
    }

    public function Create()
    {
        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");
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
        }

        $this->activeMode = $mode;
        $this->activeMode->start($parameters);
    }

    private function FindMode($name) {
        foreach (MODES as $mode) {
            if($mode->GetName() == $name) {
                return $mode;
            }
        }

        throw new Error("Mode not found");
    }

    public function SetColor($red, $green, $blue) {
        $this->ForwardData("COMMAND_EXECUTE_SETBATCH\n");
        IPS_Sleep(1);

        $colorBuffer = array($red, $green, $blue);
        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(5);
    }
}
