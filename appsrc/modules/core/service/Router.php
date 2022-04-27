<?php
/**
 * Router service
 * @package core
 * @version 0.0.1
 */

namespace Mim\Service;

class Router extends \Mim\Service
{
    
    private $_params = [];

    public function asset(string $gate, string $path, int $version=0): ?string{
        $gates = \Mim\Library\Router::$all_gates;
        $used_gate = null;
        foreach($gates as $gt){
            if($gt->name != $gate)
                continue;
            $used_gate = $gt;
            break;
        }

        if(!$used_gate){
            trigger_error('Gate named `' . $gate . '` not found');
            return null;
        }

        $scheme = $this->config->secure ? 'https://' : 'http://';

        $result = $scheme . $used_gate->asset->host;
        foreach($this->_params as $pk => $pv)
            $result = str_replace('(:' . $pk . ')', $pv, $result);

        $result.= '/theme/' . $gate . '/static/' . $path;
        if($version)
            $result.= '?v=' . $version;

        return $result;
    }

    public function exists(string $name): bool{
        $routes = \Mim\Library\Router::$all_routes;
        return isset($routes->_gateof->$name);
    }
    
    public function getParam(string $name): ?string{
        return $this->_params[$name] ?? null;
    }
    
    public function setParam(string $name, string $value): void{
        $this->_params[$name] = $value;
    }
    
    public function to(string $name, array $params=[], array $query=[]): ?string{
        $gates  = \Mim\Library\Router::$all_gates;
        $routes = \Mim\Library\Router::$all_routes;
        $gate   = $routes->_gateof->$name ?? null;
        $route  = $routes->$gate->$name ?? null;

        if(!$gate)
            trigger_error('Router named `' . $name . '` not found');

        $used_gate = null;
        foreach($gates as $gt){
            if($gt->name != $gate)
                continue;
            $used_gate = $gt;
            break;
        }

        if($used_gate->host->value === 'CLI')
            $result = $route->path->value;
        else{
            $scheme = $this->config->secure ? 'https://' : 'http://';
            $result = $scheme . $used_gate->host->value . $route->path->value;
        }

        $used_params = array_replace($this->_params, $params);

        foreach($used_params as $pk => $pv){
            if(is_array($pv))
                $pv = implode('/', array_map('urlencode', $pv));
            else
                $pv = urlencode($pv);
            $result = str_replace('(:' . $pk . ')', $pv, $result);
        }

        if($query)
            $result.= '?' . http_build_query($query);
        return $result;
    }
}