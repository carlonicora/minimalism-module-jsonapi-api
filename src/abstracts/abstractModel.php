<?php
namespace carlonicora\minimalism\modules\jsonapi\api\abstracts;

use carlonicora\minimalism\core\modules\abstracts\models\abstractApiModel;
use carlonicora\minimalism\core\services\exceptions\configurationException;
use carlonicora\minimalism\core\services\exceptions\serviceNotFoundException;
use carlonicora\minimalism\core\services\factories\servicesFactory;
use carlonicora\minimalism\services\encrypter\encrypter;
use carlonicora\minimalism\services\jsonapi\interfaces\jsonapiModelInterface;
use carlonicora\minimalism\services\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\services\jsonapi\responses\dataResponse;
use carlonicora\minimalism\services\jsonapi\responses\errorResponse;
use carlonicora\minimalism\services\jsonapi\traits\modelTrait;
use carlonicora\minimalism\services\MySQL\exceptions\dbRecordNotFoundException;
use carlonicora\minimalism\services\MySQL\exceptions\dbSqlException;
use Exception;

abstract class abstractModel extends abstractApiModel implements jsonapiModelInterface {
    use modelTrait;

    /** @var dataResponse  */
    protected dataResponse $response;

    /** @var errorResponse|null  */
    protected ?errorResponse $error=null;

    /** @var encrypter */
    protected $encrypter;

    /**
     * model constructor.
     * @param servicesFactory $services
     * @param array $passedParameters
     * @param string $verb
     * @param array $file
     * @throws serviceNotFoundException
     * @throws Exception
     */
    public function __construct(servicesFactory $services, array $passedParameters, string $verb, array $file=null) {
        // Encrypter should be initialises before calling parent construct
        $this->encrypter = $services->service(encrypter::class);
        parent::__construct($services, $passedParameters, $verb, $file);
        $this->response = new dataResponse();
    }

    /**
     * @param string $parameter
     * @return string
     */
    protected function decryptParameter(string $parameter) : string {
        // Override this method if you need decryption
        return $this->encrypter->decryptId($parameter);
    }

    /**
     * @return responseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws dbRecordNotFoundException
     * @throws dbSqlException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function DELETE(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return responseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws dbRecordNotFoundException
     * @throws dbSqlException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function GET(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return responseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws dbRecordNotFoundException
     * @throws dbSqlException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function POST(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return responseInterface
     * @throws serviceNotFoundException
     * @throws configurationException
     * @throws dbRecordNotFoundException
     * @throws dbSqlException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function PUT(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return errorResponse|null
     */
    public function preRender() : ?errorResponse {
        return $this->error;
    }
}