<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class Exception
 */
class Exception extends \Exception
{

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Exception';
    }

}
