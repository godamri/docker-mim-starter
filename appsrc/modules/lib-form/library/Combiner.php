<?php
/**
 * Combiner
 * @package lib-form
 * @version 0.4.0
 */

namespace LibForm\Library;

class Combiner
{
    private $id;
    private $format;
    private $field_options = [];
    private $options;

    private $new_object;
    private $old_object;
    private $new_chain_values = [];
    private $old_chain_values = [];

    private $ex_modules = [];
    private $ex_perms = [];

    public function __construct(int $id, array $options, string $format=null){
        $this->id = $id;
        $this->options = $options;

        if($format){
            $f_conf = \Mim::$app->config->libFormatter->formats->$format ?? NULL;
            if(!$f_conf)
                trigger_error('Formatter named `' . $format . '` not found');
            $this->format = $f_conf;
        }
    }

    private function checkModule(string $name=null): bool{
        if(!$name)
            return true;
        $this->ex_modules[$name] = $this->ex_modules[$name] ?? module_exists($name);
        return $this->ex_modules[$name];
    }

    private function checkPerms(string $perm=null): bool{
        if(!$perm || !$this->checkModule('lib-user-perm'))
            return true;

        $this->ex_perms[$perm] = $this->ex_perms[$perm] ?? \Mim::$app->user->can_i->{$perm};
        return $this->ex_perms[$perm];
    }

    public function getOptions(): array{
        return $this->field_options;
    }

    public function prepare(object $object): object{
        foreach($this->options as $field => $opts){
            $f_module = $opts[0];
            $f_perms  = $opts[1];
            $f_type   = $opts[2];
            $f_fetch  = $opts[3] ?? 'active';
            $f_label  = $opts[4] ?? 'name';
            $f_parent = $opts[5] ?? NULL;
            $f_sort   = $opts[6] ?? NULL;

            if(!$this->checkModule($f_module))
                continue;
            if(!$this->checkPerms($f_perms))
                continue;

            $value = $object->$field ?? NULL;

            switch($f_type){

                case 'array':
                    if(!$value)
                        $object->$field = [];
                    else
                        $object->$field = json_decode($value);
                    break;

                case 'format':
                    $format = $this->format->$field ?? NULL;
                    if(!$format)
                        trigger_error('Format for field `' . $field . '` not found');

                    $opt_model = null;
                    $opt_field = null;
                    $opt_value = null;

                    switch($format->type){
                        case 'chain':
                            $chain   = $format->chain;
                            $c_model = $chain->model;
                            $c_name  = $c_model->name;
                            $c_field = $c_model->field;
                            $c_ident = $chain->identity;

                            $target  = $format->model;
                            $t_name  = $opt_model = $target->name;
                            $t_field = $opt_field = $target->field;

                            $object->$field = [];

                            if($this->id){
                                $enums = $c_name::get([$c_field => $this->id]);
                                if($enums){
                                    $object->$field = array_column($enums, $c_ident);
                                    $this->old_chain_values[$field] = $object->$field;
                                }
                            }

                            $opt_value = $object->$field = \Mim::$app->req->get($field, $object->$field);
                            break;

                        case 'object':
                            if(!isset($object->$field))
                                $object->$field = null;

                            $object->$field = \Mim::$app->req->get($field, $object->$field);

                            $model     = $format->model;
                            $opt_model = $model->name;
                            $opt_field = $model->field;

                            $opt_value = $object->$field;

                            break;
                    }

                    // get the options
                    if($opt_model){
                        $fopts = [];

                        if($f_fetch !== 'none'){
                            $cond = [];
                            if($f_fetch === 'active')
                                $cond[$opt_field] = $opt_value;
                            elseif(is_array($f_fetch))
                                $cond = $f_fetch;
                            $enums = $opt_model::get($cond, 0, 1, [$f_label=>true]);

                            if($enums){
                                if(!$f_parent)
                                    $fopts = array_column($enums, $f_label, $opt_field);
                                else{
                                    foreach($enums as $enum){
                                        $fopts[] = (object)[
                                            'value'  => $enum->$opt_field,
                                            'label'  => $enum->$f_label,
                                            'parent' => $enum->$f_parent
                                        ];
                                    }
                                }
                            }
                        }

                        $this->field_options[$field] = $fopts;
                    }
                    break;

                case 'format-json':
                    $format = $this->format->$field ?? NULL;
                    if(!$format)
                        trigger_error('Format for field `' . $field . '` not found');

                    switch($format->type){
                        case 'chain':
                            $chain   = $format->chain;
                            $c_model = $chain->model;
                            $c_name  = $c_model->name;
                            $c_field = $c_model->field;
                            $c_ident = $chain->identity;

                            $target  = $format->model;
                            $t_name  = $opt_model = $target->name;
                            $t_field = $opt_field = $target->field;

                            $object->$field = '[]';

                            if($this->id){
                                $enums = $c_name::get([$c_field => $this->id]);
                                if($enums){
                                    $this->old_chain_values[$field] = array_column($enums, $c_ident);
                                    $ori_object = $t_name::get([$t_field=>$this->old_chain_values[$field]]);
                                    if($ori_object){
                                        $json_value = [];
                                        foreach($ori_object as $enum){
                                            $tobject = [];
                                            foreach($f_parent as $tfld => $tval){
                                                $tfval = $tval;
                                                if(substr($tval,0,1) === '.')
                                                    $tfval = $enum->{substr($tval,1)};
                                                $tobject[$tfld] = $tfval;
                                            }
                                            $json_value[] = $tobject;
                                        }
                                        $object->$field = json_encode($json_value);
                                    }
                                }
                            }

                            $opt_value = $object->$field = \Mim::$app->req->get($field, $object->$field);
                            break;
                    }
                    break;

                case 'json':
                    if(!$value)
                        $value = '[]';
                    $object->$field = json_decode($value);

                    foreach($object->$field as $fld => $val)
                        $object->{$field . '-' . $fld} = $val;
                    break;
            }
        }

        $this->old_object = clone $object;
        return $object;
    }

    public function finalize(object $object): object{
        $this->new_object = clone $object;
        $mod_object = clone $object;

        foreach($this->options as $field => $opts){
            $f_module = $opts[0];
            $f_perms  = $opts[1];
            $f_type   = $opts[2];
            $f_fetch  = $opts[3] ?? 'active';
            $f_label  = $opts[4] ?? 'name';
            $f_parent = $opts[5] ?? NULL;
            $f_sort   = $opts[6] ?? NULL;

            if(!$this->checkModule($f_module))
                continue;
            if(!$this->checkPerms($f_perms))
                continue;

            if(!isset($mod_object->$field))
                $mod_object->$field = NULL;

            switch($f_type){
                case 'array':
                    if($mod_object->$field)
                        $mod_object->$field = json_encode($mod_object->$field);
                    break;

                case 'format':
                    $format = $this->format->$field ?? NULL;
                    if(!$format)
                        trigger_error('Format for field `' . $field . '` not found');

                    switch($format->type){
                        case 'chain':
                            $this->new_chain_values[$field] = $mod_object->$field ?? [];
                            unset($mod_object->$field);
                        break;
                    }
                    break;

                case 'format-json':
                    $format = $this->format->$field ?? NULL;
                    if(!$format)
                        trigger_error('Format for field `' . $field . '` not found');

                    if(!$mod_object->$field )
                        $mod_object->$field  = '[]';
                    $result = json_decode(($mod_object->$field ?? '[]'));

                    switch($format->type){
                        case 'chain':
                            $this->new_chain_values[$field] = array_column($result, $f_label);
                            unset($mod_object->$field);
                        break;
                    }
                    break;

                case 'json':
                    $mod_object->$field = (object)[];
                    $fld_len = strlen($field);
                    $fld_pan = $fld_len + 1;
                    foreach($mod_object as $fld => $val){
                        if(substr($fld, 0, $fld_len) !== $field)
                            continue;
                        $prop_name = substr($fld, $fld_pan);
                        if(!$prop_name)
                            continue;
                        $mod_object->$field->$prop_name = $val;
                        unset($mod_object->$fld);
                    }
                    $mod_object->$field = json_encode($mod_object->$field);
                    break;
            }
        }

        return $mod_object;
    }

    public function save(int $id, int $user): void{
        if(!$this->new_chain_values)
            return;

        // here we save all the chains
        foreach($this->new_chain_values as $field => $value){
            $format = $this->format->$field ?? NULL;
            if(!$format)
                trigger_error('Format for field `' . $field . '` not found');

            $chain   = $format->chain;
            $c_model = $chain->model;
            $c_name  = $c_model->name;
            $c_field = $c_model->field;
            $c_ident = $chain->identity;

            $target  = $format->model;
            $t_name  = $opt_model = $target->name;
            $t_field = $opt_field = $target->field;

            $old_val = $this->old_chain_values[$field] ?? [];
            $new_val = $value ?? [];

            $add_val = array_values(array_diff((array)$new_val, (array)$old_val));
            $rem_val = array_values(array_diff((array)$old_val, (array)$new_val));

            if($rem_val)
                $c_name::remove([$c_ident=>$rem_val, $c_field=>$id]);

            if($add_val){
                $add_qry = [];
                foreach($add_val as $val){
                    $adval = [
                        $c_ident => $val,
                        $c_field => $id
                    ];
                    if($user)
                        $adval['user'] = $user;
                    $add_qry[] = $adval;
                }
                if($add_qry)
                    $c_name::createMany($add_qry);
            }
        }
    }
}
