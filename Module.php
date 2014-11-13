<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return $this->includeConfig('module', true);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getBundleConfig()
    {
        return $this->includeConfig('bundle', false);
    }

    protected function includeConfig($file, $required = false)
    {
        $file = __DIR__ . '/config/' . $file . '.config.php';
        if (file_exists($file)) {
            return include $file;
        } else {
            if ($required) {
                throw new RuntimeException(sprintf(
                    'Required config file [%s] does not exist for module [%s].',
                    $file,
                    __NAMESPACE__
                ));
            } else {
                return array();
            }
        }
    }
}
