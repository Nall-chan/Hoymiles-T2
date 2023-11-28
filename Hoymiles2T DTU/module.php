<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/libs/Hoymiles2T.php';  // diverse Klassen
eval('declare(strict_types=1);namespace Hoymiles2TDTU {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/VariableHelper.php') . '}');

/**
 * @method void SetValueInteger(string $Ident, int $value)
 * @method void SetValueFloat(string $Ident, float $value)
 */
class Hoymiles2TDTU extends IPSModuleStrict
{
    use \Hoymiles2TDTU\VariableHelper;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
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
    }

    public function ReceiveData(string $JSONString): string
    {
        $data = json_decode($JSONString);
        $this->SendDebug('Receive', $data->Data, 0);
        $this->DecodeData(json_decode($data->Data, true));
        return '';
    }
        private function DecodeData(array $DataValues): void
        {
            foreach ($DataValues as $Key => $Value) {
                if ($Key == \Hoymiles2T\DTU\Variables::SerialNumber) {
                    $this->SetSummary($Value);
                }
                if (!array_key_exists($Key, \Hoymiles2T\DTU\Variables::$Vars)) {
                    continue;
                }
                $Var = \Hoymiles2T\DTU\Variables::$Vars[$Key];
                if (!$this->FindIDForIdent($Key)) {
                    $this->MaintainVariable($Key, $this->Translate($Var[0]), $Var[1], $Var[2], 0, true);
                }
                switch ($Var[1]) {
                    case VARIABLETYPE_INTEGER:
                        $this->SetValueInteger($Key, $Value);
                        break;
                    case VARIABLETYPE_FLOAT:
                        $this->SetValueFloat($Key, $Value * $Var[3]);
                        break;
                }
            }
        }
}
