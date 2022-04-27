<?php
/**
 * Tool tools
 * @package cli
 * @version 0.0.6
 */

namespace Cli\Controller;

use Cli\Library\Bash;

class ToolController extends \Cli\Controller
{
    public function autocompleteAction(): void{
        $command = $this->req->param->command ?? [];

        if($command){
            $lidx = count($command) - 1;
            $lval = $command[$lidx];
            $command[$lidx] = preg_replace('!NULL$!', '', $lval);
        }

        $params = implode(' ', $command);
        if($params === '-')
            $params = '';

        $rules = (array)$this->config->cli->autocomplete;

        uasort($rules, function($a, $b){
            return $b->priority - $a->priority;
        });

        $print = 1;

        foreach($rules as $regex => $handler){
            if(!preg_match($regex, $params))
                continue;
            $hdr = $handler->handler;

            $class = $hdr->class;
            $method = $hdr->method;

            $print = $class::$method($command);
            break;
        }

        Bash::echo($print);
    }

    public function helpAction(): void{
        $gates  = include BASEPATH . '/etc/cache/gates.php';
        $routes = include BASEPATH . '/etc/cache/routes.php';
        
        Bash::echo('Usage: mim [command] [options...]');
        Bash::echo('');
        
        foreach($gates as $gate){
            if($gate->host->value !== 'CLI')
                continue;
            $base = $gate->path->value;
            
            Bash::echo('' . $gate->name . '');
            
            foreach($routes->{$gate->name} as $route){
                $skip_help = $route->skipHelp ?? false;
                if($skip_help)
                    continue;
                $pref = '  ' . trim($route->path->value);
                $pref = str_pad($pref, 40, ' ');
                $pref.= $route->info ?? 'No info provided';
                Bash::echo($pref);
            }

            Bash::echo('');
        }
    }
    
    public function versionAction(): void{
        Bash::echo($this->config->name);
        $dirs = \Mim\Library\Fs::scan(BASEPATH . '/modules');
        sort($dirs);
        foreach($dirs as $dir){
            $dir_abs = BASEPATH . '/modules/' . $dir;
            if(!is_dir($dir_abs))
                continue;
            $mod = '- ' . $dir;
            $mod_config = include $dir_abs . '/config.php';
            $mod.= ' ' . $mod_config['__version'];
            Bash::echo($mod);
        }
    }
}