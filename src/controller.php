<?php
namespace carlonicora\minimalism\modules\jsonapi\api;

use carlonicora\minimalism\core\bootstrapper;
use carlonicora\minimalism\core\modules\abstracts\controllers\abstractApiController;
use carlonicora\minimalism\core\services\exceptions\serviceNotFoundException;
use carlonicora\minimalism\core\services\factories\servicesFactory;
use carlonicora\minimalism\core\traits\httpHeaders;
use carlonicora\minimalism\service\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\service\jsonapi\responses\dataResponse;
use carlonicora\minimalism\service\jsonapi\responses\errorResponse;
use carlonicora\minimalism\services\security\security;
use Exception;

class controller extends abstractApiController {
    use httpHeaders;

    /** @var string */
    private string $signature;

    /**
     * apiController constructor.
     * @param servicesFactory $services
     * @param string|null $modelName
     * @param array|null $parameterValueList
     * @param array|null $parameterValues
     * @throws Exception
     */
    public function __construct(servicesFactory $services, string $modelName=null, array $parameterValueList=null, array $parameterValues=null){
        parent::__construct($services, $modelName, $parameterValueList, $parameterValues);

        $this->validateSignature();
    }


    /**
     *
     * @throws serviceNotFoundException
     * @throws Exception
     */
    protected function validateSignature(): void {
        /** @var security $security */
        $security = $this->services->service(security::class);
        $this->signature =$this->getHeader($security->getHttpHeaderSignature());

        $url = $_SERVER['REQUEST_URI'];

        $security->validateSignature($this->signature, $this->verb, $url, $this->bodyParameters, $security->getSecurityClient(), $security->getSecuritySession());
    }

    /**
     *
     */
    protected function parseUriParameters(): void {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');

        if (!(isset($uri) && $uri === '/')) {
            $variables = array_filter(explode('/', substr($uri, 1)), 'strlen');
            $variable = current($variables);
            if (stripos($variable, 'v') === 0 && is_numeric(substr($variable, 1, 1)) && strpos($variable, '.') !== 0){
                $this->version = $variable;
                array_shift($variables);
            }

            $this->passedParameters = $this->parseModelNameFromUri($variables);
        }
    }

    /**
     * @return string
     */
    public function render(): string{
        try {
            $this->model->preRender();
        } catch (Exception $e) {
            $error = new errorResponse($e->getCode(), $e->getMessage());
            return $error->toJson();
        }

        /** @var responseInterface $apiResponse */
        $apiResponse = $this->model->{$this->verb}();

        $code = $apiResponse->getStatus();
        $GLOBALS['http_response_code'] = $code;

        header(dataResponse::generateProtocol() . ' ' . $code . ' ' . $apiResponse->generateText());

        $this->services->destroyStatics();

        if (bootstrapper::$servicesCache !== null){
            file_put_contents(bootstrapper::$servicesCache, serialize($this->services));
        }

        return $apiResponse->toJson();
    }

    /**
     * @param Exception $e
     * @return void
     */
    public function writeException(Exception $e): void {
        $error = new errorResponse($e->getCode() ?? 500, $e->getMessage());

        $code = $error->getStatus();
        $GLOBALS['http_response_code'] = $code;

        header(dataResponse::generateProtocol() . ' ' . $code . ' ' . $error->generateText());

        echo $error->toJson();
    }
}