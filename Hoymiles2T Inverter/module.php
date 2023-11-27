<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/libs/Hoymiles2T.php';  // diverse Klassen
eval('declare(strict_types=1);namespace Hoymiles2TInverter {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');
eval('declare(strict_types=1);namespace Hoymiles2TInverter {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/VariableProfileHelper.php') . '}');
eval('declare(strict_types=1);namespace Hoymiles2TInverter {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/VariableHelper.php') . '}');

/**
 * @method void RegisterProfileInteger(string $Name, string $Icon, string $Prefix, string $Suffix, int $MinValue, int $MaxValue, int $StepSize)
 * @method void SetValueInteger(string $Ident, int $value)
 * @method void SetValueFloat(string $Ident, float $value)
 */
class Hoymiles2TInverter extends IPSModuleStrict
{
    use \Hoymiles2TInverter\DebugHelper;
    use \Hoymiles2TInverter\VariableProfileHelper;
    use \Hoymiles2TInverter\VariableHelper;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyInteger(\Hoymiles2T\Inverter\Property::Number, 1);
        $this->ConnectParent(\Hoymiles2T\GUID::IO);
    }

    public function Destroy(): void
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();
        /*
        $this->RegisterProfileInteger('SIRO.Tilt', 'TurnLeft', '', '%', 0, 180, 1);
        $this->RegisterVariableInteger('CONTROL', $this->Translate('Control'), '~ShutterMoveStop', 1);
        $this->EnableAction('CONTROL');
        $this->RegisterVariableInteger('LEVEL', $this->Translate('Level'), '~Shutter', 2);
        $this->EnableAction('LEVEL');
        $this->RegisterVariableInteger('TILT', $this->Translate('Tilt'), 'SIRO.Tilt', 3);
        $this->EnableAction('TILT');
        $this->RegisterVariableFloat('POWER', $this->Translate('Voltage'), '~Volt', 4);
         */
        $Address = $this->ReadPropertyInteger(\Hoymiles2T\Inverter\Property::Number);
        $this->SetSummary('Number: ' . (string) $Address);

        $this->SetStatus(IS_ACTIVE);

        if ($Address < 1) {
            $this->SetReceiveDataFilter('.*NOTING.*');
            return;
        }
        $Filter = '.*"INVERTER":' . $Address . ',.*';
        $this->SetReceiveDataFilter($Filter);
        $this->SendDebug('Filter', $Filter, 0);
    }

    /*
    public function ReceiveData(string $JSONString): string
    {
        $Data = json_decode($JSONString);
        $DeviceFrame = new \SIRO\DeviceFrame(
            $Data->DeviceCommand,
            $Data->DeviceAddress,
            $Data->Data
        );
        $this->SendDebug('Event', $DeviceFrame, 0);
        $this->DecodeEvent($DeviceFrame);
        return '';
    }


    private function DecodeEvent(\SIRO\DeviceFrame $DeviceFrame): void
    {
        switch ($DeviceFrame->Command) {
            case \SIRO\DeviceCommand::REPORT_STATE:
                $Part = explode('b', $DeviceFrame->Data);
                $Level = (int) $Part[0];
                $Tilt = (int) $Part[1];
                $this->SetValueInteger('LEVEL', $Level);
                $this->SetValueInteger('TILT', $Tilt);
                $this->RequestPowerState();
                break;
        }
    }
     */
}
