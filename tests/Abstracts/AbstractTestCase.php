<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api\Tests\Abstracts;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Encrypter\Factories\ServiceFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class AbstractTestCase extends TestCase
{
    /** @var ServicesFactory|null  */
    protected ?ServicesFactory $services=null;

    protected array $parameters = [
        'GET' => [
            'id' => ['name'=>'id', 'encrypted'=>true]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->addEnvVariable('MINIMALISM_SERVICE_ENCRYPTER_KEY', '98172ab3');

        $this->services = new ServicesFactory();
        $this->services->loadService(ServiceFactory::class);
    }

    /**
     * @param string $name
     * @param string $value
     */
    private function addEnvVariable(string $name, string $value) : void
    {
        if (false === getenv($name)) {
            putenv($name . '=' . $value);
        }
        if (!isset($_ENV[$name])) {
            $_ENV[$name] = $value;
        }
    }

    /**
     * @param $object
     * @param $parameterName
     * @return mixed|null
     */
    protected function getProperty($object, $parameterName)
    {
        try {
            $reflection = new ReflectionClass(get_class($object));
            $property = $reflection->getProperty($parameterName);
            $property->setAccessible(true);
            return $property->getValue($object);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param $object
     * @param $parameterName
     * @param $parameterValue
     */
    protected function setProperty($object, $parameterName, $parameterValue): void
    {
        try {
            $reflection = new ReflectionClass(get_class($object));
            $property = $reflection->getProperty($parameterName);
            $property->setAccessible(true);
            $property->setValue($object, $parameterValue);
        } catch (ReflectionException $e) {
        }
    }
}