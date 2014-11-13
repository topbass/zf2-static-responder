<?php
/**
 * ZF2 Static Responder
 *
 * @link      https://github.com/waltzofpearls/zf2-static-responder for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace StaticResponder\Controller;

use Zend\View\Model\ViewModel;
use StaticResponder\Library\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}
