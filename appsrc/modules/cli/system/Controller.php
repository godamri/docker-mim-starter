<?php
/**
 * CLI Base controller
 * @package cli
 * @version 0.0.4
 */

namespace Cli;

use Cli\Library\Bash;

class Controller extends \Mim\Controller implements \Mim\Iface\GateController
{
    public function show404(): void{
        $this->show404Action();
    }
    
    public function show404Action(): void{
        Bash::error('Unknow action, please hit `mim help` for list of actions');
    }
    
    public function show500(object $error): void{
        Bash::echo($error->text);
        Bash::echo($error->file . ' (' . $error->line . ')');
        if(isset($error->trace)){
            foreach($error->trace as $trace){
                if(isset($trace['file']))
                    Bash::echo($trace['file'] . ' (' . $trace['line'] . ')', 4);
            }
        }
    }
    
    public function show500Action(): void{
        $this->show500(\Mim\Library\Logger::$last_error);
    }
}