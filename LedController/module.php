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

    public function ForwardData($JSONString)
    {
        IPS_LogMessage("LedController", $JSONString);
    }

    public function ReceiveData($JSONString)
    {
        IPS_LogMessage("LedController", $JSONString);
    }

    public function Enable()
    {
    }

    public function Disable()
    {
    }
}
