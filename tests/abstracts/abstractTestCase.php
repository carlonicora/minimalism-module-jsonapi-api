<?php
namespace tests\abstracts;

use CarloNicora\Minimalism\Core\Services\factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use CarloNicora\Minimalism\Services\logger\logger;
use CarloNicora\Minimalism\Services\paths\paths;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class abstractTestCase extends TestCase
{
    /** @var MockObject */
    protected MockObject $servicesFactory;

    /** @var MockObject */
    protected MockObject $encrypterService;

    /** @var MockObject */
    protected MockObject $loggerService;

    /** @var MockObject */
    protected MockObject $pathsService;

    /**
     * abstractTestCase constructor.
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->servicesFactory = $this->getMockBuilder(servicesFactory::class)
            ->getMock();

        $this->initialiseEncrypterService();

        $this->loggerService = $this->getMockBuilder(logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pathsService = $this->getMockBuilder(paths::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->servicesFactory->method('service')
            ->with($this->logicalOr(
                Encrypter::class,
                logger::class,
                paths::class
            ))
            ->willReturn($this->returnCallback([$this, 'returnService']));
    }

    /**
     * @param string $serviceName
     * @return MockObject
     */
    public function returnService(string $serviceName): MockObject
    {
        switch ($serviceName) {
            case Encrypter::class:
                return $this->encrypterService;
                break;
            case logger::class:
                return $this->loggerService;
                break;
            case paths::class:
            default:
                return $this->pathsService;
                break;
        }
    }

    private function initialiseEncrypterService(): void
    {
        $this->encrypterService = $this->getMockBuilder(Encrypter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->encrypterService->method('decryptId')->willReturn(1);
    }
}