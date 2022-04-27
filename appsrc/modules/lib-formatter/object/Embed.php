<?php
/**
 * Embed object
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class Embed implements \JsonSerializable
{
    private $_parsed;

    private $height;
    private $html;
    private $provider;
    private $url;
    private $value;
    private $template;
    private $width;

    private function _parse(){
        $regexs = [
            '/youtube\.com\/embed\/live_stream\?channel\=(?<id>.+)/'                        => 'youtube-live-stream',
            '/youtube.com\/embed\/(?<id>[\w_-]+)/i'                                         => 'youtube',
            '/youtube\.com(.+)v=(?<id>[\w_-]+)/'                                            => 'youtube',
            '/youtu\.be\/(?<id>[\w_-]+)/'                                                   => 'youtube',
            '/youtube-nocookie.com\/embed\/(?<id>[\w_\-]+)/i'                               => 'youtube',

            '/facebook\.com\/(?<user>[^\/]+)\/videos\/([^\/]+)\/(?<id>[0-9]+)/'             => 'facebook-video',
            '/facebook\.com\/(?<user>[^\/]+)\/videos\/(?<id>[0-9]+)/'                       => 'facebook-video',
            '/facebook\.com\/.+facebook\.com%2F(?<user>[^%]+)%2Fvideos%2F(?<id>[0-9]+)/'    => 'facebook-video',

            '/facebook\.com\/(?<user>[^\/]+)\/posts\/(?<id>[0-9]+)/'                        => 'facebook-post',
            '/facebook\.com\/.+facebook\.com%2F(?<user>[^%]+)%2Fposts%2F(?<id>[0-9]+)/'     => 'facebook-post',

            '/twitter-video.+twitter.com\/(?<user>[^\/]+)\/status\/(?<id>[0-9]+)/'          => 'twitter-video',

            '/twitter.com\/(?<user>[^\/]+)\/status\/(?<id>[0-9]+)/'                         => 'twitter-tweet',

            '/plus\.google\.com\/(?<user>[^\/]+)\/posts\/(?<id>[\w]+)/'                     => 'googleplus',

            '/(?<url>^.+\.(mp4|mpeg|ogg|webm))$/i'                                          => 'video',

            '/streamable\.com\/(s\/)?(?<id>[^\/\?]+)/'                                      => 'streamable',

            '/vidio.com\/embed\/(?<id>[\w\-]+)/'                                            => 'vidio',
            '/vidio.com\/watch\/(?<id>[\w\-]+)/'                                            => 'vidio',

            '/data-instgrm-captioned.+ instagram\.com\/p\/(?<id>\w+)/'                      => 'instagram-post',
            '/instagram\.com\/p\/(?<id>\w+)/'                                               => 'instagram-video',

            '/dailymail.co.uk\/video\/(?<user>[\w]+)\/video-(?<id>[0-9]+)/'                 => 'dailymail',
            '/dailymail.co.uk\/(?<user>[\w]+)\/video\/(?<id>[0-9]+)/'                       => 'dailymail',

            '/dailymotion.com\/embed\/video\/(?<id>[a-z0-9]+)/'                             => 'dailymotion',
            '/dailymotion.com\/video\/(?<id>[a-z0-9]+)/'                                    => 'dailymotion',
            '/dailymotion.com\/.+#video=(?<id>[a-z0-9]+)/'                                  => 'dailymotion',
            '/dai\.ly\/(?<id>[a-z0-9]+)/'                                                   => 'dailymotion',

            '/imdb\.com\/videoembed\/(?<id>[\w]+)/'                                         => 'imdb',
            '/imdb\.com\/videoplayer\/(?<id>[\w]+)/'                                        => 'imdb',
            '/imdb\.com\/.*\/videoplayer\/(?<id>[\w]+)/'                                    => 'imdb',
            '/imdb\.com\/video\/imdb\/(?<id>[\w]+)/'                                        => 'imdb',

            '/liveleak.com\/ll_embed\?f=(?<id>[\w\-]+)/'                                    => 'liveleak',

            '/vid.me\/e\/(?<id>[\w\-]+)/'                                                   => 'vidme',
            '/vid.me\/(?<id>[\w\-]+)/'                                                      => 'vidme',

            '/vimeo\.com\/(?<id>[0-9]+)/'                                                   => 'vimeo',
            '/vimeo\.com\/(.*)\/(?<id>[0-9]+)/'                                             => 'vimeo'
        ];

        $embeds = [
            'dailymail' => [
                'url' => 'http://www.dailymail.co.uk/embed/video/(:id).html',
                'width' => 698,
                'height' => 573,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="auto" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'dailymotion' => [
                'url' => 'https://www.dailymotion.com/embed/video/(:id)',
                'width' => 480,
                'height' => 270,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'facebook-video' => [
                'url' => 'https://www.facebook.com/(:user)/videos/(:id)',
                'width' => 854,
                'height' => 400,
                'html' => 
                      '<div '
                    .   'class="fb-video" '
                    .   'data-allowfullscreen="true" '
                    .   'data-href="(:url)" '
                    .   'data-show-text="false" '
                    .   'data-width="auto">'
                    . '</div>'
            ],
            'facebook-post' => [
                'url' => 'https://www.facebook.com/(:user)/posts/(:id)',
                'width' => 854,
                'height' => 400,
                'html' => 
                      '<div '
                    .   'class="fb-post" '
                    .   'data-allowfullscreen="true" '
                    .   'data-href="(:url)" '
                    .   'data-show-text="false" '
                    .   'data-width="auto">'
                    . '</div>'
            ],
            'googleplus' => [
                'url' => 'https://plus.google.com/(:user)/posts/(:id)',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<div '
                    .   'class="g-post" '
                    .   'data-href="(:url)">'
                    . '</div>'
            ],
            'imdb' => [
                'url' => 'https://www.imdb.com/videoembed/(:id)',
                'width' => 854,
                'height' => 650,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'instagram-post' => [
                'url' => 'https://www.instagram.com/p/(:id)',
                'width' => 320,
                'height' => 320,
                'html' => 
                      '<blockquote '
                    .   'class="instagram-media" '
                    .   'data-instgrm-captioned '
                    .   'style="width:100%" '
                    .   'data-instgrm-version="7">'
                    .     '<a '
                    .       'href="(:url)" '
                    .       'target="_blank">'
                    .     '</a>'
                    . '</blockquote>'
            ],
            'instagram-video' => [
                'url' => 'https://www.instagram.com/p/(:id)',
                'width' => 320,
                'height' => 320,
                'html' => 
                      '<blockquote '
                    .   'class="instagram-media" '
                    .   'style="width:100%" '
                    .   'data-instgrm-version="7">'
                    .     '<a '
                    .       'href="(:url)" '
                    .       'target="_blank">'
                    .     '</a>'
                    . '</blockquote>'
            ],
            'liveleak' => [
                'url' => 'https://www.liveleak.com/ll_embed?f=(:id)',
                'width' => 640,
                'height' => 360,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'streamable' => [
                'url' => 'https://streamable.com/s/(:id)',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'twitter-tweet' => [
                'url' => 'https://twitter.com/(:user)/status/(:id)',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<blockquote class="twitter-tweet">'
                    .   '<a href="(:url)"></a>'
                    . '</blockquote>'
            ],
            'twitter-video' => [
                'url' => 'https://twitter.com/(:user)/status/(:id)',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<blockquote class="twitter-video">'
                    .   '<a href="(:url)"></a>'
                    . '</blockquote>',
            ],
            'videoplayer' => [
                'url' => '(:url)',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<video '
                    .   'width="(:width)" '
                    .   'height="(:height)" '
                    .   'controls>'
                    .     '<source src="(:url)" type="(:mime)">'
                    . '</video>'
            ],
            'vidio' => [
                'url' => 'https://www.vidio.com/embed/(:id)?player_only=true&autoplay=false',
                'width' => 480,
                'height' => 270,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>',
            ],
            'vidme' => [
                'url' => 'https://vid.me/e/(:id)?tools=1',
                'width' => 854,
                'height' => 480,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'vimeo' => [
                'url' => 'https://player.vimeo.com/video/(:id)?title=0&amp;byline=0&amp;portrait=0&color=e3a01b',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'youtube' => [
                'url' => 'https://www.youtube.com/embed/(:id)?rel=0',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ],
            'youtube-live-stream' => [
                'url' => 'https://www.youtube.com/embed/live_stream?channel=(:id)',
                'width' => 560,
                'height' => 314,
                'html' => 
                      '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="(:height)" '
                    .   'scrolling="no" '
                    .   'src="(:url)" '
                    .   'width="(:width)">'
                    . '</iframe>'
            ]
        ];

        foreach($regexs as $regex => $provider){
            if(!preg_match($regex, $this->value, $match))
                continue;
            
            $this->provider = $provider;
            $options = $embeds[$provider];

            $url            = $options['url'];
            $this->width    = $options['width'];
            $this->height   = $options['height'];
            $this->html     = $options['html'];

            foreach($match as $index => $value)
                $url = str_replace('(:' . $index . ')', $value, $url);
            $this->url = $url;

            break;
        }

        if(!$this->url)
            return;

        // get the size
        if(preg_match('!width ?[:|=][ "]?([0-9]+)!', $this->value, $match))
            $this->width = $match[1] ?? $this->width;
        if(preg_match('!height ?[:|=][ "]?([0-9]+)!', $this->value, $match))
            $this->height = $match[1] ?? $this->height;

        $apply_vars = ['url', 'height', 'width'];
        foreach($apply_vars as $prop)
            $this->html = str_replace('(:' . $prop . ')', $this->$prop, $this->html);
    }

    public function __construct(string $source=null){
        $this->value = $source ?? '';
    }

    public function __get($name){
        if(!$this->_parsed)
            $this->_parse();
        return $this->$name ?? null;
    }

    public function __toString(){
        return $this->value;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return $this->value;
    }
}
