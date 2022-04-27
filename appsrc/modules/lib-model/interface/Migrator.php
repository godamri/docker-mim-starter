<?php
/**
 * Model migrator interface
 * @package lib-model
 * @version 0.0.1
 */

namespace LibModel\Iface;

interface Migrator
{
    public function __construct(string $model, array $data);
    public function lastError(): ?string;
    public function db(array $configs): bool;
    public function schema(string $dirname): bool;
    public function start(): bool;
    public function test(): ?array;
}