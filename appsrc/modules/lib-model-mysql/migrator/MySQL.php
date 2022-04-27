<?php
/**
 * MySQL migrator
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Migrator;

use LibModelMysql\Library\{
    Data,
    Index,
    Query,
    Table
};
use Mim\Library\Fs;

class MySQL implements \LibModel\Iface\Migrator
{
    private $model;
    private $data;
    private $error;

    public function __construct(string $model, array $data){
        $this->model = $model;
        $this->data  = $data;
    }

    public function lastError(): ?string{
        return $this->error;
    }

    public function db(array $configs): bool{
        $args = [
            'host'      => ini_get('mysqli.default_host'),
            'user'      => ini_get('mysqli.default_user'),
            'passwd'    => ini_get('mysqli.default_pw'),
            'dbname'    => '',
            'port'      => ini_get('mysqli.default_port'),
            'socket'    => ini_get('mysqli.default_socket')
        ];

        $used_conns = null;
        foreach($configs as $config){
            $fn_args = [];
            foreach($args as $arg => $def)
                $fn_args[] = $config->$arg ?? $def;

            $dbname = $fn_args[3];
            $fn_args[3] = NULL;

            $conn = call_user_func_array('mysqli_connect', $fn_args);
            if(mysqli_connect_error()){
                $this->error = mysqli_connect_error();
                return false;
            }

            $result = mysqli_query($conn, 'SHOW DATABASES;');
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            mysqli_free_result($result);
            $rows = array_column($rows, 'Database');

            if(in_array($dbname, $rows))
                continue;

            $sql = 'CREATE DATABASE `' . $dbname . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

            $result = mysqli_query($conn, $sql);
            if(!$result){
                $this->error = mysqli_error($conn);
                return false;
            }

            mysqli_close($conn);
        }

        return true;
    }

    public function schema(string $file): bool{
        $diff = $this->test();
        if(!$diff)
            return true;

        $sql = Query::build($this->model, $this->data, $diff);

        if($sql){
            $target_file = $file . '.sql';
            Fs::write($target_file, $sql, true);
        }

        return true;
    }

    public function start(): bool{
        $diff = $this->test();
        if(!$diff)
            return true;

        $sqls = Query::buildMutliple($this->model, $this->data, $diff, false);
        if(!$sqls)
            return true;

        $model = $this->model;

        $result = true;

        foreach($sqls as $sql){
            $res = $model::query($sql, 'write');
            if(!$res){
                $result = false;
                $this->error = $model::lastError();
            }
        }
        
        return $result;
    }

    public function test(): ?array{
        $result = [];
        
        // table structure
        $res_table = Table::test($this->model, $this->data['fields']);
        if($res_table)
            $result = array_replace($result, $res_table);

        // index structure
        $res_index = Index::test($this->model, $this->data['indexes'] ?? [], $this->data['fields']);
        if($res_index)
            $result = array_replace($result, $res_index);

        // data row
        if(isset($this->data['data'])){
            $res_data = Data::test($this->model, $this->data['data']);
            if($res_data)
                $result = array_replace($result, $res_data);
        }

        return $result;
    }
}