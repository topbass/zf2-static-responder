<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder\Library\View\Helper;

class ResourceCdn extends AbstractHelper
{
    const ORIG_AUTO = 1;
    const ORIG_REMOTE = 2;
    const ORIG_LOCAL = 3;

    const TYPE_JS = 'js';
    const TYPE_CSS = 'css';

    protected $resources = array(
        'remote' => array(
            'js' => array(
                'jquery' => '//ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js',
                'jquery.ui' => '//ajax.aspnetcdn.com/ajax/jquery.ui/1.11.1/jquery-ui.min.js',
                'jquery.migrate' => '//ajax.aspnetcdn.com/ajax/jquery.migrate/jquery-migrate-1.2.1.min.js',
                'jquery.validate' => '//ajax.aspnetcdn.com/ajax/jquery.validate/1.12.0/jquery.validate.min.js',
                'bootstrap' => '//netdna.bootstrapcdn.com/bootstrap/2.3.2/js/bootstrap.min.js',
            ),
            'css' => array(
                'jquery.ui' => '//ajax.aspnetcdn.com/ajax/jquery.ui/1.11.1/themes/smoothness/jquery-ui.min.css',
            ),
        ),
        'local' => array(
            'js' => array(
                'jquery' => '/js/libs/jquery-1.11.1.js',
                'jquery.ui' => '/js/libs/jquery-ui-1.11.2.js',
                'jquery.migrate' => '/js/libs/jquery-migrate-1.2.1.js',
                'jquery.validate' => '/js/libs/validation-1.12.0/jquery.validate.js',
                'bootstrap' => '/js/libs/bootstrap-2.3.2.js',
            ),
            'css' => array(
                'jquery.ui' => '/css/jqueryui-1.11.2/smoothness/jquery-ui.css',
            ),
        ),
    );

    public function __invoke()
    {
        return $this;
    }

    protected function origin($origin = self::ORIG_AUTO)
    {
        switch ($origin) {
            case static::ORIG_LOCAL:
                return 'local';
            case static::ORIG_REMOTE:
            case static::ORIG_AUTO:
            default:
                return 'remote'
        }
    }

    public function resource($name, $origin = self::ORIG_AUTO, $type = self::TYPE_JS)
    {
        $res = $this->resources[$this->origin()][$type];

        if (is_null($name) || !isset($res[$name])) {
            return $res;
        } else
            return $res[$name];
    }

    public function js($name = null, $origin = self::ORIG_AUTO)
    {
        return $this->resource($name, $origin, static::TYPE_JS);
    }

    public function css($name = null, $origin = self::ORIG_AUTO)
    {
        return $this->resource($name, $origin, static::TYPE_CSS);
    }

    public function cdnJs($name = null)
    {
        return $this->js($name, static::ORIG_REMOTE);
    }

    public function cdnCss($name = null)
    {
        return $this->css($name, static::ORIG_REMOTE);
    }

    public function localJs($name = null)
    {
        return $this->js($name, static::ORIG_LOCAL);
    }

    public function localCss($name = null)
    {
        return $this->css($name, static::ORIG_LOCAL);
    }
}
