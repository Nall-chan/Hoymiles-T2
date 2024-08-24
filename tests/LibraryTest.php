<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateIO(): void
    {
        $this->validateModule(__DIR__ . '/../HoymilesWiFi IO');
    }
    public function testValidateConfigurator(): void
    {
        $this->validateModule(__DIR__ . '/../HoymilesWiFi Configurator');
    }
    public function testValidateInverter(): void
    {
        $this->validateModule(__DIR__ . '/../HoymilesWiFi Inverter');
    }
    public function testValidateDTU(): void
    {
        $this->validateModule(__DIR__ . '/../HoymilesWiFi DTU');
    }
    public function testValidateSolarPort(): void
    {
        $this->validateModule(__DIR__ . '/../HoymilesWiFi SolarPort');
    }
}
