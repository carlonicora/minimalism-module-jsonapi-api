<?php
namespace carlonicora\minimalism\modules\jsonapi\api\exceptions;

use RuntimeException;
use Throwable;

class entityNotFoundException extends RuntimeException {

    /**
     * entityNotFoundException constructor.
     * @param string $code
     * @param Throwable|null $previous
     */
    public function __construct(string $code, Throwable $previous = null) {
        parent::__construct('Not found', $code, $previous);
    }
}