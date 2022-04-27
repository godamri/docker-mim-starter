<?php
/**
 * Locatino embed object
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class LocationEmbed
{
    private $lat;
    private $long;

    public function __construct($lat, $long){
        $this->lat = $lat;
        $this->long= $long;
    }

    public function google(string $apikey){
        $loc = $this->lat . '%2C' . $this->long;
        $attrs = [
            'width' => 600,
            'height' => 450,
            'frameborder' => 0,
            'style' => 'border:0',
            'src' => 'https://www.google.com/maps/embed/v1/search?q=' . $loc . '&key=' . $apikey,
            'allowfullscreen' => null
        ];

        return '<iframe' . to_attr($attrs) . '></iframe>';
    }
}