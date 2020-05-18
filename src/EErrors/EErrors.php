<?php
namespace carlonicora\minimalism\modules\jsonapi\api\errors;

use carlonicora\minimalism\services\logger\abstracts\abstractErrors;

class EErrors extends abstractErrors {
    /** @var string */
    public const LOGGER_SERVICE_NAME = 'minimalism-module-jsonapi-api';

    /** @var int */
    public const FATAL_INITIALIZE_ERROR = 1;
    /** @var int */
    public const FATAL_RENDER_ERROR = 2;
    /** @var int */
    public const SERVICE_CACHE_ERROR = 3;
}