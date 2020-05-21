<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api\Tests\Unit;

use CarloNicora\Minimalism\Core\Modules\ErrorController;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ApiModelInterface;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Modules\JsonApi\Api\Controller;
use CarloNicora\Minimalism\Modules\JsonApi\Api\Tests\Abstracts\AbstractTestCase;
use CarloNicora\Minimalism\Services\Security\Exceptions\UnauthorisedException;
use CarloNicora\Minimalism\Services\Security\Factories\ServiceFactory;
use CarloNicora\Minimalism\Services\Security\Interfaces\SecurityClientInterface;
use CarloNicora\Minimalism\Services\Security\Interfaces\SecuritySessionInterface;
use CarloNicora\Minimalism\Services\Security\Security;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

class ControllerTest extends AbstractTestCase
{
    /**
     * @throws Exception
     */
    public function testPostInitialise() : Controller
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/uri';
        $services = new ServicesFactory();
        $services->loadService(ServiceFactory::class);
        $controller = new Controller($services);

        $security = $this->getMockBuilder(Security::class)
            ->disableOriginalConstructor()
            ->getMock();

        $security->method('getHttpHeaderSignature')->willReturn('X-Phlow');
        $security->method('getSecurityClient')->willReturn(
            $this->getMockBuilder(SecurityClientInterface::class)->getMock()
        );
        $security->method('getSecuritySession')->willReturn(
            $this->getMockBuilder(SecuritySessionInterface::class)->getMock()
        );

        $this->setProperty($controller, 'security', $security);

        $this->assertEquals($controller, $controller->postInitialise());

        return $controller;
    }

    /**
     * @throws Exception
     */
    public function testPostInitialiseFailsBecauseOfSignatureMissing() : void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/uri';
        $services = new ServicesFactory();
        $services->loadService(ServiceFactory::class);
        $controller = new Controller($services);

        $security = $this->getMockBuilder(Security::class)
            ->disableOriginalConstructor()
            ->getMock();

        $security->method('getHttpHeaderSignature')->willReturn('X-Phlow');
        $security->method('validateSignature')->willThrowException(new UnauthorisedException('', 2));

        $this->setProperty($controller, 'security', $security);

        $errorController = new ErrorController($services);
        $this->setProperty($errorController, 'security', $security);
        $errorController->setException(new UnauthorisedException('Unauthorised', 2));

        $this->assertEquals($errorController, $controller->postInitialise());
    }

    /**
     * @param Controller $controller
     * @depends testPostInitialise
     */
    public function testRender(Controller $controller): void
    {
        $response = new Response();
        $response->setData('');
        $response->setStatus('200');
        $response->setContentType('application/vnd.api+json');

        /** @var MockObject|ApiModelInterface $model */
        $model = $this->getMockBuilder(ApiModelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->method('GET')
            ->willReturn($response);

        $this->setProperty($controller, 'model', $model);

        $this->assertEquals($response, $controller->render());
    }
}