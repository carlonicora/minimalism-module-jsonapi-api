<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\api\abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Core\Modules\abstracts\models\abstractApiModel;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use CarloNicora\Minimalism\Core\Services\Exceptions\ServiceNotFoundException;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use Exception;
use JsonException;
use Throwable;

abstract class AbstractModel extends abstractApiModel {
    /** @var Response  */
    protected Response $response;

    /** @var Encrypter */
    protected Encrypter $encrypter;

    /**
     * @param array $passedParameters
     * @param array|null $file
     * @throws Exception
     */
    public function initialise(array $passedParameters, array $file = null): void
    {
        parent::initialise($passedParameters, $file);

        $this->encrypter = $this->services->service(Encrypter::class);
        $this->response = new Response();
    }

    /**
     * @param string $parameter
     * @return string
     */
    public function decryptParameter(string $parameter) : string {
        return $this->encrypter->decryptId($parameter);
    }

    /**
     * @param Throwable $e
     * @return Response
     * @throws JsonException
     */
    public function getResponseFromError(Throwable $e): Response
    {
        $document = new Document();
        $document->addError(new Error($e));

        $this->response->data = $document->export();
        $this->response->httpStatus = $document->errors[0]->status ?? '500';

        return $this->response;
    }

    /**
     * @return Response
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws JsonException
     */
    public function DELETE(): Response {
        return $this->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }

    /**
     * @return Response
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws JsonException
     */
    public function GET(): Response {
        return $this->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }

    /**
     * @return Response
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws JsonException
     */
    public function POST(): Response {
        return $this->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }

    /**
     * @return Response
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws JsonException
     */
    public function PUT(): Response {
        return $this->getResponseFromError(new Exception('Not implemented', (int)Response::HTTP_STATUS_405));
    }
}