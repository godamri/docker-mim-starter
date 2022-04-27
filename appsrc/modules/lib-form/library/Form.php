<?php
/**
 * Form
 * @package lib-form
 * @version 0.0.1
 */

namespace LibForm\Library;

use LibValidator\Library\Validator;
use LibView\Library\View;

class Form
{

    private $csrf;

    private $errors = [];

    private $form;

    private $object;

    private $result;

    private $rules;


    public function __construct(string $name){
        $this->object = (object)[];
        $this->result = (object)[];

        $this->form = $name;
        
        $forms = \Mim::$app->config->libForm->forms;
        if(!isset($forms->$name))
            trigger_error('Form named `' . $name . '` not found');

        $this->rules = (object)[];
        $rules = (array)$forms->$name;
        foreach($rules as $name => $field){
            $field->name = $name;
            if(isset($field->modules)){
                $field->modules = (array)$field->modules;
                foreach($field->modules as $mod){
                    if(!module_exists($mod))
                        continue 2;
                }
            }

            $this->rules->$name = $field;
        }
    }

    public function addError(string $field, string $code, string $text=null): void{
        if(!$text){
            $locale = \Mim::$app->config->libValidator->errors->$code ?? '';
            if($locale)
                $text = lang($locale);
        }

        $error = (object)[
            'field' => $field,
            'code'  => $code,
            'text'  => $text
        ];

        $this->errors[$field] = $error;
    }
    
    public function csrfField(string $name='CSRFToken'): string{
        $token = $this->csrfToken();
        return '<input type="hidden" value="' . $token . '" name="' . $name . '">';
    }

    public function csrfTest(string $name='CSRFToken'): bool{
        $token = \Mim::$app->req->get($name);
        if(!$token)
            return false;

        $cname = 'csrf-' . $token;

        $cache = \Mim::$app->cache->get($cname);
        if(!$cache)
            return false;

        if($cache['form'] != $this->form)
            return false;

        if(module_exists('lib-user')){
            if($cache['user'] && $cache['user'] != \Mim::$app->user->id)
                return false;
        }

        \Mim::$app->cache->remove($cname);
        return true;
    }

    public function csrfToken(): string{
        if($this->csrf)
            return $this->csrf;

        $this->csrf = sha1(base64_encode(random_bytes(25)));

        $cname = 'csrf-' . $this->csrf;

        $data = [
            'form' => $this->form,
            'user' => 0
        ];
        if(module_exists('lib-user')){
            if(\Mim::$app->user->isLogin())
                $data['user'] = \Mim::$app->user->id;
        }

        \Mim::$app->cache->add($cname, $data, ( 60 * 60 * 2 ));

        return $this->csrf;
    }

    public function field(string $name, $options=null): string{
        if(!isset($this->rules->$name))
            trigger_error('Field `' . $name . '` under form `' . $this->form . '` is not exists');

        $value = $this->result->$name ?? null;
        if(\Mim::$app->req->method === 'GET')
            $value = $this->object->$name ?? null;

        $field_params = $this->rules->$name;
        
        $field_id = $this->getName() . '-fld-' . $name;
        $field_id = preg_replace('![^a-zA-Z0-9-]!', '-', $field_id);
        $field_id = preg_replace('!-+!', '-', $field_id);

        $params = [
            'id'         => $field_id,
            'field'      => $field_params,
            'options'    => $options,
            'value'      => $value,
            'form'       => $this,
            'error'      => $this->getError($name),
            'show_label' => !isset($field_params->nolabel) || !$field_params->nolabel,
            'rules'      => (object)$field_params->rules
        ];

        $view = 'form/field/' . $field_params->type;
        return View::render($view, $params);
    }

    public function fieldExists(string $name): bool
    {
        $fields = $this->getFields();

        return property_exists($fields, $name);
    }

    public function getError(string $field): ?object{
        return $this->errors[$field] ?? null;
    }

    public function getErrors(): array{
        return $this->errors;
    }

    public function getFields(): object{
        return $this->rules;
    }

    public function getName(): string{
        return $this->form;
    }

    public function getResult(): ?object{
        return $this->result;
    }

    public function hasError(): ?bool{
        return !!$this->errors;
    }

    public function setObject(object $object): void{
        $this->object = $object;
    }

    public function validate(object $object=null): ?object {
        if($object)
            $this->setObject($object);
        if(\Mim::$app->req->method === 'GET')
            return null;
        $to_validate = (object)\Mim::$app->req->get();
        list($result, $error) = Validator::validate($this->rules, $to_validate);

        $this->result = (object)$result;

        if($error){
            $this->errors = $error;
            return null;
        }

        return $this->result;
    }
}
