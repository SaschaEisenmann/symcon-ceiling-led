<?php

class LedController extends IPSModule
{
    public function Create()
    {
        parent::Create();

        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");
        $this->RegisterTimer('SCHEDULE', 0, 'LEDC_TriggerInterval($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->RegisterVariableInteger('MODE', 'Mode', "", 0);
        $this->RegisterVariableString('PARAMETERS', 'Parameters', "", 1);
        $this->RegisterVariableString('STATE', 'State', "", 3);
        // 0 -> Off
        // 1 -> Color
        // 2 -> ColorChange
        // 3 -> Rainbow
    }

    public function SetMode($mode, $parameters)
    {
        if($parameters == null) {
            $parameters = [];
        }

        SetValueInteger($this->GetIDForIdent("MODE"), $mode);
        SetValueString($this->GetIDForIdent("PARAMETERS"), json_encode($parameters));

        $this->TriggerMode($mode, false);
    }

    private function TriggerMode($mode, $isInterval)
    {
        if(!$isInterval) {
            $this->SetTimerInterval('SCHEDULE', 0);
            $this->SaveState(array('_dummy' => ''));
        }

        switch ($mode) {
            case 1:
                $this->ModeColor($isInterval);
                return;
            case 2:
                $this->ModeColorChange($isInterval);
                return;
            case 3:
                $this->ModeRainbow($isInterval);
                return;
            default:
                $this->ModeOff($isInterval);
                return;
        }
    }

    private function ModeColor($isInterval) {
        $parameters = $this->LoadParameters();
        $this->SetBatch($parameters[0], $parameters[1], $parameters[2]);
    }

    private function ModeOff($isInterval) {
        $this->SetBatch(0, 0, 0);
    }

    private function ModeColorChange($isInterval) {

        if(!$isInterval) {
            $this->SetTimerInterval('SCHEDULE', 100);
        } else {
            $hue = $this->LoadState()->hue;

            $color = $this->HslToRgb($hue, 100, 100);
            $this->SetBatch($color['red'], $color['green'], $color['blue']);

            $hue += 2;
            if($hue > 359) {
                $hue = 1;
            }

            $this->SaveState(array(
               'hue' => $hue
            ));
        }
    }

    private function ModeRainbow($isInterval) {

        $ledCount = 247;
        $speed = 3;

        if(!$isInterval) {
            $this->SetTimerInterval('SCHEDULE', 500);
        } else {
            $ledColors = [];
            for ($i = 0; $i < $ledCount; $i++) {
                $ledColors[] = array(
                    'red' => 0,
                    'green' => 0,
                    'blue' => 0
                );
            }

            $state = $this->LoadState();
            $step = property_exists($state, "step") ? $state->step : 0;

            for ($i = 0; $i < $ledCount; $i++) {
                $hue = $i + 1 + $step;

                if($hue >= 359) {
                    $hue -= 359;
                }

                if($hue == 0) {
                    $hue = 1;
                }
                $ledColors[$i] = $this->HslToRgb($hue, 100, 100);
            }

            $step += $speed;
            if($step > 360) {
                $step = 0;
            }


            $this->SetColor($ledColors);

            $this->SaveState(array(
                'step' => $step
            ));
        }
    }

    public function TriggerInterval()
    {
        $mode = GetValueInteger($this->GetIDForIdent("MODE"));
        $this->TriggerMode($mode, true);
    }

    private function LoadParameters() {
        return json_decode(GetValueString($this->GetIDForIdent("PARAMETERS")));
    }

    private function LoadState() {
        return json_decode(GetValueString($this->GetIDForIdent("STATE")));
    }

    private function SaveState($state) {
        SetValueString($this->GetIDForIdent("STATE"), json_encode($state));
    }

    private function HslToRgb($iH, $iS, $iV) {

        if($iH < 0)   $iH = 0;   // Hue:
        if($iH > 360) $iH = 360; //   0-360
        if($iS < 0)   $iS = 0;   // Saturation:
        if($iS > 100) $iS = 100; //   0-100
        if($iV < 0)   $iV = 0;   // Lightness:
        if($iV > 100) $iV = 100; //   0-100

        $dS = $iS/100.0; // Saturation: 0.0-1.0
        $dV = $iV/100.0; // Lightness:  0.0-1.0
        $dC = $dV*$dS;   // Chroma:     0.0-1.0
        $dH = $iH/60.0;  // H-Prime:    0.0-6.0
        $dT = $dH;       // Temp variable

        while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
        $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link

        switch(floor($dH)) {
            case 0:
                $dR = $dC; $dG = $dX; $dB = 0.0; break;
            case 1:
                $dR = $dX; $dG = $dC; $dB = 0.0; break;
            case 2:
                $dR = 0.0; $dG = $dC; $dB = $dX; break;
            case 3:
                $dR = 0.0; $dG = $dX; $dB = $dC; break;
            case 4:
                $dR = $dX; $dG = 0.0; $dB = $dC; break;
            case 5:
                $dR = $dC; $dG = 0.0; $dB = $dX; break;
            default:
                $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
        }

        $dM  = $dV - $dC;
        $dR += $dM; $dG += $dM; $dB += $dM;
        $dR *= 255; $dG *= 255; $dB *= 255;

        return array(
            'red' => round($dR),
            'green' => round($dG),
            'blue' => round($dB)
        );
    }

    public function ForwardData($text)
    {
        IPS_LogMessage("LedController", "Sending Data: " . $text);

        $data = json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => utf8_encode($text)));
        $this->SendDataToParent($data);
    }

    public function ReceiveData($json)
    {
        $data = json_decode($json);
        IPS_LogMessage("LedController", "Received Data: " . utf8_decode($data->Buffer));
    }

    public function Enable()
    {
        $this->SetBatch(255, 255, 255);
    }

    public function Disable()
    {
        $this->SetBatch(0, 0, 0);
    }

    public function Reset()
    {
        $this->ForwardData("RESET\n");
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
            $colorBuffer[] = $c['blue'];
            $colorBuffer[] = $c['green'];
            $colorBuffer[] = $c['red'];
        }

        $this->ForwardData("COMMAND_EXECUTE_SETALL\n");
        IPS_Sleep(1);

        $this->ForwardData(implode(array_map("chr", $colorBuffer)));
        IPS_Sleep(35);
    }
}
