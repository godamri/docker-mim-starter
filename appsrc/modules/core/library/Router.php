<?php
/**
 * Router parser
 * @package core
 * @version 0.0.3
 */

namespace Mim\Library;

class Router
{

    static $all_routes;
    static $all_gates;

    private static function _matchFilter(object $filter, string $target, string $sep='/', bool $next=false): ?array{
        $result = [
            'params' => []
        ];
        
        if($filter->_type === 'text'){
            $value_len  = strlen($filter->value);
            $target_len = strlen($target);
            
            if($next && $value_len !== $target_len){
                if(substr($target, 0, $value_len) !== $filter->value)
                    return null;
                
                // match the next character
                if(substr($filter->value, -1) !== $sep){
                    $filter_value_n = ltrim($filter->value . $sep);
                    $filter_value_len = strlen($filter_value_n);
                    if(substr($target, 0, $filter_value_len) !== $filter_value_n)
                        return null;
                }
            }else{
                if($filter->value != $target)
                    return null;
            }
        }elseif($filter->_type === 'regex'){
            if(!preg_match($filter->_value, $target, $match))
                return null;
            foreach($filter->params as $par => $type){
                $result['params'][$par] = $match[$par] ?? '';
                if($type === 'rest')
                    $result['params'][$par] = explode($sep, $result['params'][$par]);
            }
        }else{
            return null;
        }
        
        return $result;
    }

    static function parseGate(array $options): ?array{
        self::$all_gates = $gates = include BASEPATH . $options['file_gate'];

        if(!$gates)
            return null;

        $is_cli = $options['is_cli'];
        $r_host = $options['req_host'];
        $r_path = $options['req_path'];

        $result = [
            'gate' => null,
            'param' => (object)[]
        ];

        foreach($gates as $gate){
            $m_host = [];
            
            if($is_cli !== ($gate->host->value === 'CLI'))
                continue;

            if ($gate->host->value === '*') {
                $gate->host->value = $_SERVER['HTTP_HOST'];
            }

            if ($gate->asset->host === '*') {
                $gate->asset->host = $_SERVER['HTTP_HOST'];
            }

            $sep = $is_cli ? ' ' : '.';
            if(!$is_cli && null === ($m_host = self::_matchFilter($gate->host, $r_host, $sep)))
                continue;
            
            $sep = $is_cli ? ' ' : '/';
            if(null === ($m_path = self::_matchFilter($gate->path, $r_path, $sep, true)))
                continue;
            
            if($m_host){
                foreach($m_host['params'] as $k => $val)
                    $result['param']->$k = $val;
            }
            
            foreach($m_path['params'] as $k => $val)
                $result['param']->$k = $val;
            
            $result['gate'] = $gate;
            break;
        }
        
        if($result['gate'])
            return $result;
        return null;
    }

    static function parseRoute(array $options): ?array{
        $result = [
            'route' => null,
            'param' => (object)[],
            'handlers' => []
        ];

        $is_cli   = $options['is_cli'];
        $r_path   = $options['req_path'];
        $r_method = $options['req_method'];
        $p_sep    = $is_cli ? ' ' : '/';
        $gate     = $options['req_gate'];

        self::$all_routes = $gates = include BASEPATH . $options['file_routes'];

        if($gates && property_exists($gates, $gate->name)){
            $routes = $gates->{$gate->name};
            foreach($routes as $route){
                if(!$is_cli && !in_array($r_method, $route->_method))
                    continue;
                
                $sep = $gate->host->value === 'CLI' ? ' ' : '/';
                if(null === ($m_path = self::_matchFilter($route->path, $r_path, $sep)))
                    continue;
                
                $result['route'] = $route;
                
                // set the params
                foreach($m_path['params'] as $k => $v)
                    $result['param']->$k = $v;
                break;
            }
        }

        if($result['route'])
            return $result;
        return null;
    }
}
