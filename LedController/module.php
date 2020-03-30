<?php
class LedController extends IPSModule
{
    public function Create()
    {
        parent::Create();

        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    public function ForwardData($text)
    {
        IPS_LogMessage("LedController", "Sending Command: " . $text);

        $response = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $text)));
        if($response === false || is_null($response)){
            $jsonError = json_last_error();
            IPS_LogMessage("LedController", "Received Error: " . $jsonError);
        }



        IPS_LogMessage("LedController", "Received Response: " . $response);

    }

    public function ReceiveData($JSONString)
    {
        IPS_LogMessage("LedController", $JSONString);
    }

    public function Enable()
    {
        SetColor(255, 255, 255);
    }

    public function Disable()
    {
        SetColor(0, 0, 0);
    }

    public function Reset()
    {
        $this->ForwardData("RESET\n");
        IPS_Sleep(1);
    }

    public function SetColor($red, $green, $blue) {
        $this->ForwardData("COMMAND_EXECUTE_SETBATCH\n");
        IPS_Sleep(1);

        $colorBuffer = array($red, $green, $blue);
        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(5);
    }
}
