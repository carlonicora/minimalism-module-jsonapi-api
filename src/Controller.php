<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Core\Modules\Abstracts\Controllers\AbstractApiController;
use CarloNicora\Minimalism\Core\Modules\ErrorController;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ApiModelInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ControllerInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ModelInterface;
use CarloNicora\Minimalism\Core\Response;
use CarloNicora\Minimalism\Core\Services\Exceptions\ServiceNotFoundException;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Traits\HttpHeadersTrait;
use CarloNicora\Minimalism\Modules\JsonApi\Api\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Services\Security\Security;
use Exception;

class Controller extends AbstractApiController {
    use HttpHeadersTrait;

    /** @var ModelInterface|ApiModelInterface|AbstractModel  */
    protected ModelInterface $model;

    /** @var Security  */
    private Security $security;

    public function __construct(ServicesFactory $services)
    {
        parent::__construct($services);

        $this->security = $this->services->service(Security::class);
    }

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
        } catch (Exception $e) {
            $errorController->setException($e);
        }

        return $errorController ?? $this;
    }

    /**
     *
     * @throws serviceNotFoundException
     * @throws Exception
     */
    protected function validateSignature(): void {
        $signature = $this->getHeader($this->security->getHttpHeaderSignature());

        $url = $_SERVER['REQUEST_URI'];

        $this->security->validateSignature(
            $signature,
            $this->verb,
            $url,
            $this->bodyParameters,
            $this->security->getSecurityClient(),
            $this->security->getSecuritySession());
    }

    /**
     * @return Response
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function render(): Response {
        try {
            $this->model->preRender();

            $response = $this->model->{$this->verb}();
        } catch (Exception $e) {
            $document = new Document();
            $document->addError(new Error($e));

            $response = $this->model->generateResponse($document, $document->errors[0]->status ?? '500');
        }

        $this->services->destroyStatics();

        return $response;
    }
}