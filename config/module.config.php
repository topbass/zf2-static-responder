<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

return array(
    // Router
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'StaticResponder\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'static' => array(
                'type' => 'StaticResponder\Library\Mvc\Router\Http\StaticAsset',
                //
                // By default StaticAsset route plugin uses namespace inherited
                // from parent route. The global static route (/js/*, /css/*,
                // /img/* and /font/*) does not have a parent route, so we need
                // to tell the plugin what namespace it should use
                //
                'options' => array(
                    'defaults' => array('__NAMESPACE__' => 'StaticResponder\Controller')
                ),
            ),
            //
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /staticresponder/:controller/:action
            //
            'staticresponder' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/staticresponder',
                    'defaults' => array(
                        '__NAMESPACE__' => 'StaticResponder\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(),
                        ),
                    ),
                    'static' => array('type' => 'StaticResponder\Library\Mvc\Router\Http\StaticAsset'),
                ),
            ),
        ),
    ),
    // Controller and controller plugin
    'controllers' => array(
        'invokables' => array(
            'StaticResponder\Controller\Index' => 'StaticResponder\Controller\IndexController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'StaticResponse' => 'StaticResponder\Library\Mvc\Controller\Plugin\StaticResponse',
        )
    ),
    // View manager and view helper
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_path_stack'      => array(__DIR__ . '/../view'),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'StaticBundle' => 'StaticResponder\Library\View\Helper\StaticBundle',
            'ResourceCdn'  => 'Nges\Library\View\Helper\ResourceCdn',
        ),
    ),
);
