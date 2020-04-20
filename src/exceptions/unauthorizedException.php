<?php
namespace carlonicora\minimalism\modules\jsonapi\api\exceptions;

use RuntimeException;
use Throwable;

class unauthorizedException extends RuntimeException {

    /**
     * unauthorizedException constructor.
     * @param string $code
     * @param Throwable|null $previous
     */
    public function __construct(string $code, Throwable $previous = null) {
        parent::__construct('Unauthorized', $code, $previous);
    }
}