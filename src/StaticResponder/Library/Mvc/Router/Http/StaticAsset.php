<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder\Library\Mvc\Router\Http;

use Zend\Mvc\Router\Http\Regex;
use Zend\Mvc\Router\Http\RouteInterface;

/**
 * Route config for static assets (js, css, font, images) shared by
 * both "/(js|css|font|img)/..." and "/nges/(js|css|font|img)/..."
 */
class StaticAsset extends Regex implements RouteInterface
{
    const ALLOWED_TYPE      = 'js|css|img|font|json';
    const ALLOWED_EXTENSION = 'js|css|jpg|jgeg|png|gif|ico|ttf|eot|svg|svgz|woff|json';

    protected static $options = array(
        'regex'    => '/(?<type>(%s))/(?<filename>.+)\.(?<extension>(%s))$',
        'defaults' => array(
            // __NAMESPACE__ does not get set here because it
            // will be inherited from the parent route, but
            // for global root level route (/js/*, /css/*,
            // /img/* and /font/*) __NAMESPACE__ needs to be
            // set in config to indicate what namespace it should
            // use. External config settins from module.config.php
            // will be merged into these options in the static
            // factory method and then passed to parent class
            // (Regex) at class instantiation.
            'controller'    => 'Index',
            'action'        => 'staticAsset',
        ),
        'spec' => '/%type%/%filename%.%extension%',
    );

    public static function factory($options = array())
    {
        $options = array_merge_recursive(self::$options, $options);

        return new static(
            sprintf(
                $options['regex'],
                static::ALLOWED_TYPE,
                static::ALLOWED_EXTENSION
            ),
            $options['spec'],
            $options['defaults']
        );
    }
}
