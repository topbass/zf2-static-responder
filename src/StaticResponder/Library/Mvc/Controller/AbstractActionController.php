<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder\Library\Mvc\Controller;

use Zend\View\Model\ViewModel;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

abstract class AbstractActionController extends ZendAbstractActionController
{
    public static function urlencode($url)
    {
        return empty($url) ? '' : strtr(
            base64_encode(
                addslashes(
                    gzcompress(
                        serialize($url), 9
                    ) // gzcompress
                ) // addslashes
            ), '+/=', '-_,'
        ); // strtr
    }

    public static function urldecode($string)
    {
        return empty($string) ? '' : unserialize(
            gzuncompress(
                stripslashes(
                    base64_decode(
                        strtr($string, '-_,', '+/=')
                    ) // base64_decode
                ) // stripslashes
            ) // gzuncompress
        ); // unserialize
    }

    public function staticAssetAction()
    {
        // Serves static asset files such as js, css, font and image
        return $this->StaticResponse()->response();
    }
}
