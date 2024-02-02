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
require_once dirname(__DIR__) . '/libs/Hoymiles/CommandReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/CommandResDTO.php';
/*
require_once dirname(__DIR__) . '/libs/Hoymiles/GetConfigReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/GetConfigResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/InfoDataReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/InfoDataResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/NetworkInfoReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/NetworkInfoResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/WarnReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/WarnResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/CommandStatusReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/CommandStatusResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/DevConfigFetchResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/DevConfigFetchReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/EventDataReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/EventDataResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/HBReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/HBResDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/WWVDataReqDTO.php';
require_once dirname(__DIR__) . '/libs/Hoymiles/WWVDataResDTO.php';
 */
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
        $this->RegisterAttributeInteger(\Hoymiles2T\IO\Attribute::LastState, IS_CREATING);
        $this->RegisterTimer(\Hoymiles2T\IO\Timer::RequestState, 0, 'IPS_RequestAction(' . $this->InstanceID . ',"' . \Hoymiles2T\IO\Timer::RequestState . '",true);');
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
        if ($this->ReadPropertyString(\Hoymiles2T\IO\Property::Host) == '') {
            return;
        }

        if ($this->ReadPropertyBoolean(\Hoymiles2T\IO\Property::Active)) {
            $this->StartWithDayNightCheck();
        } else {
            $this->SetStatus(IS_INACTIVE);
        }
    }

    public function SetActive(): bool
    {
        if ($this->ReadPropertyString(\Hoymiles2T\IO\Property::Host) == '') {
            return false;
        }
        if (!$this->ReadPropertyBoolean(\Hoymiles2T\IO\Property::Active)) {
            return false;
        }

        if ($this->GetStatus() != IS_INACTIVE) {
            return false;
        }
        $this->SetStatus(IS_ACTIVE);
        return true;
    }

    public function SetInactive(): bool
    {
        if ($this->GetStatus() != IS_ACTIVE) {
            return false;
        }
        $this->SetStatus(IS_INACTIVE);
        return true;
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
        $Data = json_decode($JSONString, true);
        switch ($Data['Function']) {
            case 'ListDevices':
                return serialize(
                    [
                        \Hoymiles2T\ConfigArray::NbrOfInverter  => $this->NbrOfInverter,
                        \Hoymiles2T\ConfigArray::NbrOfSolarPort => $this->NbrOfSolarPort
                    ]
                );
            case 'SetPowerLimit':
                $Request = new \Hoymiles\CommandResDTO();
                $Request->setTime(time());
                $Request->setTid(time());
                $Request->setPackageNub(1);
                $Request->setAction(8);
                $Request->setData($Data['Data']);
                $RequestBytes = $Request->serializeToString();
                $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::CommandResDTO, $RequestBytes);
                if (!$ResultStream) {
                    return serialize(false);
                }
                $Result = new \Hoymiles\CommandReqDTO();
                $Result->mergeFromString($ResultStream);
                return serialize($Result->getErrCode() == 0);
        }
    }

    protected function Test(): bool
    {
        // WWVDataResDTO commando unbekannt
        // EventDataResDTO commando unbekannt
        /*
        $Request = new \Hoymiles\EventDataResDTO();
        $Request->setTime(time());
        $Request->setOffset(28800);
        //$Request->setYmdHmsStart(date('Y-m-d H:i:s', time()-1841976410));//-60*60*24*7));
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(0xA301, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\EventDataReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\DevConfigFetchResDTO();
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::DevConfigFetchResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\DevConfigFetchReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\CommandStatusResDTO();
        $Request->setPackageNow(1);
        $Request->setTime(time());
        $Request->setAction(8);
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::CommandStatusResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\CommandStatusReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\CommandResDTO();
        $Request->setTime(time());
        $Request->setTid(time());
        $Request->setPackageNub(1);
        $Request->setAction(8);
        $Request->setData("A:800\r");
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::CommandResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\CommandReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\WarnResDTO();
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::WarnResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\WarnReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\NetworkInfoResDTO();
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::NetworkInfoResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\NetworkInfoReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\InfoDataResDTO();
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::InfoDataResDTO, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\InfoDataReqDTO();
        $Result->mergeFromString($ResultStream);
         */
        /*
        $Request = new \Hoymiles\GetConfigResDTO();
        $RequestBytes = $Request->serializeToString();
        $ResultStream = $this->SendCommand(\Hoymiles\DTU\Commands::GetConfig, $RequestBytes);
        if (!$ResultStream) {
            return false;
        }
        $Result = new \Hoymiles\GetConfigReqDTO(); // rssi in -db ?
        $Result->mergeFromString($ResultStream);
         */

        $Json = $Result->serializeToJsonString();
        $this->SendDebug('TEST', $Json, 0);
        $this->SendDebug('TEST', json_decode($Json, true), 0);
        return true;
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
        switch ($NewState) {
            case IS_ACTIVE:
                $this->SetTimerInterval(\Hoymiles2T\IO\Timer::RequestState, $this->ReadPropertyInteger(\Hoymiles2T\IO\Property::RequestInterval) * 1000);
                $this->WriteAttributeInteger(\Hoymiles2T\IO\Attribute::LastState, IS_ACTIVE);
                break;
            case IS_INACTIVE:
                $this->WriteAttributeInteger(\Hoymiles2T\IO\Attribute::LastState, IS_INACTIVE);
                // And deactivate timer
                // No break. Add additional comment above this line if intentional
            default:
                $this->SetTimerInterval(\Hoymiles2T\IO\Timer::RequestState, 0);
                break;
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
        //$this->RequestState();
    }

    private function RealDataResDTO(): bool
    {
        $Request = new \Hoymiles\RealDataResDTO();
        $Request->setYmdHms(date('Y-m-d H:i:s', time()));
        $Request->setTime(time());
        $Request->setOft(28800);
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
        $this->SendDebug('SendCommand', pack('n', $Command), 1);
        $this->SendDebug('RequestBytes', $RequestBytes, 1);
        $CRC16 = pack('n', $this->CRC16($RequestBytes));
        $Len = strlen($RequestBytes) + 10;
        //$this->SendDebug('CRC16', $CRC16, 1);
        //$this->SendDebug('Len', $Len, 0);
        $this->lock(\Hoymiles2T\IO\Locks::SendSequenz);
        $Sequenz = ++$this->Sequenz;
        $this->SendDebug('SendSequenz', pack('n', $Sequenz), 1);
        $this->unlock(\Hoymiles2T\IO\Locks::SendSequenz);
        $Content = \Hoymiles\DTU\SendStream::Header . pack('n', $Command) . pack('n', $Sequenz) . $CRC16 . pack('n', $Len) . $RequestBytes;
        $DeviceAddress = 'tcp://' . $this->ReadPropertyString(\Hoymiles2T\IO\Property::Host) . ':' . $this->ReadPropertyInteger(\Hoymiles2T\IO\Property::Port);
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client($DeviceAddress, $errno, $errstr, 5);
        if (!$fp) {
            $this->SendDebug('ERROR (' . $errno . ')', $errstr, 0);
            // todo trigger_error
            return false;
        } else {
            $this->SendDebug('Send', $Content, 1);
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
        if (!$Data) {
            // todo trigger_error
            $this->SendDebug('ERROR (0)', 'Timeout', 0);
            return false;
        }
        $Header = substr($Data, 0, 10);
        $Payload = substr($Data, 10);
        $this->SendDebug('Recv Command', substr($Header, 2, 2), 1);
        $this->SendDebug('Recv Payload', $Payload, 1);
        $this->SendDebug('Recv Sequenz', unpack('n', substr($Header, 4, 2))[1], 0);
        $Len = unpack('n', substr($Header, 8, 2))[1];
        //$this->SendDebug('Recv Len', $Len, 0);
        if ($Len != strlen($Payload) + 10) {
            trigger_error($this->Translate('Data has wrong length.'), E_USER_NOTICE);
            return false;
        }
        //$this->SendDebug('Recv CRC', substr($Header, 6, 2), 1);
        $CRC16 = pack('n', $this->CRC16($Payload));
        //$this->SendDebug('Recv CRC', $CRC16, 1);
        if ($CRC16 != substr($Header, 6, 2)) {
            trigger_error($this->Translate('Invalid checksum.'), E_USER_NOTICE);
            return false;
        }
        return $Payload;
    }

    private function CRC16(string $string): int
    {
        $crc = 0xffff;
        $polynom = 0x8005;
        for ($i = 0; $i < strlen($string); $i++) {
            $c = ord(self::reverseChar($string[$i]));
            $crc ^= ($c << 8);
            for ($j = 0; $j < 8; ++$j) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) & 0xffff) ^ $polynom;
                } else {
                    $crc = ($crc << 1) & 0xffff;
                }
            }
        }
        $ret = pack('cc', $crc & 0xff, ($crc >> 8) & 0xff);
        $ret = self::reverseString($ret);
        $arr = unpack('vshort', $ret);
        $crc = $arr['short'];
        return $crc;
    }

    private static function reverseString($str)
    {
        $m = 0;
        $n = strlen($str) - 1;
        while ($m <= $n) {
            if ($m == $n) {
                $str[$m] = self::reverseChar($str[$m]);
                break;
            }
            $ord1 = self::reverseChar($str[$m]);
            $ord2 = self::reverseChar($str[$n]);
            $str[$m] = $ord2;
            $str[$n] = $ord1;
            $m++;
            $n--;
        }
        return $str;
    }

    private static function reverseChar($char)
    {
        $byte = ord($char);
        $tmp = 0;
        for ($i = 0; $i < 8; ++$i) {
            if ($byte & (1 << $i)) {
                $tmp |= (1 << (7 - $i));
            }
        }
        return chr($tmp);
    }
}
