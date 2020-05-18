<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Core\Modules\Abstracts\Controllers\AbstractApiController;
use CarloNicora\Minimalism\Core\Modules\ErrorController;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ControllerInterface;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Core\Services\Exceptions\ServiceNotFoundException;
use CarloNicora\Minimalism\Core\Traits\HttpHeadersTrait;
use CarloNicora\Minimalism\Services\Security\Security;
use JsonException;
use Throwable;

class Controller extends AbstractApiController {
    use HttpHeadersTrait;

    /**
     * @return ControllerInterface
     */
    public function postInitialise() : ControllerInterface
    {
        $errorController = null;
        try {
            $errorController = new ErrorController($this->services);
            $this->validateSignature();
            $errorController = null;
        } catch (Throwable $e) {
            $errorController->setException($e);
        }

        return $errorController ?? $this;
    }

    /**
     *
     * @throws serviceNotFoundException
     * @throws Throwable
     */
    protected function validateSignature(): void {
        /** @var Security $security */
        $security = $this->services->service(Security::class);
        $signature = $this->getHeader($security->getHttpHeaderSignature());

        $url = $_SERVER['REQUEST_URI'];

        $security->validateSignature($signature, $this->verb, $url, $this->bodyParameters, $security->getSecurityClient(), $security->getSecuritySession());
    }

    /**
     * @return Response
     * @noinspection PhpRedundantCatchClauseInspection
     * @throws JsonException
     */
    public function render(): Response {
        $response = new Response();
        $document = new Document();

        try {
            $this->model->preRender();

            $response = $this->model->{$this->verb}();
        } catch (Throwable $e) {
            $document->addError(new Error($e));

            $response->data = $document->export();
            $response->httpStatus = $document->errors[0]->status ?? '500';
        }

        $response->contentType = 'application/vnd.api+json';

        $this->services->destroyStatics();

        return $response;
    }
}