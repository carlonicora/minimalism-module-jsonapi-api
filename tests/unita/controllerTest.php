<?php
namespace tests\unit;


use CarloNicora\Minimalism\Modules\JsonApi\api\Controller;
use tests\abstracts\abstractTestCase;

class controllerTest extends abstractTestCase
{
    public function testConstructController() : Controller
    {
        $controller = new Controller();
    }
}