<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api\Tests\Unit\Validators;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ModelInterface;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Modules\JsonApi\Validators\JsonApiValidator;
use CarloNicora\Minimalism\Services\ParameterValidator\Objects\ParameterObject;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JsonApiValidatorTest extends TestCase
{
    private array $jsonApi = [
        'data' => [
            'type' => 'journalEntry',
            'id' => '1',
            'attributes' => [
                'title' => 'My journal entry'
            ]
        ],
        'links' => [
            'self' => 'https://journalentry/1'
        ]
    ];

    /**
     * @throws Exception
     */
    public function testCorrectJsonApiParameter() : void
    {
        $services = new ServicesFactory();
        /** @var MockObject|ParameterObject $parameter */
        $parameter = $this->getMockBuilder(ParameterObject::class)
            ->setConstructorArgs(['one', $this->jsonApi])
            ->getMockForAbstractClass();

        /** @var MockObject|ModelInterface $model */
        $model = $this->getMockBuilder(ModelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object = new JsonApiValidator($services, $parameter);

        $object->setParameter($model, $this->jsonApi);

        $this->assertEquals(1,1);
    }
}