<?php
/**
 * Model driver interface
 * @package lib-model
 * @version 0.0.1
 */

namespace LibModel\Iface;

interface Driver
{
    public function __construct(array $options);
    public function autocommit(bool $mode, string $conn='write'): bool;
    public function avg(string $field, array $where=[]);
    public function count(array $where=[]): int;
    public function countGroup(string $field, array $where=[]): array;
    public function create(array $row, bool $ignore=false): ?int;
    public function createMany(array $rows, bool $ignore=false): bool;
    public function commit(string $conn='write'): bool;
    public function dec(array $fields, array $where=[]): bool;
    public function escape(string $str): string;
    public function getOne(array $where=[], array $order=['id'=>false]): ?object;
    public function get(array $where=[], int $rpp=0, int $page=1, array $order=['id'=>false]): ?array;
    public function getConnection(string $target='read');
    public function getConnectionName(string $target='read'): ?string;
    public function getDBName(string $target='read'): ?string;
    public function getDriver(): ?string;
    public function getModel(): ?string;
    public function getTable(): string;
    public function inc(array $fields, array $where=[]): bool;
    public function lastError(): ?string;
    public function lastId(): ?int;
    public function lastQuery(): ?string;
    public function max(string $field, array $where=[]);
    public function min(string $field, array $where=[]);
    public function remove(array $where=[]): bool;
    public function rollback(string $conn='write'): bool;
    public function set(array $fields, array $where=[]): bool;
    public function sum(string $field, array $where=[]);
    public function sumFs(array $fields, array $where=[]);
    public function truncate(string $target='write'): bool;

}