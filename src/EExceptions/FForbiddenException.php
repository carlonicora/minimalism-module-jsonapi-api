<?php
namespace carlonicora\minimalism\modules\jsonapi\api\exceptions;

use RuntimeException;
use Throwable;

class FForbiddenException extends RuntimeException {

    /**
     * forbiddenException constructor.
     * @param string $code
     * @param Throwable|null $previous
     */
    public function __construct(string $code, Throwable $previous = null) {
        parent::__construct('Access denied', $code, $previous);
    }
}