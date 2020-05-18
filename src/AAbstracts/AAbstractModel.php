<?php
namespace carlonicora\minimalism\modules\jsonapi\api\abstracts;

use carlonicora\minimalism\core\modules\abstracts\models\abstractApiModel;
use carlonicora\minimalism\core\services\exceptions\configurationException;
use carlonicora\minimalism\core\services\exceptions\serviceNotFoundException;
use carlonicora\minimalism\core\services\factories\servicesFactory;
use carlonicora\minimalism\services\encrypter\encrypter;
use carlonicora\minimalism\services\jsonapi\interfaces\jsonapiModelInterface;
use carlonicora\minimalism\services\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\services\jsonapi\jsonApiDocument;
use carlonicora\minimalism\services\jsonapi\resources\errorObject;
use carlonicora\minimalism\services\jsonapi\traits\modelTrait;
use carlonicora\minimalism\services\MySQL\exceptions\dbRecordNotFoundException;
use carlonicora\minimalism\services\MySQL\exceptions\dbSqlException;
use Exception;

abstract class AAbstractModel extends abstractApiModel implements jsonapiModelInterface {
    use modelTrait;

    /** @var jsonApiDocument  */
    protected jsonApiDocument $response;

    /** @var errorObject|null  */
    protected ?errorObject $error=null;

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
        $this->encrypter = $services->service(encrypter::class);

        parent::__construct($services, $passedParameters, $verb, $file);

        $this->response = new jsonApiDocument();
    }

    /**
     * @param string $parameter
     * @return string
     */
    public function decryptParameter(string $parameter) : string {
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
        $this->response->addError(new errorObject(
            responseInterface::HTTP_STATUS_405,
            responseInterface::HTTP_STATUS_405
        ));

        return $this->response;
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
        $this->response->addError(new errorObject(
            responseInterface::HTTP_STATUS_405,
            responseInterface::HTTP_STATUS_405
        ));

        return $this->response;
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
        $this->response->addError(new errorObject(
            responseInterface::HTTP_STATUS_405,
            responseInterface::HTTP_STATUS_405
        ));

        return $this->response;
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
        $this->response->addError(new errorObject(
            responseInterface::HTTP_STATUS_405,
            responseInterface::HTTP_STATUS_405
        ));

        return $this->response;
    }

    /**
     * @return errorObject|null
     */
    public function preRender() : ?errorObject {
        return $this->error;
    }

    /**
     * @param $parameter
     * @return jsonApiDocument
     */
    public function validateJsonapiParameter($parameter): jsonApiDocument{
        return new jsonApiDocument($parameter);
    }
}