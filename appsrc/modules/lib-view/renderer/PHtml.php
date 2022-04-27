<?php
/**
 * PHtml renderer
 * @package lib-view
 * @version 0.0.1
 */

namespace LibView\Renderer;

class PHtml implements \LibView\Iface\Renderer
{
    private $last_gate = null;
    private $used_params = [];

    private function getViewFile(string $gate, string $file): ?string{
        $view_path = 'theme/' . $gate . '/' . $file . '.phtml';
        $view_file = BASEPATH . '/' . $view_path;

        if(!is_file($view_file)){
            trigger_error('View file `' . $view_path. '` not found');
            return null;
        }
        return $view_file;
    }

    private function getUsedGate(string $gate=null): string{
        if($gate)
            return $gate;
        return $this->last_gate;
    }

    public function __get($name){
        return \Mim::$app->$name;
    }

    public function asset(string $path, int $version=0, string $gate=null): string{
        $used_gate = $this->getUsedGate($gate);
        return \Mim::$app->router->asset($used_gate, $path, $version);
    }
    
    public function partial(string $view, array $params=[], string $gate=null): void{
        $used_gate = $this->getUsedGate($gate);
        if(!($view_file = $this->getViewFile($used_gate, $view)))
            return;

        extract($this->used_params);
        extract($params);

        include $view_file;
    }

    public function render(string $view, array $params=[], string $gate=null): ?string{
        if(!$gate)
            $gate = \Mim::$app->req->gate->name;
        $this->last_gate = $gate;
        $this->used_params = $params;

        if(!($view_file = $this->getViewFile($gate, $view)))
            return null;

        $content = '';

        ob_start();
        extract($params);
        include $view_file;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}