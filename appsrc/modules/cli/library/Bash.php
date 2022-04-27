<?php
/**
 * Bash handler
 * @package cli
 * @version 0.0.2
 */

namespace Cli\Library;

class Bash
{

    /*
     * [
     *  text::string
     *  type::string 'any' [ any, number, bool, regex ]
     *  options::array [] [ key=>value ]
     *  regex::string ''
     *  space::int 0
     *  required::bool false
     * ]
     */
    static function ask(array $opts){
        $opt = (object)[
            'text'      => $opts['text'],
            'type'      => $opts['type'] ?? 'any',
            'options'   => $opts['options'] ?? [],
            'regex'     => $opts['regex'] ?? '',
            'space'     => $opts['space'] ?? 0,
            'required'  => $opts['required'] ?? false,
            'default'   => $opts['default'] ?? ''
        ];
        
        $rl_text = '';
        $rl_space= 0;
        if($opt->options){
            self::echo($opt->text . ': ', $opt->space);
            foreach($opt->options as $key => $val)
                self::echo(' ' . $key . ') ' . $val, $opt->space);
            $rl_text = ' Your choice ';
            $rl_space= $opt->space;
        }else{
            $suffix = '';
            if($opt->type === 'bool')
                $suffix = $opt->default ? ' (Y/n)' : ' (y/N)';
            elseif($opt->default)
                $suffix = ' (' . $opt->default . ')';

            $rl_text = $opt->text . $suffix;
            $rl_space= $opt->space;
        }
        
        $rl_qst = str_repeat(' ', $rl_space) . $rl_text;
        $ans = readline($rl_qst . ': ');
        if($ans === '')
            $ans = $opt->default;
        else
            readline_add_history($ans);
        
        if(!$ans && $opt->required){
            self::error('Please provide any value', false, $opt->space);
            return self::ask($opts);
        }
        
        // validate the value
        $error = false;
        
        switch($opt->type){
            case 'number':
                if(!is_numeric($ans))
                    $error = 'Provided value is not numeric';
                $ans = (int)$ans;
                break;
            case 'bool':
                if(!is_bool($ans))
                    $ans = strtolower($ans) === 'y';
                break;
            case 'regex':
                if(!preg_match($opt->regex, $ans))
                    $error = 'Provide value is not acceptable';
                break;
        }
        
        if(!$error && $opt->options && !isset($opt->options[$ans]))
            $error = 'Selected value is not in options';
        
        if($error){
            self::error($error, false, $opt->space);
            return self::ask($opts);
        }
        
        return $ans;
    }
    
    static function echo($str, int $space=0, bool $eol=true): void{
        echo str_repeat(' ', $space);
        print_r($str);
        if($eol)
            echo PHP_EOL;
    }
    
    static function error(string $str, bool $exit=true, int $space=0): void{
        $text = 'Error: ' . $str;
        self::echo($text, $space);
        if($exit)
            exit;
    }

    static function json($data): void{
        $data = json_encode($data);

        self::echo($data);
    }
    
    static function run(string $cmd): object{
        $code = 0;
        $result = (object)[
            'text' => system($cmd, $code)
        ];
        
        $result->code = $code;
        
        return $result;
    }
}