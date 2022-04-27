<?php
/**
 * GateController Interface
 * @package core
 * @version 0.0.1
 */

namespace Mim\Iface;

interface GateController
{
    public function show404(): void;
    public function show404Action(): void;
    
    public function show500(object $error): void;
    public function show500Action(): void;
}