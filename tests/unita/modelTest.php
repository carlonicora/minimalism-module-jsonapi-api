<?php
namespace tests\unit;

use CarloNicora\Minimalism\Modules\JsonApi\api\abstracts\AbstractModel;
use PHPUnit\Framework\MockObject\MockObject;
use tests\abstracts\abstractTestCase;
use tests\traits\arraysTrait;

class modelTest extends abstractTestCase
{
    use arraysTrait;

    public function testModelInitialisation(): MockObject
    {
        $model = $this->getMockForAbstractClass(
            AbstractModel::class,
            [$this->servicesFactory, [], 'GET', null]
        );

        $this->assertNull($model->redirectPage);

        return $model;
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testValidateJsonapiParameterSimpleObject(MockObject $model): void
    {
        $object = $model->validateJsonapiParameter($this->jsonApiDocumentSimple);

        $this->assertEquals('carlo', $object->data->attributes['name']);
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testValidateParameterDecryptionSimpleObject(MockObject $model): void
    {
        $object = $model->validateJsonapiParameter($this->jsonApiDocumentSimple);

        $this->assertEquals(1, $model->decryptParameter($object->data->id));
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testNullPreRender(MockObject $model): void
    {
        $this->assertNull($model->preRender());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testPUT(MockObject $model): void
    {
        $object = $model->PUT();
        $this->assertEquals(405, $object->getStatus());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testGET(MockObject $model): void
    {
        $object = $model->GET();
        $this->assertEquals(405, $object->getStatus());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testPOST(MockObject $model): void
    {
        $object = $model->POST();
        $this->assertEquals(405, $object->getStatus());
    }

    /**
     * @param MockObject|AbstractModel $model
     * @depends testModelInitialisation
     */
    public function testDELETE(MockObject $model): void
    {
        $object = $model->DELETE();
        $this->assertEquals(405, $object->getStatus());
    }
}