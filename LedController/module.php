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
        IPS_LogMessage("LedController", $text);

        $response = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $text)));

        IPS_LogMessage("LedController", $response);

    }

    public function ReceiveData($JSONString)
    {
        IPS_LogMessage("LedController", $JSONString);
    }

    public function Enable()
    {
        $this->ForwardData("COMMAND_EXECUTE_SETBATCH\n");

        $bytes = array(255, 255, 255);
        $string = implode(array_map("chr", $bytes));

        $this->ForwardData($string);
    }

    public function Disable()
    {
        $this->ForwardData("COMMAND_EXECUTE_SETBATCH\n");


        $bytes = array(0, 0, 0);
        $string = implode(array_map("chr", $bytes));

        $this->ForwardData($string);
    }
}
