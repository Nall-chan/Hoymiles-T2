<?php

declare(strict_types=1);

$AutoLoader = new AutoLoaderHoymiles2T('Google\Protobuf');
$AutoLoader->register();

class AutoLoaderHoymiles2T
{
    private $namespace;

    public function __construct($namespace = null)
    {
        $this->namespace = $namespace;
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass($className)
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

require_once dirname(__DIR__) . '/libs/Hoymiles2T.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/RealDataResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/RealDataReqDTO.php';
eval('declare(strict_types=1);namespace HoymilesIO {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');
eval('declare(strict_types=1);namespace HoymilesIO {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace HoymilesIO {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/SemaphoreHelper.php') . '}');

/**
 * @property int $Sequenz
 * @property int $NbrOfInverter
 * @property int $NbrOfSolarPort
 *
 * @method bool lock(string $ident)
 * @method void unlock(string $ident)
 * @method bool SendDebug(string $Message, mixed $Data, int $Format)
 */
class Hoymiles2TIO extends IPSModuleStrict
{
    use \HoymilesIO\DebugHelper;
    use \HoymilesIO\Semaphore;
    use \HoymilesIO\BufferHelper;

    public function Create(): void
    {
        parent::Create();

        $this->Sequenz = 0;
        $this->NbrOfInverter = 0;
        $this->NbrOfSolarPort = 0;

        $this->RegisterPropertyBoolean(\Hoymiles2T\IO\Property::Active, false);
        $this->RegisterPropertyString(\Hoymiles2T\IO\Property::Host, '');
        $this->RegisterPropertyInteger(\Hoymiles2T\IO\Property::Port, 10081);
        $this->RegisterPropertyInteger(\Hoymiles2T\IO\Property::RequestInterval, 10);
        $this->RegisterPropertyInteger(\Hoymiles2T\IO\Property::LocationId, 1);
        $this->RegisterPropertyInteger(\Hoymiles2T\IO\Property::StartVariableId, 1);
        $this->RegisterPropertyInteger(\Hoymiles2T\IO\Property::StopVariableId, 1);
        $this->RegisterPropertyString(\Hoymiles2T\IO\Property::DayValue, '""');
        $this->RegisterPropertyString(\Hoymiles2T\IO\Property::NightValue, '""');

        $this->RegisterTimer(\Hoymiles2T\IO\Timer::RequestState, 0, 'IPS_RequestAction(' . $this->InstanceID . ',"' . \Hoymiles2T\IO\Timer::RequestState . '",true);');
        $this->RegisterTimer(\Hoymiles2T\IO\Timer::Reconnect, 0, 'IPS_RequestAction(' . $this->InstanceID . ',"' . \Hoymiles2T\IO\Timer::Reconnect . '",true);');
    }

    public function Destroy(): void
    {
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->Sequenz = 0;
        $this->SetSummary($this->ReadPropertyString(\Hoymiles2T\IO\Property::Host));

        // Wenn Kernel nicht bereit, dann warten... KR_READY kommt ja gleich
        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
            return;
        }
        if ($this->ReadPropertyBoolean(\Hoymiles2T\IO\Property::Active)) {
            $this->StartWithDayNightCheck();
        } else {
            $this->SetStatus(IS_INACTIVE);
        }
    }
    /**
     * Nachrichten aus der Nachrichtenschlange verarbeiten.
     *
     * @param int       $TimeStamp
     * @param int       $SenderID
     * @param int       $Message
     * @param array|int $Data
     */
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->UnregisterMessage(0, IPS_KERNELSTARTED);
                $this->KernelReady();
                break;
        }
    }
    /**
     * Interne Funktion des SDK.
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case \Hoymiles2T\IO\Timer::RequestState:
                $this->RequestState();
                return;
            case \Hoymiles2T\IO\Timer::Reconnect:
                $this->ApplyChanges();
                return;
            case \Hoymiles2T\IO\Property::LocationId:
                $this->UpdateNightObjectForm($Value);
                return;
            case \Hoymiles2T\IO\Property::DayValue:
            case \Hoymiles2T\IO\Property::NightValue:
                $this->UpdateDayNightVariables($Value, $Ident);
                return;
        }
    }

    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if ($this->GetStatus() == IS_CREATING) {
            return json_encode($Form);
        }

        $StartVariableId = $this->ReadPropertyInteger(\Hoymiles2T\IO\Property::StartVariableId);
        if ($StartVariableId < 10000) {
            $StartVariableId = false;
        } else {
            if (IPS_VariableExists($StartVariableId)) {
                if (IPS_GetVariable($StartVariableId)['VariableProfile'] == '~UnixTimestamp') {
                    $StartVariableId = false;
                }
            } else {
                $StartVariableId = false;
            }
        }
        if ($StartVariableId) {
            $Form['elements'][3]['items'][1]['variableID'] = $StartVariableId;
            $Form['elements'][3]['items'][1]['value'] = json_decode($this->ReadPropertyString(\Hoymiles2T\IO\Property::DayValue), true);
        } else {
            $Form['elements'][3]['items'][1]['variableID'] = -1;
            $Form['elements'][3]['items'][1]['value'] = '""';
            $Form['elements'][3]['items'][1]['visible'] = false;
        }

        $StopVariableId = $this->ReadPropertyInteger(\Hoymiles2T\IO\Property::StopVariableId);

        if ($StopVariableId < 10000) {
            $StopVariableId = false;
        } else {
            if (IPS_VariableExists($StopVariableId)) {
                if (IPS_GetVariable($StopVariableId)['VariableProfile'] == '~UnixTimestamp') {
                    $StopVariableId = false;
                }
            } else {
                $StopVariableId = false;
            }
        }

        if ($StopVariableId) {
            $Form['elements'][4]['items'][1]['variableID'] = $StopVariableId;
            $Form['elements'][4]['items'][1]['value'] = json_decode($this->ReadPropertyString(\Hoymiles2T\IO\Property::NightValue), true);
        } else {
            $Form['elements'][4]['items'][1]['variableID'] = -1;
            $Form['elements'][4]['items'][1]['value'] = '""';
            $Form['elements'][4]['items'][1]['visible'] = false;
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    public function RequestState(): bool
    {
        if ($this->GetStatus() != IS_ACTIVE) {
            trigger_error($this->Translate('Instance is not active.'), E_USER_NOTICE);
            return false;
        }
        return $this->RealDataResDTO();
    }
    public function ForwardData($JSONString): string
    {
        return serialize(
            [
                \Hoymiles2T\ConfigArray::NbrOfInverter  => $this->NbrOfInverter,
                \Hoymiles2T\ConfigArray::NbrOfSolarPort => $this->NbrOfSolarPort
            ]
        );
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    protected function SetStatus(int $NewState): bool
    {
        $this->SetTimerInterval(\Hoymiles2T\IO\Timer::RequestState, 0);
        $this->SetTimerInterval(\Hoymiles2T\IO\Timer::Reconnect, 0);

        if ($NewState == IS_ACTIVE) {
            $this->SetTimerInterval(\Hoymiles2T\IO\Timer::RequestState, $this->ReadPropertyInteger(\Hoymiles2T\IO\Property::RequestInterval) * 1000);
        }
        parent::SetStatus($NewState);
        return true;
    }

    private function UpdateDayNightVariables(int $VariableId, string $Property): void
    {
        if ($VariableId < 10000) {
            $this->UpdateFormField($Property, 'variableID', -1);
            $this->UpdateFormField($Property, 'visible', false);
            $this->UpdateFormField($Property, 'value', '""');
            return;
        }
        if (!IPS_VariableExists($VariableId)) {
            $this->UpdateFormField($Property, 'variableID', -1);
            $this->UpdateFormField($Property, 'visible', false);
            $this->UpdateFormField($Property, 'value', '""');
            return;
        }
        switch (IPS_GetVariable($VariableId)['VariableType']) {
            case VARIABLETYPE_BOOLEAN:
                $this->UpdateFormField($Property, 'variableID', $VariableId);
                $this->UpdateFormField($Property, 'visible', true);
                break;
            case VARIABLETYPE_INTEGER:
                if (IPS_GetVariable($VariableId)['VariableProfile'] == '~UnixTimestamp') {
                    $this->UpdateFormField($Property, 'variableID', -1);
                    $this->UpdateFormField($Property, 'visible', false);
                    $this->UpdateFormField($Property, 'value', '""');
                } else {
                    $this->UpdateFormField($Property, 'variableID', $VariableId);
                    $this->UpdateFormField($Property, 'visible', true);
                }
                break;
            default:
                $this->UpdateFormField($Property, 'visible', false);
                $this->UpdateFormField($Property, 'variableID', -1);
                $this->UpdateFormField($Property, 'value', '""');
                break;
        }
    }
    private function UpdateNightObjectForm(int $LocationId): void
    {
        if ($LocationId < 10000) {
            $this->UpdateFormField('StartVariableId', 'value', 0);
            $this->UpdateFormField('StopVariableId', 'value', 0);
            return;
        }
        if (!IPS_InstanceExists($LocationId)) {
            $this->UpdateFormField('StartVariableId', 'value', 0);
            $this->UpdateFormField('StopVariableId', 'value', 0);
            return;
        }
        if (IPS_GetInstance($LocationId)['ModuleInfo']['ModuleID'] != \Hoymiles2T\GUID::LocationControl) {
            $this->UpdateFormField('StartVariableId', 'value', 0);
            $this->UpdateFormField('StopVariableId', 'value', 0);
            return;
        }
        $this->UpdateFormField('StartVariableId', 'value', IPS_GetObjectIDByIdent('Sunrise', $LocationId));
        $this->UpdateFormField('StopVariableId', 'value', IPS_GetObjectIDByIdent('Sunset', $LocationId));
    }
    private function StartWithDayNightCheck()
    {
        //todo
        $this->SetStatus(IS_ACTIVE);
    }
    private function RealDataResDTO(): bool
    {
        $Request = new \Hoymiles\RealDataResDTO();
        //todo set time ?
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::RealDataResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }

        $Result = new \Hoymiles\RealDataReqDTO();
        $Result->mergeFromString($ResultStream);

        $DTU = json_encode([
            'sn'            => $Result->getSn(),
            'time'          => $Result->getTime(),
            'pvCurrentPower'=> $Result->getPvCurrentPower(),
            'pvDailyYield'  => $Result->getPvDailyYield()
        ]);
        $this->SendDebug('DTU', $DTU, 0);
        $this->SendDataToChildren(
            json_encode(
                [
                    'DataID'     => \Hoymiles2T\GUID::IoToDTU,
                    'Data'       => $DTU
                ]
            )
        );
        /** @var \Hoymiles\InverterState[] $Inverters */
        $Inverters = $Result->getInverterState();
        /** @var \Hoymiles\PortState[] $SolarPorts */
        $SolarPorts = $Result->getPortState();

        $this->NbrOfInverter = count($Inverters);
        $this->NbrOfSolarPort = count($SolarPorts);

        foreach ($Inverters as $Inverter) {
            $this->SendDebug('Inverter:' . $Inverter->getVer(), $Inverter->serializeToJsonString(), 0);
            $this->SendDataToChildren(
                json_encode(
                    [
                        'DataID'     => \Hoymiles2T\GUID::IoToInverter,
                        'Data'       => $Inverter->serializeToJsonString()
                    ]
                )
            );
        }
        foreach ($SolarPorts as $SolarPort) {
            $this->SendDebug('Solar:' . $SolarPort->getPi(), $SolarPort->serializeToJsonString(), 0);
            $this->SendDataToChildren(
                json_encode(
                    [
                        'DataID'     => \Hoymiles2T\GUID::IoToSolarPort,
                        'Data'       => $SolarPort->serializeToJsonString()
                    ]
                )
            );
        }
        return true;
    }

    private function SendCommand(int $Command, string $RequestBytes): false|string
    {
        $this->SendDebug('SendCommand', $Command, 0);
        $CRC16 = $this->CRC16($RequestBytes);
        $Len = strlen($RequestBytes) + 10;
        $this->lock(\Hoymiles2T\IO\Locks::SendSequenz);
        $Content = \Hoymiles\DTU\SendStream::Header . pack('n', $Command) . pack('n', ++$this->Sequenz) . $RequestBytes . $CRC16 . pack('n', $Len);
        $this->unlock(\Hoymiles2T\IO\Locks::SendSequenz);
        $DeviceAddress = 'tcp://' . $this->ReadPropertyString(\Hoymiles2T\IO\Property::Host) . ':' . $this->ReadPropertyInteger(\Hoymiles2T\IO\Property::Port);
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client($DeviceAddress, $errno, $errstr, 5);
        if (!$fp) {
            $this->SendDebug('ERROR (' . $errno . ')', $errstr, 0);
            // todo trigger_error
            return false;
        } else {
            $this->SendDebug('Send', $Content, 0);
            for ($fwrite = 0, $written = 0, $max = strlen($Content); $written < $max; $written += $fwrite) {
                $fwrite = @fwrite($fp, substr($Content, $written));
                if ($fwrite === false) {
                    $this->SendDebug('ERROR on write (' . $errno . ')', $errstr, 0);
                    @fclose($fp);
                    // todo trigger_error
                    return false;
                }
            }
            $Data = '';
            $Data = fread($fp, 8192);
            fclose($fp);
        }
        $Header = substr($Data, 0, 10);
        $this->SendDebug('Recv Header', substr($Header, 0, 2), 1);
        $this->SendDebug('Recv Command', unpack('n', substr($Header, 2, 2))[1], 0);
        $this->SendDebug('Recv Sequenz', unpack('n', substr($Header, 4, 2))[1], 0);
        $this->SendDebug('Recv CRC', substr($Header, 6, 2), 1);
        $this->SendDebug('Recv Len', unpack('n', substr($Header, 8, 2))[1], 0);
        $Payload = substr($Data, 10);
        return $Payload;
    }

    private function CRC16(string $string): string
    {
        $crc = 0xffff;
        $polynom = 0x8005;
        for ($x = 0; $x < strlen($string); $x++) {
            $crc = $crc ^ ord($string[$x]);
            for ($y = 0; $y < 8; $y++) {
                if (($crc & 0x0001) == 0x0001) {
                    $crc = (($crc >> 1) ^ $polynom);
                } else {
                    $crc = $crc >> 1;
                }
            }
        }
        $high_byte = ($crc & 0xff00) / 256;
        $low_byte = $crc & 0x00ff;

        return chr($high_byte) . chr($low_byte);
    }
}
