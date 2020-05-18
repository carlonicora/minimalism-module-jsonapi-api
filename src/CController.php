<?php
namespace carlonicora\minimalism\modules\jsonapi\api;

use carlonicora\minimalism\core\bootstrapper;
use carlonicora\minimalism\core\modules\abstracts\controllers\abstractApiController;
use carlonicora\minimalism\core\services\exceptions\serviceNotFoundException;
use carlonicora\minimalism\core\services\factories\servicesFactory;
use carlonicora\minimalism\core\traits\httpHeaders;
use carlonicora\minimalism\modules\jsonapi\api\errors\EErrors;
use carlonicora\minimalism\modules\jsonapi\api\exceptions\EEntityNotFoundException;
use carlonicora\minimalism\modules\jsonapi\api\exceptions\FForbiddenException;
use carlonicora\minimalism\services\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\services\jsonapi\jsonApiDocument;
use carlonicora\minimalism\services\jsonapi\resources\errorObject;
use carlonicora\minimalism\services\logger\traits\logger;
use carlonicora\minimalism\services\security\exceptions\unauthorisedException;
use carlonicora\minimalism\services\security\security;
use Error;
use Exception;
use JsonException;
use Throwable;

class CController extends abstractApiController {
    use httpHeaders;
    use logger;

    /** @var jsonApiDocument  */
    private jsonApiDocument $response;

    /**
     * controller constructor.
     * @param servicesFactory $services
     * @param string|null $modelName
     * @param array|null $parameterValueList
     * @param array|null $parameterValues
     * @throws JsonException
     */
    public function __construct(servicesFactory $services, string $modelName=null, array $parameterValueList=null, array $parameterValues=null){
        $this->response = new jsonApiDocument();

        try {
            $this->loggerInitialise($services);
            parent::__construct($services, $modelName, $parameterValueList, $parameterValues);
            $this->validateSignature();
        } catch (unauthorisedException $unauthorisedException) {
            $this->writeException($unauthorisedException, responseInterface::HTTP_STATUS_401);
        } catch (FForbiddenException $forbiddenException) {
            $this->writeException($forbiddenException, responseInterface::HTTP_STATUS_403);
        } catch (EEntityNotFoundException $entityNotFoundException) {
            $this->writeException($entityNotFoundException, responseInterface::HTTP_STATUS_404);
        } catch (Error $error) {
            try {
                $message = $error->getMessage() . ' in file ' . $error->getFile() . ':' . $error->getLine();
                $this->loggerWriteError(EErrors::FATAL_INITIALIZE_ERROR, 'FATAL ERROR while initializing. ' . $message , null, $error);
            } catch (Throwable $throwable) {
                // Sad, but we can't log a fatal error
            }
            $exception = new Exception('Server error', EErrors::FATAL_INITIALIZE_ERROR, $error);
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
        $signature =$this->getHeader($security->getHttpHeaderSignature());

        $url = $_SERVER['REQUEST_URI'];

        $security->validateSignature($signature, $this->verb, $url, $this->bodyParameters, $security->getSecurityClient(), $security->getSecuritySession());
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
        /** @var errorObject $error  */
        if (($error = $this->model->preRender()) !== null){
            $this->response->addError($error);
        } else {
            /** @var responseInterface $apiResponse */
            try {
                $this->response = $this->model->{$this->verb}();
            } catch (unauthorisedException $unauthorisedException) {
                $this->writeException($unauthorisedException, responseInterface::HTTP_STATUS_401);
            } catch (FForbiddenException $forbiddenException) {
                $this->writeException($forbiddenException, responseInterface::HTTP_STATUS_403);
            } catch (EEntityNotFoundException $entityNotFoundException) {
                $this->writeException($entityNotFoundException, responseInterface::HTTP_STATUS_404);
            } catch (Error $error) {
                $message = $error->getMessage() . ' in file ' . $error->getFile() . ':' . $error->getLine();
                $this->loggerWriteError(EErrors::FATAL_RENDER_ERROR, 'FATAL ERROR while rendering. ' . $message, null, $error);
                $exception = new Exception('Server error', EErrors::FATAL_RENDER_ERROR, $error);
                $this->writeException($exception);
            } catch (Exception $exception) {
                $this->writeException($exception);
            }
        }

        $GLOBALS['http_response_code'] = $this->response->getStatus();

        header( jsonApiDocument::generateProtocol() . ' ' . $this->response->getStatus() . ' ' . $this->response->generateText());

        $this->services->destroyStatics();

        if (bootstrapper::$servicesCache !== null){
            try {
                file_put_contents(bootstrapper::$servicesCache, serialize($this->services));
            } catch (Throwable $exception) {
                $message = 'Services could not be cached. Services object:' . PHP_EOL . print_r($this->services, true);
                $this->loggerWriteError(EErrors::SERVICE_CACHE_ERROR, $message, EErrors::LOGGER_SERVICE_NAME, $exception);
            }
        }

        return $this->response->toJson();
    }

    /**
     * @param Throwable $e
     * @param string $httpStatusCode
     * @throws JsonException
     */
    public function writeException(Throwable $e, string $httpStatusCode = responseInterface::HTTP_STATUS_500): void {
        $this->response->addError(new errorObject($httpStatusCode, $httpStatusCode, $e->getMessage(), $e->getCode()));

        $GLOBALS['http_response_code'] = $httpStatusCode;

        header(jsonApiDocument::generateProtocol() . ' ' . $httpStatusCode . ' ' . $this->response->generateText());

        echo $this->response->toJson();
        exit;
    }
}