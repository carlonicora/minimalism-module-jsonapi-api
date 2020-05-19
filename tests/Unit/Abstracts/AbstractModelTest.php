<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api\Tests\Unit\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Modules\JsonApi\Api\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Modules\JsonApi\Api\Tests\Abstracts\AbstractTestCase;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use Exception;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractModelTest extends AbstractTestCase
{
    /**
     * @return MockObject
     */
    public function testModelCreation(): MockObject
    {
        $model = $this->getMockForAbstractClass(
            AbstractModel::class,
            [$this->services]
        );

        $this->setProperty($model, 'parameters', $this->parameters);
        //$model->method('getParameters')->willReturn($this->parameters);

        $this->assertEquals('', $model->redirect());

        return $model;
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelCreation
     * @return MockObject
     * @throws Exception
     */
    public function testModelInitialisation(MockObject $model) : MockObject
    {
        /** @var Encrypter $e */
        $e = $this->services->service(Encrypter::class);

        $model->initialise(['id'=>$e->encryptId(1)]);

        $response = $this->getProperty($model, 'response');
        $this->assertEquals(new Response(), $response);

        return $model;
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     * @throws JsonException
     */
    public function testPOST(MockObject $model): void
    {
        $response = $model->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));

        $this->assertEquals($response, $model->POST());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     * @throws JsonException
     */
    public function testGET(MockObject $model): void
    {
        $response = $model->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));

        $this->assertEquals($response, $model->GET());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     * @throws JsonException
     */
    public function testDELETE(MockObject $model): void
    {
        $response = $model->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));

        $this->assertEquals($response, $model->DELETE());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     * @throws JsonException
     */
    public function testPUT(MockObject $model): void
    {
        $response = $model->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));

        $this->assertEquals($response, $model->PUT());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testGenerateResponse(MockObject $model) : void
    {
        /** @var MockObject|Document $document */
        $document = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $document->method('export')
            ->willThrowException(new JsonException());

        $response = new Response();
        $response->contentType = 'application/vnd.api+json';
        $response->httpStatus = '500';

        $this->assertEquals($response, $model->generateResponse($document, Response::HTTP_STATUS_200));
    }
}