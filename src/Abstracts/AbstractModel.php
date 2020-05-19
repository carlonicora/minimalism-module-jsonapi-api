<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Core\Modules\abstracts\models\abstractApiModel;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use CarloNicora\Minimalism\Core\Services\Exceptions\ServiceNotFoundException;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use CarloNicora\Minimalism\Services\Encrypter\ParameterValidator\Decrypter;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\DecrypterInterface;
use Exception;
use JsonException;

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
        $this->encrypter = $this->services->service(Encrypter::class);

        parent::initialise($passedParameters, $file);

        $this->response = new Response();
    }

    /**
     * @return DecrypterInterface
     */
    public function decrypter(): DecrypterInterface
    {
        return new Decrypter($this->encrypter);
    }

    /**
     * @param Exception $e
     * @return Response
     * @throws JsonException
     */
    public function getResponseFromError(Exception $e): Response
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

    /**
     * @param Document $document
     * @param string $status
     * @return Response
     */
    final public function generateResponse(Document $document, string $status) : Response
    {
        $response = new Response();

        try {
            $response->data = $document->export();
            $response->httpStatus = $status;
        } catch (JsonException $e) {
            $response->httpStatus = Response::HTTP_STATUS_500;
        }

        $response->contentType = 'application/vnd.api+json';

        return $response;
    }
}