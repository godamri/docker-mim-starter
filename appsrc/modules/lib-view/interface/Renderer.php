<?php
/**
 * Renderer interface
 * @package lib-view
 * @version 0.0.1
 */

namespace LibView\Iface;

interface Renderer
{
    public function render(string $view, array $params=[], string $gate=null): ?string;
}