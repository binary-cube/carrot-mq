<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class InvalidConfigException
 *
 * InvalidConfigException represents an exception caused by incorrect object configuration.
 */
class InvalidConfigException extends Exception
{

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }

}
