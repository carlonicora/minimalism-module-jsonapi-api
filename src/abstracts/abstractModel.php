<?php
namespace carlonicora\minimalism\modules\jsonapi\api\abstracts;

use carlonicora\minimalism\core\modules\abstracts\models\abstractApiModel;
use carlonicora\minimalism\service\jsonapi\interfaces\responseInterface;
use carlonicora\minimalism\service\jsonapi\responses\dataResponse;
use carlonicora\minimalism\service\jsonapi\responses\errorResponse;

abstract class abstractModel extends abstractApiModel {
    /** @var dataResponse  */
    protected dataResponse $response;

    /** @var errorResponse|null  */
    protected ?errorResponse $error=null;

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
}