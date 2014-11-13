<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder\Library\Mvc\Controller\Plugin;

use Zend\Http\Headers;
use Zend\Http\Response\Stream;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use StaticResponder\Library\Mvc\Exception\InvalidStaticAssetException;

class StaticResponse extends AbstractPlugin
{
    const STATIC_JS   = 'js';
    const STATIC_CSS  = 'css';
    const STATIC_IMG  = 'img';
    const STATIC_JSON = 'json';
    const STATIC_FONT = 'font';

    const STATUS_NOT_MODIFIED   = 304;
    const STATUS_NOT_FOUND      = 404;
    const STATUS_INTERNAL_ERROR = 500;

    protected $allowedStaticTypes = array(
        self::STATIC_JS => array(
            'js'   => array('Content-Type' => 'application/javascript'),
            'css'  => array(
                'Content-Type' => 'text/css',
                'X-Content-Type-Options' => 'nosniff',
            ),
            'ttf'  => array('Content-Type' => 'application/x-font-ttf'),
            'woff' => array('Content-Type' => 'application/x-font-woff'),
            'eot'  => array('Content-Type' => 'application/vnd.ms-fontobject'),
            'json' => array('Content-Type' => 'application/json'),
        ),
        self::STATIC_CSS => array(
            'css'  => array(
                'Content-Type' => 'text/css',
                'X-Content-Type-Options' => 'nosniff',
            ),
            'png'  => array('Content-Type' => 'image/png'),
        ),
        self::STATIC_IMG => array(
            'ico'  => array('Content-Type' => 'image/x-icon'),
            'png'  => array('Content-Type' => 'image/png'),
            'gif'  => array('Content-Type' => 'image/gif'),
            'jpg'  => array('Content-Type' => 'image/jpeg'),
            'jpeg' => array('Content-Type' => 'image/jpeg'),
        ),
        self::STATIC_JSON => array(
            'json' => array('Content-Type' => 'application/json'),
        ),
        self::STATIC_FONT => array(
            'ttf'  => array('Content-Type' => 'application/x-font-ttf'),
            'eot'  => array('Content-Type' => 'application/vnd.ms-fontobject'),
            'svg'  => array('Content-Type' => 'image/svg+xml'),
            'svgz' => array('Content-Type' => 'image/svg+xml'),
            'woff' => array('Content-Type' => 'application/x-font-woff'),
        ),
    );

    public function response()
    {
        $routeMatches = $this->getController()->getEvent()->getRouteMatch()->getParams();
        extract($routeMatches);

        $pathToAssetFile = sprintf(
            'module/%s/public/%s/%s.%s',
            $this->getModuleName(),
            $type,
            $this->stripVersionNumber($filename, $type),
            $extension
        );
        $etag = md5($pathToAssetFile);
        $lastModified = gmdate('D, d M Y H:i:s', @filemtime($pathToAssetFile)) . ' GMT';

        if (!isset($this->allowedStaticTypes[$type])
            || !isset($this->allowedStaticTypes[$type][$extension])
            || !file_exists($pathToAssetFile)
        ) {
            // HTTP/1.1 404 Not Found
            $response = $this->httpExceptionResponse(static::STATUS_NOT_FOUND);
        } elseif (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            && isset($_SERVER['HTTP_IF_NONE_MATCH'])
            && trim($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified
            && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag
        ) {
            // HTTP/1.1 304 Not Modified
            $response = $this->httpExceptionResponse(static::STATUS_NOT_MODIFIED);
        } else {
            // HTTP/1.1 200 OK
            $response = $this->httpStreamResponse(array(
                'type'      => $type,
                'extension' => $extension,
                'path'      => $pathToAssetFile,
                'etag'      => $etag,
                'modified'  => $lastModified,
            ));
        }

        header_remove('X-Powered-By');
        header_remove('Set-Cookie');

        return $response;
    }

    protected function getModuleName()
    {
        $reflector = new \ReflectionClass($this->getController());
        return strstr(trim($reflector->getNamespaceName(), '\\'), '\\', true);
    }

    protected function httpStreamResponse($params)
    {
        $headers = new Headers();
        $headers->addHeaders(array_merge(
            $this->allowedStaticTypes[$params['type']][$params['extension']],
            array(
                'Pragma' => 'public',
                'Cache-Control' => 'public, max-age=' . 31536000, // 1 year
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
                'Last-Modified' => $params['modified'],
                'ETag' => $params['etag'],
                'Accept-Ranges' => 'bytes',
                'Content-Length' => filesize($params['path']),
            )
        ));

        $response = new Stream();
        $response->setStream(fopen($params['path'], 'r'));
        $response->setHeaders($headers);

        return $response;
    }

    public function httpExceptionResponse($statusCode = self::STATUS_NOT_FOUND)
    {
        return $this->getController()
                    ->getResponse()
                    ->setStatusCode($statusCode)
                    ->setContent('');
    }

    protected function stripVersionNumber($file, $type)
    {
        if (preg_match('/(js|css)/', $type)
            && preg_match('/^(.*)\-[0-9]*$/', $file, $matches)
        ) {
            $file = $matches[1];
        }
        return $file;
    }
}
