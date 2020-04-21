<?php
namespace carlonicora\minimalism\modules\jsonapi\api;

use carlonicora\minimalism\core\bootstrapper;
use carlonicora\minimalism\core\modules\abstracts\controllers\abstractApiController;
use carlonicora\minimalism\core\services\exceptions\serviceNotFoundException;
use carlonicora\minimalism\core\services\factories\servicesFactory;
use carlonicora\minimalism\core\traits\httpHeaders;
use carlonicora\minimalism\modules\jsonapi\api\errors\errors;
use carlonicora\minimalism\modules\jsonapi\api\exceptions\entityNotFoundException;
use carlonicora\minimalism\modules\jsonapi\api\exceptions\forbiddenException;
use carlonicora\minimalism\modules\jsonapi\api\exceptions\unauthorizedException;
use carlonicora\minimalism\services\jsonapi\abstracts\abstractResponseObject;
use carlonicora\minimalism\services\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\services\jsonapi\responses\dataResponse;
use carlonicora\minimalism\services\jsonapi\responses\errorResponse;
use carlonicora\minimalism\services\logger\traits\logger;
use carlonicora\minimalism\services\security\security;
use Error;
use Exception;
use JsonException;
use Throwable;

class controller extends abstractApiController {
    use httpHeaders, logger;

    /** @var string */
    private string $signature;

    /**
     * controller constructor.
     * @param servicesFactory $services
     * @param string|null $modelName
     * @param array|null $parameterValueList
     * @param array|null $parameterValues
     * @throws JsonException
     */
    public function __construct(servicesFactory $services, string $modelName=null, array $parameterValueList=null, array $parameterValues=null){
        try {
            $this->loggerInitialise($services);
            parent::__construct($services, $modelName, $parameterValueList, $parameterValues);
            $this->validateSignature();
        } catch (unauthorizedException $unauthorizedException) {
            $this->writeException($unauthorizedException, abstractResponseObject::HTTP_STATUS_401);
        } catch (forbiddenException $forbiddenException) {
            $this->writeException($forbiddenException, abstractResponseObject::HTTP_STATUS_403);
        } catch (entityNotFoundException $entityNotFoundException) {
            $this->writeException($entityNotFoundException, abstractResponseObject::HTTP_STATUS_404);
        } catch (Error $error) {
            try {
                $message = $error->getMessage() . ' in file ' . $error->getFile() . ':' . $error->getLine();
                $this->loggerWriteError(errors::FATAL_INITIALIZE_ERROR, 'FATAL ERROR while initializing. ' . $message , null, $error);
            } catch (Throwable $throwable) {
                // Sad, but we can't log a fatal error
            }
            $exception = new Exception('Server error', errors::FATAL_INITIALIZE_ERROR, $error);
            $this->writeException($exception);
        } catch (Exception $exception) {
            $this->writeException($exception);
        }
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
     * @throws JsonException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function render(): string{
        /** @var errorResponse $error  */
        if (($error = $this->model->preRender()) !== null){
            return $error->toJson();
        }

        /** @var responseInterface $apiResponse */
        try {
            $apiResponse = $this->model->{$this->verb}();
        } catch (unauthorizedException $unauthorizedException) {
            $this->writeException($unauthorizedException, abstractResponseObject::HTTP_STATUS_401);
        } catch (forbiddenException $forbiddenException) {
            $this->writeException($forbiddenException, abstractResponseObject::HTTP_STATUS_403);
        } catch (entityNotFoundException $entityNotFoundException) {
            $this->writeException($entityNotFoundException, abstractResponseObject::HTTP_STATUS_404);
        } catch (Error $error) {
            $message = $error->getMessage() . ' in file ' . $error->getFile() . ':' . $error->getLine();
            $this->loggerWriteError(errors::FATAL_RENDER_ERROR, 'FATAL ERROR while rendering. ' . $message, null, $error);
            $exception = new Exception('Server error', errors::FATAL_RENDER_ERROR, $error);
            $this->writeException($exception);
        } catch (Exception $exception) {
            $this->writeException($exception);
        }

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
     * @param Throwable $e
     * @param string $httpStatusCode
     * @throws JsonException
     */
    public function writeException(Throwable $e, string $httpStatusCode = abstractResponseObject::HTTP_STATUS_500): void {
        $error = new errorResponse($httpStatusCode, $e->getMessage(), $e->getCode());

        $GLOBALS['http_response_code'] = $httpStatusCode;

        header(dataResponse::generateProtocol() . ' ' . $httpStatusCode . ' ' . $error->generateText());

        echo $error->toJson();
        exit;
    }
}