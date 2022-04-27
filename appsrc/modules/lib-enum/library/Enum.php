<?php
/**
 * Enum
 * @package lib-enum
 * @version 0.0.1
 */

namespace LibEnum\Library;

class Enum implements \JsonSerializable
{

    private $label;
    private $value;
    private $options;

    static function getOptions(string $name): ?array {
        return \Mim::$app->config->libEnum->enums->$name ?? null;
    }

    public function __construct(string $name, $value=null, string $value_type=null){
        $options = self::getOptions($name);
        if(!$options)
            return;
        $this->options = $options;
        if(is_null($value))
            return;

        if(!is_null($value_type)){
            switch($value_type){
                case 'int':
                    $value = (int)$value;
                    break;
                case 'str':
                    $value = (string)$value;
                    break;
            }
        }
        
        $this->value = $value;
        $this->label = $options[$value] ?? NULL;
    }

    public function __get($name) {
        if(!in_array($name, ['label','value','options']))
            return null;
        return $this->$name;
    }

    public function __toString(): string {
        return $this->label;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return ['label'=>$this->label, 'value'=>$this->value];
    }
}
