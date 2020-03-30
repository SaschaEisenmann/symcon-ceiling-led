<?php

require_once('ILedAdapter.php');
require_once('Modes/OffMode.php');
require_once('Modes/ColorMode.php');
require_once('Modes/ColorChangeMode.php');
require_once('Modes/RainbowMode.php');

define('TIMER_NAME', 'SCHEDULE');

define('PARAMETERS', 'PARAMETERS');
define('STATE', 'STATE');
define('MODE', 'MODE');
define('MODE_CHANGE', 'MODE_CHANGE');

class LedController extends IPSModule implements ILedAdapter
{
    public function Create()
    {
        parent::Create();

        // The parent Serial Port instance
        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");

        // Timer for usage by modes
        $this->RegisterTimer(TIMER_NAME, 0, 'LEDC_Trigger($_IPS[\'TARGET\']);');

        // Register attributes for internal usage
        $this->RegisterAttributeString(PARAMETERS, '',);
        $this->RegisterAttributeString(STATE, '');
        $this->RegisterAttributeBoolean(MODE_CHANGE, false);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        // Register a variable for the active mode
        $this->RegisterVariableInteger(MODE, 'Mode', "", 0);

        if ($this->HasActiveParent()) {
            $this->Reset();
        }
    }

    /**
     * Changes the current mode of the LEDs.
     * @param int $modeId The id of the mode to select
     * @param int $parameters An array of parameters for the mode
     */
    public function SetMode($modeId, $parameters)
    {
        $this->WriteAttributeBoolean(MODE_CHANGE, true);
        $this->StartLooping(0);
        IPS_Sleep(500);

        $this->WriteAttributeString(PARAMETERS, json_encode($parameters ? $parameters : []));
        $this->SaveState(array('EMPTY' => 'EMPTY'));
        SetValueInteger($this->GetIDForIdent("MODE"), $modeId);

        $mode = $this->FindMode($modeId);
        $mode->Start($this);


        $this->WriteAttributeBoolean(MODE_CHANGE, false);
    }

    /**
     * Resets the connected LED Board
     */
    public function Reset() {
        $this->ForwardData("RESET\n");
        IPS_Sleep(5);

        $this->SetMode(0, null);
    }

    /**
     * Will be called from the timer 'SCHEDULE' and calls the Trigger method of the current mode.
     */
    public function Trigger()
    {
        if(!$this->ReadAttributeBoolean(MODE_CHANGE)) {
            $modeId = GetValueInteger($this->GetIDForIdent("MODE"));
            $mode = $this->FindMode($modeId);

            $mode->Trigger($this);
        }
    }

    private function FindMode(int $mode)
    {
        switch ($mode) {
            case 1: return new ColorMode();
            case 2: return new ColorChangeMode();
            case 3: return new RainbowMode();
            default: return new OffMode();
        }
    }

    /**
     * Configures the timer to trigger the current mode at a given interval
     * @param int $interval The interval duration in milliseconds
     */
    public function StartLooping(int $interval)
    {
        $this->SetTimerInterval(TIMER_NAME, $interval);
    }

    /**
     * Return the parameters for the active mode
     * @return array
     */
    public function GetParameters()
    {
        return json_decode($this->ReadAttributeString(PARAMETERS));
    }

    /**
     * Loads the last saved state of a mode
     * @return array The state
     */
    public function LoadState() {
        return json_decode($this->ReadAttributeString(STATE));
    }

    /**
     * Save the given state
     * @param array $state The state to save
     */
    public function SaveState($state) {
        $this->WriteAttributeString(STATE, json_encode($state));
    }

    /**
     * Changes the color of all connected LEDs at once.
     * @param int $red The red value (0 - 255)
     * @param int $green The green value (0 - 255)
     * @param int $blue The blue value (0 - 255)
     */
    public function SetColorBatch(int $red, int $green, int $blue)
    {
        $colorBuffer = array($blue, $green, $red);

        $this->ForwardData("COMMAND_EXECUTE_SETBATCH\n");
        IPS_Sleep(1);

        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(5);
    }

    /**
     * Changes the color of each connected LEDs individually.
     * @param array $colors An array with a color for each LED.
     */
    public function SetColor($colors)
    {
        $colorBuffer = [];
        foreach ($colors as $c) {
            $colorBuffer[] = $c['blue'];
            $colorBuffer[] = $c['green'];
            $colorBuffer[] = $c['red'];
        }

        $this->ForwardData("COMMAND_EXECUTE_SETALL\n");
        IPS_Sleep(1);

        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(35);
    }

    /**
     * Forwares the given command to the parent Serial Port instance
     * @param string $command The command to send
     */
    public function ForwardData($command)
    {
        $data = json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => utf8_encode($command)));
        $this->SendDataToParent($data);
    }

    /**
     * Reciever for inbound date from the parent Serial Port instance
     * @param mixed $json The date container from the parent Serial Port instance
     */
    public function ReceiveData($json)
    {
        $data = json_decode($json);
        IPS_LogMessage("LedController", "Received Data: " . utf8_decode($data->Buffer));
    }
}
