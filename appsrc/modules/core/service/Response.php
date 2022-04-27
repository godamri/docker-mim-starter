<?php
/**
 * Response service
 * @package core
 * @version 1.8.2
 */

namespace Mim\Service;

class Response extends \Mim\Service
{
    private $_cache = 0;
    private $_content = '';
    private $_cookies = [];
    private $_headers = [];
    private $_status = 200;
    
    public function addContent(string $text, bool $truncate=false): void{
        if($truncate)
            $this->_content = '';
        $this->_content.= $text;
    }
    
    public function addCookie(string $name, string $value, int $expires=604800): void{
        $this->_cookies[$name] = (object)[
            'name'    => $name,
            'value'   => $value,
            'expires' => $expires
        ];
    }
    
    public function addHeader(string $name, string $value, bool $append=true): void{
        if(!isset($this->_headers[$name]) || !$append)
            $this->_headers[$name] = [];
        $this->_headers[$name][] = $value;
    }
    
    public function getCache(): int{
        return $this->_cache;
    }
    
    public function getContent(): string{
        return $this->_content;
    }
    
    public function getCookie(string $name=null) {
        if($name)
            return $this->_cookies[$name] ?? null;
        return $this->_cookies;
    }
    
    public function getHeader(string $name=null): ?array{
        if($name)
            return $this->_headers[$name] ?? null;
        return $this->_headers;
    }
    
    public function getStatus(): int{
        return $this->_status;
    }
    
    public function redirect(string $url, int $code=302): void{
        if($code !== 200){
            $this->setStatus($code);
            $this->addHeader('Location', $url);
            $this->send();
            return;
        }
        
        ob_start();
        
        http_response_code(200);
        $tx = '<!DOCTYPE html>';
        $tx.= '<html><head>';
        $tx.= '<meta http-equiv="refresh" content="0; URL=\'' . $url . '\'" />';
        $tx.= '</head><body></body></html>';
        echo $tx;
        ob_end_flush();
        ob_flush();
        flush();
    }
    
    public function removeCache(): void{
        $this->_cache = 0;
    }
    
    public function removeContent(): void{
        $this->_content = '';
    }
    
    public function removeCookie(string $name=null): void{
        if(!$name)
            $this->_cookies = [];
        elseif(isset($this->_cookies[$name]))
            unset($this->_cookies[$name]);
    }
    
    public function removeHeader(string $name=null, string $value=null): void{
        if(!$name)
            $this->_headers = [];
        elseif(isset($this->_headers[$name])){
            if(is_null($value))
                unset($this->_headers[$name]);
            elseif(in_array($value, $this->_headers[$name])){
                $index = array_keys($this->_headers[$name], $value)[0];
                array_splice($this->_headers[$name], $index, 1);
            }
        }
    }

    public function render(string $view, array $params=[], string $gate=null): void{
        if(!module_exists('lib-view'))
            return;
        $content = \LibView\Library\View::render($view, $params, $gate);
        if(!is_null($content))
            $this->addContent($content);
    }
    
    public function send(bool $callback=true): void{
        $continue = true;

        if($callback){
            $callback = \Mim::$app->config->callback ?? null;
            if($callback){
                if(isset($callback->core->printing)){
                    foreach($callback->core->printing as $handler){
                        $class = $handler->class;
                        $method= $handler->method;

                        if(!$class::$method())
                            $continue = false;
                    }
                }
            }

            if(!$continue)
                return;
        }

        ob_start();
        
        // set status code
        http_response_code($this->_status);
        
        // set headers
        foreach($this->_headers as $name => $headers){
            $length = count($headers);
            foreach($headers as $value)
                header($name . ': ' . $value, 1 === $length);
        }

        // set cookies
        $s_cookie = \Mim::$app->config->secure;
        $s_domain = '.' . \Mim::$app->config->host;
        $s_domain = preg_replace('!:[0-9]+$!', '', $s_domain);
        foreach($this->_cookies as $name => $cookie){
            $copts = [
                'expires'  => $cookie->expires ? ( $cookie->expires + time() ) : 0,
                'path'     => '/',
                'domain'   => $s_domain,
                'secure'   => $s_cookie,
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            setcookie($name, $cookie->value, $copts);
        }
        
        // set content
        echo $this->_content;
        
        // send response
        ob_end_flush();
        ob_flush();
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
    
    public function setCache(int $expires): void{
        $this->_cache = $expires;
    }
    
    public function setStatus(int $status=200): void{
        $this->_status = $status;
    }
}
