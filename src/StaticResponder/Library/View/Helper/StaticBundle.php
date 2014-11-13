<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder\Library\View\Helper;

use Zend\Stdlib\ArrayUtils;
use Zend\Config\Config;

class StaticBundle
{
    const ENV_DEVELOPMENT   = 'development';
    const ENV_TESTING       = 'testing';
    const ENV_PRODUCTION    = 'production';

    const BUNDLE_JAVASCRIPT = 'javascripts';
    const BUNDLE_STYLESHEET = 'stylesheets';

    protected $sm             = null;
    protected $routeMatch     = null;
    protected $moduleName     = null;
    protected $controllerName = null;
    protected $actionName     = null;
    protected $packageName    = null;

    protected $environment = null;
    protected $version     = null;
    protected $bundles     = null;
    protected $outputs     = array();
    protected $delimiter   = PHP_EOL;
    protected $types       = array(
        self::BUNDLE_JAVASCRIPT => 'js',
        self::BUNDLE_STYLESHEET => 'css',
    );

    public function __invoke()
    {
        $args = func_get_args();

        // Method arguments
        // $arg1 = isset($args[0]) ? $args[0] : null;

        // Method body
        if (is_null($this->sm)) {
            $this->prePopulateServiceManager();
        }

        if (is_null($this->bundles)) {
            $this->populateBundleConfig();
        }

        $this->outputs = array();

        return $this;
    }

    public function __toString()
    {
        return implode($this->delimiter, $this->outputs) . PHP_EOL;
    }

    protected function prePopulateServiceManager()
    {
        $this->sm = $this->getView()->getHelperPluginManager()->getServiceLocator();
        return $this;
    }

    protected function populateBundleConfig()
    {
        $config = array();
        $moduleManager = $this->sm->get('ModuleManager');

        if ($moduleManager !== null) {
            foreach ($moduleManager->getLoadedModules(false) as $name => $module) {
                if (!method_exists($module, 'getBundleConfig')) {
                    continue;
                }

                $config = ArrayUtils::merge($config, $module->getBundleConfig());
            }
        }

        $this->bundles = new Config($config);

        return $this;
    }

    protected function getServiceLocator()
    {
        if (is_null($this->sm)) {
            $this->sm = $this->getView()->getHelperPluginManager()->getServiceLocator();
        }

        return $this->sm;
    }

    protected function getRouteMatch()
    {
        if (is_null($this->routeMatch)) {
            // Equivalent to the uncommented and working code, the commented
            // block of code can also retrieve RouteMatch
            //
            // $router = $this->sm->get('router');
            // $request = $this->sm->get('request');
            // return $router->match($request);
            //
            $this->routeMatch = $this->getServiceLocator()->get('Application')
                ->getMvcEvent()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    protected function getModuleName()
    {
        if (is_null($this->moduleName)) {
            try {
                $reflector = new \ReflectionClass($this->getFullControllerName());
                $this->moduleName = strstr(trim($reflector->getNamespaceName(), '\\'),
                    '\\', true);
            } catch (\ReflectionException $e) {
                $this->moduleName = static::DEFAULT_MODULE;
            }
        }

        return $this->moduleName;
    }

    public function getControllerName()
    {
        if (is_null($this->controllerName)) {
            $this->controllerName = $this->getRouteMatch()->getParam('controller');
        }

        return $this->controllerName;
    }

    public function getActionName()
    {
        if (is_null($this->actionName)) {
            $this->actionName = $this->getRouteMatch()->getParam('action');
        }

        return $this->actionName;
    }

    public function getPackageName()
    {
        if (is_null($this->packageName)) {
            $this->packageName = $this->getModuleName() . '.' . $this->getControllerName();
        }

        return $this->packageName;
    }

    public function includeJavaScriptBundle($name)
    {
        $this->delimiter = PHP_EOL;
        $this->outputs = ArrayUtils::merge($this->outputs, array_map(function($file) {
            return sprintf(
                '<script type="text/javascript" src="%s"></script>',
                $file
            );
        }, $this->getBundleFiles($name, self::BUNDLE_JAVASCRIPT)));

        return $this;
    }

    public function headjsJavaScriptBundle($name)
    {
        $this->delimiter = ',' . PHP_EOL;
        $this->outputs = ArrayUtils::merge($this->outputs, array_map(function($file) {
            return sprintf('"%s"', $file);
        }, $this->getBundleFiles($name, self::BUNDLE_JAVASCRIPT)));

        return $this;
    }

    public function moduleJavaScriptBundle()
    {
        $this->headjsJavaScriptBundle(strtolower($this->getModuleName()));

        return $this;
    }

    public function viewModelJavaScriptBundle()
    {
        $bundle = sprintf('%s.%s', $this->getPackageName(), $this->getActionName());
        $config = array(
            'javascripts' => array(
                $bundle => array(
                    sprintf(
                        '/%s/js/%s/%s/%s.js',
                        strtolower($this->getModuleName()),
                        $this->getModuleName(),
                        $this->getControllerName(),
                        $this->getActionName()
                    ),
                ),
            ),
        );

        $this->bundles->merge(new Config($config));
        $this->headjsJavaScriptBundle($bundle);

        return $this;
    }

    public function includeStyleSheetBundle($name, $forceSplit = false)
    {
        $this->delimiter = PHP_EOL;
        $this->outputs = ArrayUtils::merge($this->outputs, array_map(function($file) {
            return sprintf(
                '<link href="%s" rel="stylesheet" type="text/css">',
                $file
            );
        }, $this->getBundleFiles($name, self::BUNDLE_STYLESHEET, $forceSplit)));

        return $this;
    }

    public function moduleStyleSheetBundle()
    {
        $this->includeStyleSheetBundle(strtolower($this->getModuleName()));

        return $this;
    }

    public function viewModelStyleSheetBundle()
    {
        $bundle = sprintf('%s.%s', $this->getPackageName(), $this->getActionName());
        $this->includeStyleSheetBundle($bundle);

        return $this;
    }

    public function getBundleFiles($name, $type, $forceSplit = false)
    {
        $files = array();

        if (!$this->bundles->offsetExists($type)) {
            return $files;
        }
        if (!$this->bundles[$type]->offsetExists($name)) {
            return $files;
        }

        if ($forceSplit || $this->getAppEnvironment() == self::ENV_DEVELOPMENT) {
            foreach ($this->bundles[$type][$name] as $file) {
                $files[] = $this->appendBundleVersionNumber($file, $type);
            }
        } else {
            $files[] = $this->assembleBundleFileName($name, $type);
        }

        return $files;
    }

    protected function assembleBundleFileName($name, $type)
    {
        return $this->appendBundleVersionNumber(sprintf(
            '/%s/bundle.%s.%s',
            $this->types[$type],
            $name,
            $this->types[$type]
        ), $type);
    }

    protected function appendBundleVersionNumber($file, $type)
    {
        if (preg_match('/\.(js|css)$/', $file)) {
            $file = str_replace(
                sprintf('.%s', $this->types[$type]),
                sprintf('-%s.%s', $this->getBundleVersion($file), $this->types[$type]),
                $file
            );
        } else {
            $file = sprintf(
                '%s-%s.%s',
                $file,
                $this->getBundleVersion($file),
                $this->types[$type]
            );
        }

        return $file;
    }

    protected function getAppEnvironment()
    {
        if (is_null($this->environment)) {
            $config = $this->sm->get('Config');
            $this->environment = isset($config['env']) ? $config['env'] : 'development';
        }

        return $this->environment;
    }

    protected function getBundleVersion($file)
    {
        $version = '';

        if ($this->getAppEnvironment() == self::ENV_DEVELOPMENT) {
            if (preg_match('/^(?:\/?([^\/]+))?(\/?(?:js|css)\/.*\.(?:js|css))$/', $file, $matches)) {
                $module = ucfirst(trim($matches[1], '/'));
                $file = sprintf(
                    'module/%s/public/%s',
                    !empty($module) ? $module : 'StaticResponder',
                    ltrim($matches[2], '/')
                );
                $version = file_exists($file) ? filemtime($file) : time();
            }
        } else {
            $file = sprintf('public/%s', ltrim($file, '/'));
            $version = file_exists($file) ? filemtime($file) : date('Ym');
        }

        return $version;
    }
}
