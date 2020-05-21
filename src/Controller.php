<?php
namespace CarloNicora\Minimalism\Modules\JsonApi\Api;

use CarloNicora\Minimalism\Core\Modules\Abstracts\Controllers\AbstractApiController;
use CarloNicora\Minimalism\Core\Modules\ErrorController;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ApiModelInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ControllerInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ModelInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Core\Services\Exceptions\ServiceNotFoundException;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Traits\HttpHeadersTrait;
use CarloNicora\Minimalism\Modules\JsonApi\Api\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Services\Security\Events\SecurityErrorEvents;
use CarloNicora\Minimalism\Services\Security\Exceptions\UnauthorisedException;
use CarloNicora\Minimalism\Services\Security\Security;
use Exception;
use Throwable;

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
     * @param string|null $modelName
     * @return ControllerInterface
     * @throws Exception
     */
    public function initialiseModel(string $modelName = null): ControllerInterface
    {
        $response = parent::initialiseModel($modelName);

        if ($this->model !== null){
            $fields = [];

            foreach ($this->passedParameters as $parameterKey=>$parameter) {
                if ($parameterKey === 'include') {
                    $this->model->setIncludedResourceTypes(explode(',', $parameter));
                } elseif (strlen($parameterKey) > 6 && strpos($parameterKey, 'fields') === 0) {
                    $typeName = substr($parameterKey, 7, -1);
                    $fields[$typeName] = explode(',', $parameter);
                }
            }

            if ($fields !== []){
                $this->model->setRequiredFields($fields);
            }
        }

        return $response;
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

        try {
            $this->security->validateSignature(
                $signature,
                $this->verb,
                $url,
                $this->bodyParameters,
                $this->security->getSecurityClient(),
                $this->security->getSecuritySession());
        } catch (Throwable $e) {
            $this->services->logger()->error()
                ->log(SecurityErrorEvents::SIGNATURE_MISSED($url, $this->verb, json_encode($this->bodyParameters, JSON_THROW_ON_ERROR)))
                ->throw(UnauthorisedException::class, 'Unauthorised');
        }
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function render(): ResponseInterface {
        try {
            $this->model->preRender();

            $response = $this->model->{$this->verb}();
        } catch (Exception $e) {
            $response=$this->model->generateResponseFromError($e);
        }

        $this->services->destroyStatics();

        return $response;
    }
}