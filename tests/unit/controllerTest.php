<?php
namespace tests\unit;


use carlonicora\minimalism\modules\jsonapi\api\CController;
use tests\abstracts\abstractTestCase;

class controllerTest extends abstractTestCase
{
    public function testConstructController() : CController
    {
        $controller = new CController();
    }
}