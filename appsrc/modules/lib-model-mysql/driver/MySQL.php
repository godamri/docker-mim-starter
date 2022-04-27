<?php
/**
 * MySQL driver
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Driver;

use LibModelMysql\Library\Table;

class MySQL implements \LibModel\Iface\Driver
{
    private static $conns = [];

    private $last_query;
    private $last_connection_name;

    private $used_join = [];

    private $chains;
    private $connections;
    private $model;
    private $q_field;
    private $table;

    private $operators = ['>','<','<=','>=','=','!=','NOT IN'];

    private function transField($val): ?string{
        $used_table = $this->getTable();
        $used_db    = $this->getDBName();
        $enclose    = true;

        if(is_array($val)){
            $used_table = $val[1] ?? null;
            $used_db    = $val[2] ?? null;
            $val        = $val[0];

        }elseif(substr($val,0,1) === '?'){
            $enclose    = false;
            $used_table = null;
            $used_db    = null;
            $val        = substr($val,1);

        }elseif(false !== strstr($val, '.')){
            $vals = explode('.', $val);
            if(count($vals) != 2)
                trigger_error('Unable to get referense too deep chains');

            $my_field = $vals[0];
            $val      = $vals[1];

            if(!isset($this->chains[$my_field])){
                trigger_error(
                    vsprintf('Field `%s`.`%s` don\'t have any reference to other table', [
                        $this->getTable(),
                        $my_field
                    ])
                );
            }

            $chain = $this->chains[$my_field];
            $chain_model = $chain['model'];

            $this->used_join[] = $my_field;

            $used_table = $chain_model::getTable();
            $used_db    = $chain_model::getDBName();
        }

        $vkey = [];
        $vval = [];

        if($used_db){
            $vkey = ['%s','%s','%s'];
            $vval = [$used_db, $used_table, $val];
        }elseif($used_table){
            $vkey = ['%s','%s'];
            $vval = [$used_table, $val];
        }else{
            $vkey = ['%s'];
            $vval = [$val];
        }

        $vkey = $enclose ? '`' . implode('`.`', $vkey) . '`' : implode('.', $vkey);

        return vsprintf($vkey, $vval);
    }

    private function transTable($val): ?string{
        $model = $this->model; 
        if(is_array($val)){
            $model = $val[1];
            $val = $val[0];
        }

        $used_db = $model::getDBName();

        return vsprintf('`%s`.`%s`', [$used_db, $val]);
    }

    private function transVal($val){
        $type = gettype($val);

        $result = $val;
        switch($type){
            case 'boolean':
                $result = $val ? 'TRUE' : 'FALSE';
                break;
            case 'float':
            case 'double':
                $result = str_replace(',', '.', $val);
                break;
            case 'integer':
                $result = $val;
                break;
            case 'string':
                $result = "'" . $this->escape($val) . "'";
                break;
            case 'array':
                $result = array_map([$this, 'transVal'], $val);
                break;
            case 'object':
                $result = "'" . $this->escape(json_encode($val)) . "'";
                break;
            case 'NULL':
                $result = 'NULL';
                break;
            default:
                $result = "'UNKNOW'";
        }

        return $result;
    }

    public function __construct(array $options){
        $this->model        = $options['model'];
        $this->table        = $options['table'];
        $this->chains       = $options['chains'];
        $this->connections  = $options['connections'];
        $this->q_field      = $options['q_field'];
    }

    public function autocommit(bool $mode, string $conn='write'): bool{
        $conn = $this->getConnection($conn);
        return mysqli_autocommit($conn, $mode);
    }

    public function avg(string $field, array $where=[]){
        $sql = $this->putField('SELECT AVG((:field)) AS (:result) (:from)', [
            'field' => $field
        ]);
        $sql = $this->putFieldPlain($sql, ['result' => 'result']);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return 0;
        $result = $result[0]->result;

        if(false !== strstr($result, '.'))
            return (double)$result;
        return (int)$result;
    }

    public function commit(string $conn='write'): bool{
        $conn = $this->getConnection($conn);
        return mysqli_commit($conn);
    }

    public function count(array $where=[]): int{
        $sql = $this->putFieldPlain('SELECT COUNT(*) AS (:result) (:from)', [
            'result' => 'result'
        ]);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return 0;
        $result = $result[0]->result;
        return (int)$result;
    }
    
    public function countGroup(string $field, array $where=[]): array{
        $sql = 'SELECT (:field) AS (:name), COUNT(*) AS (:total) (:from)';

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql.= 'GROUP BY (:field)';
        $sql = $this->putField($sql, ['field' => $field]);
        $sql = $this->putFieldPlain($sql, [
            'name'  => 'name',
            'total' => 'total'
        ]);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return [];
        return array_column($result, 'total', 'name');
    }
    
    public function create(array $row, bool $ignore=false): ?int{
        if(!$row)
            return null;

        $sql = 'INSERT INTO';
        if($ignore)
            $sql = 'INSERT IGNORE INTO';
        $sql.= ' (:table) ( (:fields) ) VALUES (:values)';

        $sql = $this->putTable($sql, [
            'table' => $this->getTable()
        ]);
        $sql = $this->putField($sql, [
            'fields' => array_keys($row)
        ]);
        $sql = $this->putValue($sql, [
            'values' => array_values($row)
        ]);

        $result = $this->query($sql, 'write');
        if(!$result)
            return null;

        return $this->lastId();
    }
    
    public function createMany(array $rows, bool $ignore=false): bool{
        $sql = 'INSERT INTO';
        if($ignore)
            $sql = 'INSERT IGNORE INTO';
        $sql.= ' (:table) ( (:fields) ) VALUES ';

        $sql = $this->putTable($sql, [
            'table' => $this->getTable()
        ]);

        $fields = [];
        foreach($rows as $row){
            foreach($row as $field => $val)
                $fields[] = $field;
        }

        $fields = array_values(array_unique($fields));
        if(!$fields)
            return false;

        $sql = $this->putField($sql, [
            'fields' => $fields
        ]);

        $vals = [];
        foreach($rows as $row){
            $used_values = [];
            foreach($fields as $field)
                $used_values[] = $row[$field] ?? null;
            
            $vals[] = $this->putValue('(:value)', [
                'value' => $used_values
            ]);
        }

        $sql.= implode(', ', $vals);

        return !!$this->query($sql, 'write');
    }
    
    public function dec(array $fields, array $where=[]): bool{
        $set_fields = [];
        foreach($fields as $fld => $val)
            $set_fields[$fld] = ['__dec', $val];
        return $this->set($set_fields, $where);
    }

    public function escape(string $str): string{
        return mysqli_real_escape_string($this->getConnection('read'), $str);
    }
    
    public function getOne(array $where=[], array $order=['id'=>false]): ?object{
        $sql = $this->putTable('SELECT (:table).* (:from)', [
            'table' => $this->getTable()
        ]);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        if($order)
            $sql.= $this->putOrder($order);

        $sql.= ' LIMIT 1';

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return null;

        return $result[0];
    }
    
    public function get(array $where=[], int $rpp=0, int $page=1, array $order=['id'=>false]): ?array{
        $sql = $this->putTable('SELECT (:table).* (:from)', [
            'table' => $this->getTable()
        ]);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        if($order)
            $sql.= $this->putOrder($order);

        if($rpp)
            $sql.= $this->putLimit($rpp, $page);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return null;
        return $result;
    }
    
    public function getConnection(string $target='read'): ?object{
        $name = $this->getConnectionName($target);
        if(isset(self::$conns[$name]))
            return self::$conns[$name]['connection'];

        $args = [
            'host'      => ini_get('mysqli.default_host'),
            'user'      => ini_get('mysqli.default_user'),
            'passwd'    => ini_get('mysqli.default_pw'),
            'dbname'    => '',
            'port'      => ini_get('mysqli.default_port'),
            'socket'    => ini_get('mysqli.default_socket')
        ];

        $conns = $this->connections[$target]->configs;
        $used_conns = null;
        mysqli_report(MYSQLI_REPORT_OFF);
        foreach($conns as $con){
            $fn_args = [];
            foreach($args as $arg => $def)
                $fn_args[] = $con->$arg ?? $def;

            $conn = call_user_func_array('mysqli_connect', $fn_args);
            if(mysqli_connect_error())
                continue;
            $used_conns = $conn;
            $used_conf  = $con;
            break;
        }

        if(!$used_conns)
            trigger_error('Unable to connect to any of database connection name `' . $name . '`');

        mysqli_set_charset($used_conns, 'utf8mb4');

        self::$conns[$name] = [
            'connection' => $used_conns,
            'config'     => $used_conf
        ];

        return $used_conns;
    }
    
    public function getConnectionName(string $target='read'): ?string{
        return $this->connections[$target]->name;
    }

    public function getDBName(string $target='read'): ?string{
        $this->getConnection($target);
        $name = $this->getConnectionName($target);
        if(isset(self::$conns[$name]))
            return self::$conns[$name]['config']->dbname;
        return null;
    }
    
    public function getDriver(): ?string{
        return 'mysql';
    }

    public function getLastConnection(): ?object{
        $last_conn = $this->last_connection_name;
        if(!$last_conn)
            return null;
        return $this->getConnection($last_conn);
    }
    
    public function getModel(): ?string{
        return $this->model;
    }
    
    public function getTable(): string{
        return $this->table;
    }
    
    public function inc(array $fields, array $where=[]): bool{
        $set_fields = [];
        foreach($fields as $fld => $val)
            $set_fields[$fld] = ['__inc', $val];
        return $this->set($set_fields, $where);
    }
    
    public function lastError(): ?string{
        $conn = $this->getLastConnection();
        if(!$conn)
            return null;
        return $conn->error;
    }
    
    public function lastId(): ?int{
        $conn = $this->getLastConnection();
        if(!$conn)
            return null;
        return (int)mysqli_insert_id($conn);
    }
    
    public function lastQuery(): ?string{
        return $this->last_query;
    }
    
    public function max(string $field, array $where=[]){
        $sql = $this->putField('SELECT MAX((:field)) AS (:result) (:from)', [
            'field' => $field
        ]);
        $sql = $this->putFieldPlain($sql, ['result' => 'result']);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return 0;
        $result = $result[0]->result;

        if(false !== strstr($result, '.'))
            return (double)$result;
        return (int)$result;
    }
    
    public function min(string $field, array $where=[]){
        $sql = $this->putField('SELECT MIN((:field)) AS (:result) (:from)', [
            'field' => $field
        ]);
        $sql = $this->putFieldPlain($sql, ['result' => 'result']);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return 0;
        $result = $result[0]->result;

        if(false !== strstr($result, '.'))
            return (double)$result;
        return (int)$result;
    }
    
    public function putField(string $sql, array $fields): string{
        foreach($fields as $key => $val){
            if(is_array($val)){
                $all_vals = [];
                foreach($val as $va)
                    $all_vals[] = $this->transField($va);
                $used_val = implode(', ', $all_vals);
            }else{
                $used_val = $this->transField($val);
            }

            $sql = str_replace('(:' . $key . ')', $used_val, $sql);
        }

        return $sql;
    }

    public function putFieldPlain(string $sql, array $fields): string{
        foreach($fields as $key => $val){
            if(is_array($val)){
                $all_vals = [];
                foreach($val as $va)
                    $all_vals[] = vsprintf('`%s`', [$va]);
                $used_val = implode(', ', $all_vals);
            }else{
                $used_val = vsprintf('`%s`', [$val]);
            }

            $sql = str_replace('(:' . $key . ')', $used_val, $sql);
        }

        return $sql;
    }

    public function putFrom($sql){
        $self_sql = 'FROM (:table)';
        $main_table = $this->getTable();
        $main_db    = $this->getDBName();

        $tables = [
            'table' => $main_table
        ];
        $fields = [];

        if($this->used_join){
            $this->used_join = array_unique($this->used_join);
            foreach($this->used_join as $index => $field){
                $chain = $this->chains[$field];

                $pcl_chain = 'chain_' . $index;
                $pcl_chain_parent = 'chain_parent_' . $index;
                $pcl_chain_own = 'chain_own_' . $index;

                $chain_model = $chain['model'];
                $chain_table = $chain_model::getTable();
                $chain_field = $chain['field'];
                $chain_db    = $chain_model::getDBName();

                $field_self  = $field;
                if(isset($chain['self']))
                    $field_self = $chain['self'];

                $join_type   = 'LEFT JOIN';
                if(isset($chain['join']))
                    $join_type = $chain['join'] . ' JOIN';
                $join_type = ' ' . trim($join_type) . ' ';

                $fields[$pcl_chain_parent] = [[$field_self, $main_table, $main_db]];
                $fields[$pcl_chain_own] = [[$chain_field, $chain_table, $chain_db]];

                $tables[$pcl_chain] = [$chain_table, $chain_model];

                $self_sql.= $join_type . '(:' . $pcl_chain . ')'
                    . ' ON (:' . $pcl_chain_parent . ') = (:' . $pcl_chain_own . ')';
            }
        }

        $self_sql = $this->putTable($self_sql, $tables);
        $self_sql = $this->putField($self_sql, $fields);
        return str_replace('(:from)', $self_sql, $sql);
    }

    public function putLimit(int $rpp=12, int $page=1): string{
        if(!$rpp)
            return '';

        $sql = ' LIMIT ' . $rpp;
        $offset = 0;

        $page--;
        $offset = $page * $rpp;
        if($offset)
            $sql.= ' OFFSET ' . $offset;

        return $sql;
    }

    public function putOrder(array $orders): string{
        if(!$orders)
            return '';

        $all_sort = [];

        foreach($orders as $field => $target){
            $tgr_text = $target ? 'ASC' : 'DESC';
            if($field === 'RAND()')
                $all_sort[] = $field;
            else{
                $all_sort[] = $this->putField('(:field) ' . $tgr_text, [
                    'field' => $field
                ]);
            }
        }

        $self_sql = implode(', ', $all_sort);

        return ' ORDER BY ' . $self_sql;
    }

    public function putTable(string $sql, array $values): string{
        foreach($values as $key => $val){
            $used_val = $this->transTable($val);
            $sql = str_replace('(:' . $key . ')', $used_val, $sql);
        }

        return $sql;
    }
    
    public function putValue(string $sql, array $values): string{
        foreach($values as $key => $val){
            $used_val = $this->transVal($val);
            if(is_array($used_val))
                $used_val = '( ' . implode(', ', $used_val) . ' )';
            $sql = str_replace('(:'.$key.')', $used_val, $sql);
        }

        return $sql;
    }
    
    public function putWhere(string $sql, array $where, string $combiner='AND', $group=true): string{
        $conds = [];

        $index = 0;

        $all_fields = [];
        $all_values = [];

        if(isset($where['q'])){
            if($this->q_field){
                $where['$or_q_field'] = [];
                foreach($this->q_field as $field){
                    $where['$or_q_field'][] = [
                        $field => ['__like', $where['q'], 'both']
                    ];
                }
            }
            unset($where['q']);
        }

        foreach($where as $field => $value){
            if(substr($field, 0, 3) === '$or'){
                $self_conds = [];
                foreach($value as $val)
                    $self_conds[] = $this->putWhere('(:where)', $val, 'AND', false);
                $conds[] = '( ' . implode(' ) OR ( ', $self_conds) . ' )';
            
            }elseif(substr($field, 0, 4) === '$and'){
                $self_conds = [];
                foreach($value as $val)
                    $self_conds[] = $this->putWhere('(:where)', $val, 'AND', false);
                $conds[] = '( ' . implode(' ) AND ( ', $self_conds) . ' )';
            
            }else{
                $plc_op = '=';
                $plc_field = 'fld_' . $index;
                $plc_value = 'val_' . $index;

                $used_field = $field;
                $used_value = $value;

                $use_field = true;
                $use_value = true;
                $add_format= true;

                $format = '(:%s) %s (:%s)';
                $format_vals = [$plc_field, $plc_op, $plc_value];

                if(is_array($used_value)){
                    if(!$used_value)
                        continue;

                    if(end($used_value) === '__!'){
                        array_pop($used_value);
                        $plc_op = 'IN';
                        
                    }else{
                        $used_value_count = count($used_value);

                        if($used_value[0] === '__between' && $used_value_count === 3){
                            
                            $plc_op = 'BETWEEN';
                            $format = '(:%s) %s (:%s) AND (:%s)';

                            $plc_value_min = 'val_' . $index . '_min';
                            $plc_value_max = 'val_' . $index . '_max';

                            $format_vals[2] = $plc_value_min;
                            $format_vals[3] = $plc_value_max;

                            $all_values[$plc_value_min] = $used_value[1];
                            $all_values[$plc_value_max] = $used_value[2];

                            $use_value = false;

                        }elseif($used_value[0] === '__like' && $used_value_count > 1 && $used_value_count < 5){
                            
                            $plc_op     = 'LIKE';
                            $perc_pos   = $used_value[2] ?? 'both';
                            $suffix     = $used_value[3] ?? null;
                            $used_value = $used_value[1];

                            if(is_array($used_value)){
                                $add_format = false;

                                $self_where = ['$or' => []];
                                foreach($used_value as $val)
                                    $self_where['$or'][] = [$field => ['__like', $val, $perc_pos, $suffix]];

                                $self_combiner = $suffix === 'NOT' ? 'AND' : 'OR';
                                $self_sql = $this->putWhere('(:where)', $self_where, $self_combiner, false);
                                $conds[] = $self_sql;

                            }else{
                                if($suffix)
                                    $plc_op = $suffix . ' LIKE';

                                if($perc_pos === 'left')
                                    $used_value = '%' . $used_value;
                                elseif($perc_pos === 'right')
                                    $used_value = $used_value . '%';
                                elseif($perc_pos === 'both')
                                    $used_value = '%' . $used_value . '%';
                            }

                        }elseif($used_value[0] === '__op'
                            && $used_value_count === 3
                            && in_array($used_value[1], $this->operators)){
                            
                            $plc_op     = $used_value[1];
                            $used_value = $used_value[2];

                            if(is_null($used_value)){
                                if($plc_op === '!=')
                                    $plc_op = 'IS NOT';
                                else
                                    $plc_op = 'IS';
                            }
                        }else{
                            $plc_op = 'IN';
                        }
                    }

                }elseif(is_null($used_value)){
                    $plc_op = 'IS';
                }

                if($plc_op != $format_vals[1])
                    $format_vals[1] = $plc_op;

                if($add_format)
                    $conds[] = vsprintf($format, $format_vals);
                if($use_field)
                    $all_fields[$plc_field] = $used_field;
                if($use_value)
                    $all_values[$plc_value] = $used_value;
            }

            $index++;
        }

        $nl = ''; //PHP_EOL;
        if($group){
            $sql_format = '( ' . implode(' ) ' . $nl . $combiner . ' ( ', $conds) . ' )';
        }else{
            $sql_format = implode($nl . ' ' . $combiner . ' ', $conds);
        }

        $sql = str_replace('(:where)', $sql_format, $sql);

        $sql = $this->putField($sql, $all_fields);
        $sql = $this->putValue($sql, $all_values);

        return $sql;
    }
    
    public function query(string $sql, string $target='read'){
        $this->last_query = $sql;
        $this->used_join = [];
        $this->last_connection_name = $target;

        $conn = $this->getConnection($target);
        $result = mysqli_query($conn, $sql);

        if(is_bool($result))
            return $result;

        $rows = $result->fetch_all(MYSQLI_ASSOC);
        mysqli_free_result($result);

        array_walk($rows, function(&$a){ $a = (object)$a; });

        return $rows;
    }
    
    public function remove(array $where=[]): bool{
        $sql = 'DELETE (:from)';

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        return !!$this->query($sql, 'write');
    }

    public function rollback(string $conn='write'): bool{
        $conn = $this->getConnection($conn);
        return mysqli_rollback($conn);
    }
    
    public function set(array $fields, array $where=[]): bool{
        if(!$fields)
            return true;
        $sql = 'UPDATE (:table) SET ';

        $index = 0;
        $val_fields = [];
        $val_values = [];
        $sets = [];

        foreach($fields as $field => $value){
            $plc_field = 'fld_' . $index;
            $plc_value = 'val_' . $index;

            $used_value = $value;

            $format = '(:' . $plc_field . ') = (:' . $plc_value . ')';

            if(is_array($value)){
                if(!$value)
                    $used_value = json_encode($value);
                else{
                    $ops = $value[0];
                    if($ops === '__inc'){
                        $format = '(:' . $plc_field . ') = (:' . $plc_field . ') + (:' . $plc_value . ')';
                        $used_value = $value[1];
                    }elseif($ops === '__dec'){
                        $format = '(:' . $plc_field . ') = (:' . $plc_field . ') - (:' . $plc_value . ')';
                        $used_value = $value[1];
                    }else{
                        $used_value = json_encode($value);
                    }
                }
            }

            $val_fields[$plc_field] = $field;
            $val_values[$plc_value] = $used_value;

            $sets[] = $format;
            $index++;
        }

        $sql.= implode(', ', $sets);

        $sql = $this->putTable($sql, [
            'table' => $this->getTable()
        ]);

        $sql = $this->putField($sql, $val_fields);
        $sql = $this->putValue($sql, $val_values);
        
        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        return !!$this->query($sql, 'write');
    }
    
    public function sum(string $field, array $where=[]){
        $sql = $this->putField('SELECT SUM((:field)) AS (:result) (:from)', [
            'field' => $field
        ]);
        $sql = $this->putFieldPlain($sql, ['result' => 'result']);

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return 0;
        $result = $result[0]->result;

        if(false !== strstr($result, '.'))
            return (double)$result;
        return (int)$result;
    }

    public function sumFs(array $fields, array $where=[]){
        $flds = [];
        foreach($fields as $field){
            $fld = $this->putField('SUM((:field)) AS (:alt)', [
                'field' => $field
            ]);
            $fld = $this->putFieldPlain($fld, ['alt' => $field]);
            $flds[] = $fld;
        }

        $sql = 'SELECT ' . implode(', ', $flds) . ' (:from)';

        if($where)
            $sql.= $this->putWhere(' WHERE (:where)', $where);

        $sql = $this->putFrom($sql);

        $result = $this->query($sql, 'read');
        if(!$result)
            return null;

        return $result[0];
    }
    
    public function truncate(string $target='write'): bool{
        $sql = $this->putTable('TRUNCATE (:table);', [
            'table' => $this->getTable()
        ]);

        return $this->query($sql, 'write');
    }
    
}
