<?php

declare(strict_types=1);

namespace Hoymiles2T{
    class GUID
    {
        public const IO = '{5972AA13-358F-A088-CEBD-207C289C9395}';
        public const Configurator = '{4062635D-2680-4A39-C364-05EB8B196DA9}';
        public const DTU = '{BB414362-B36F-81C5-2701-E968A29F58AD}';
        public const Inverter = '{52D8E128-5588-B496-4BE5-14E8EFD737B8}';
        public const SolarPort = '{65B18475-D1B7-825C-5958-5300C1100845}';
        public const IoToDevices = '{CEE3BF9B-2A23-4433-ACAB-6E22F10D743F}';
        public const DevicesToIo = '{2651EA6C-47E9-BA79-7410-382895EC8244}';
    }
}

namespace Hoymiles2T\IO{
    class Property
    {
        public const Active = 'Open';
        public const Host = 'Host';
        public const Port = 'Port';
        public const RequestInterval = 'RequestInterval';
        public const NightObject = 'NightObject';
        public const NightInverted = 'NightInverted';
    }

    class Timer
    {
        public const RequestState = 'RequestState';
        public const Reconnect = 'Reconnect';
    }

    class Attribute
    {
    }

    class InstanceStatus
    {
        public const ConfigError = IS_EBASE + 1;
        public const TimeoutError = IS_EBASE + 2;
    }
    class Locks
    {
        public const SendSequenz = 'SendSequenz';
        public const ReplyDeviceFrames = 'ReplyDeviceFrames';
    }
}

namespace Hoymiles2T\Inverter{
    class Property
    {
        public const Number = 'Number';
    }
}

namespace Hoymiles2T\SolarPort{
    class Property
    {
        public const Port = 'Port';
    }
}

namespace Hoymiles\DTU{
    class SendStream
    {
        public const Header = 'HM';
    }
    class Commands
    {
        public const APPInfoDataResDTO = -23807; //Response APPInfoDataReqDTO
        public const HBResDTO = -23806; // Response HBReqDTO
        public const RealDataResDTO = -23805;
        public const WInfoResDTO = -23804;
        public const CommandResDTO = -23803;
        public const CommandStatusResDTO = -23802; // Response: CommandStatusReqDTO
        public const DevConfigFetchResDTO = -23801;
        public const DevConfigPutResDTO = -23800;
        public const GetConfig = -23799;
        public const SetConfig = -23792;
        public const RealResDTO = -23791;
        public const GPSTResDTO = -23790;
        public const AutoSearch = -23789;
        public const NetworkInfoRes = -23788; // Response: NetworkInfoReq
        public const AppGetHistPowerRes = -23787; // Response: AppGetHistPowerReq
        public const AppGetHistEDRes = -23786; // Response: AppGetHistEDReq
        //public const HBResDTO = -31999;
        public const RegisterResDTO = -31998;
        public const StorageDataRes = -31997;
        //public const CommandResDTO = -31995;
        //public const CommandStatusResDTO = -31994;
        //public const DevConfigFetchResDTO = -31993;
        //public const DevConfigPutResDTO = -31992;
        public const GetConfigRes = -9464;
        public const SetConfigRes = -9465;
    }
}