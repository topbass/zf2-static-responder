<?php
/**
 * ZF2 Static Responder - Unit Test Suite
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponderTest\Library\Mvc\Router\Http;

use StaticResponderTest\Bootstrap;
use PHPUnit_Framework_TestCase;

class StaticAssetTest extends PHPUnit_Framework_TestCase
{
    protected $serviceManager;

    public function setUp()
    {
        parent::setUp();

        $this->serviceManager = Bootstrap::getServiceManager();
    }

    public function testTest()
    {
        //
    }
}
