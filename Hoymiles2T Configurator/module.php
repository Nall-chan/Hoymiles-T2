<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/libs/Hoymiles2T.php';  // diverse Klassen
eval('declare(strict_types=1);namespace Hoymiles2TConfigurator {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');

class Hoymiles2TConfigurator extends IPSModuleStrict
{
    use \Hoymiles2TConfigurator\DebugHelper;

    public function Create(): void
    {
        parent::Create();
        $this->ConnectParent(\Hoymiles2T\GUID::IO);
    }

    public function Destroy(): void
    {
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->SetReceiveDataFilter('.*"NOTHING".*');
    }

    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if ($this->GetStatus() == IS_CREATING) {
            return json_encode($Form);
        }
        if (!$this->HasActiveParent()) {
            $Form['actions'][1]['visible'] = true;
            $Form['actions'][1]['popup']['items'][0]['caption'] = 'Instance has no active parent.';
            $Form['actions'][0]['items'][0]['visible'] = false;
        }
        $Values = $this->GetDevicesConfigValues();
        $Form['actions'][0]['values'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    private function GetDevicesConfigValues(): array
    {
        return [];
        /*
        $FoundDevices = $this->GetDevicesFromBridge();
        $this->GetNamesFromBridge($FoundDevices);
        $this->SendDebug('Found Devices', $FoundDevices, 0);

        $InstanceIDList = [];
        $Splitter = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        foreach (IPS_GetInstanceListByModuleID('{742F943E-F3E2-0E3E-8F3D-9ACDDC379D26}') as $InstanceID) {
            // Fremde Geräte überspringen
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $Splitter) {
                $InstanceIDList[$InstanceID] = IPS_GetProperty($InstanceID, 'Address');
            }
        }
        $this->SendDebug('Known Instances', $InstanceIDList, 0);
        foreach ($FoundDevices as &$Device) {
            $InstanceIDDevice = array_search($Device['Address'], $InstanceIDList);
            if ($InstanceIDDevice !== false) {
                $Device['instanceID'] = $InstanceIDDevice;
                $Device['Name'] = IPS_GetName($InstanceIDDevice);
                $Device['Location'] = stristr(IPS_GetLocation($InstanceIDDevice), IPS_GetName($InstanceIDDevice), true);
                unset($InstanceIDList[$InstanceIDDevice]);
            } else {
                $Device['instanceID'] = 0;
                $Device['Name'] = $Device['Name'];
                $Device['Location'] = '';
            }
            $Device['create'] = [
                'moduleID'      => '{742F943E-F3E2-0E3E-8F3D-9ACDDC379D26}',
                'configuration' => ['Address' => $Device['Address']]
            ];
        }

        foreach ($InstanceIDList as $InstanceID => $DeviceAddress) {
            $FoundDevices[] = [
                'instanceID'  => $InstanceID,
                'Address'     => $DeviceAddress,
                'Name'        => IPS_GetName($InstanceID),
                'Location'    => stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true)
            ];
        }
        return $FoundDevices;
         */
    }
    private function GetInstanceList(string $GUID, int $Parent, string $ConfigParam): array
    {
        $InstanceIDList = [];
        foreach (IPS_GetInstanceListByModuleID($GUID) as $InstanceID) {
            // Fremde Geräte überspringen
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $Parent) {
                $InstanceIDList[] = $InstanceID;
            }
        }
        if ($ConfigParam != '') {
            $InstanceIDList = array_flip(array_values($InstanceIDList));
            array_walk($InstanceIDList, [$this, 'GetConfigParam'], $ConfigParam);
        }
        return $InstanceIDList;
    }
}
