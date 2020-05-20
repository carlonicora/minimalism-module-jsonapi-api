<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api\Abstracts;

use CarloNicora\Minimalism\Core\Modules\abstracts\models\abstractApiModel;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use CarloNicora\Minimalism\Core\Services\Exceptions\ServiceNotFoundException;
use CarloNicora\Minimalism\Modules\JsonApi\JsonApiResponse;
use CarloNicora\Minimalism\Modules\JsonApi\Traits\JsonApiModelTrait;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use CarloNicora\Minimalism\Services\Encrypter\ParameterValidator\Decrypter;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\DecrypterInterface;
use Exception;

abstract class AbstractModel extends abstractApiModel {
    use JsonApiModelTrait;

    /** @var JsonApiResponse  */
    protected JsonApiResponse $response;

    /** @var Encrypter */
    protected Encrypter $encrypter;

    /**
     * @param array $passedParameters
     * @param array|null $file
     * @throws Exception
     */
    public function initialise(array $passedParameters, array $file = null): void
    {
        $this->encrypter = $this->services->service(Encrypter::class);

        parent::initialise($passedParameters, $file);

        $this->response = new JsonApiResponse();
    }

    /**
     * @return DecrypterInterface
     */
    public function decrypter(): DecrypterInterface
    {
        return new Decrypter($this->encrypter);
    }

    /**
     * @return ResponseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     */
    public function DELETE(): ResponseInterface {
        return $this->generateResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }

    /**
     * @return ResponseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     */
    public function GET(): ResponseInterface {
        return $this->generateResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }

    /**
     * @return ResponseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     */
    public function POST(): ResponseInterface {
        return $this->generateResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }

    /**
     * @return ResponseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     */
    public function PUT(): ResponseInterface {
        return $this->generateResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }
}