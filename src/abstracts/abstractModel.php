<?php
namespace carlonicora\minimalism\modules\jsonapi\api\abstracts;

use carlonicora\minimalism\core\modules\abstracts\models\abstractApiModel;
use carlonicora\minimalism\core\services\exceptions\serviceNotFoundException;
use carlonicora\minimalism\core\services\factories\servicesFactory;
use carlonicora\minimalism\services\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\services\jsonapi\responses\dataResponse;
use carlonicora\minimalism\services\jsonapi\responses\errorResponse;

abstract class abstractModel extends abstractApiModel {
    /** @var dataResponse  */
    protected dataResponse $response;

    /** @var errorResponse|null  */
    protected ?errorResponse $error=null;

    /**
     * model constructor.
     * @param servicesFactory $services
     * @param array $passedParameters
     * @param string $verb
     * @param array $file
     * @throws serviceNotFoundException
     */
    public function __construct(servicesFactory $services, array $passedParameters, string $verb, array $file=null){
        parent::__construct($services, $passedParameters, $verb, $file);
        $this->response = new dataResponse();
    }

    /**
     * @return responseInterface
     */
    public function DELETE(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return responseInterface
     */
    public function GET(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return responseInterface
     */
    public function POST(): responseInterface {
        return new errorResponse(errorResponse::HTTP_STATUS_405);
    }

    /**
     * @return responseInterface
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