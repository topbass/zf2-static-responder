<?php
/**
 * ZF2 Static Responder - Unit Test Suite
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponderTest\Controller;

use StaticResponderTest\Bootstrap;
use Zend\Authentication\AuthenticationService;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include './config/application.php'
        );
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->mockLoginAuthenticate();
        $this->dispatch('/staticresponder/index/index');

        $this->assertResponseStatusCode(200);
        $this->assertNotRedirect();

        $this->assertModuleName('StaticResponder');
        $this->assertControllerName('StaticResponder\Controller\Index');
        $this->assertControllerClass('IndexController');
        $this->assertActionName('index');
        $this->assertMatchedRouteName('staticresponder/default');
    }
}
