<?php

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class InvalidConfigException
 *
 * InvalidConfigException represents an exception caused by incorrect object configuration.
 *
 * @package BinaryCube\CarrotMQ\Exception
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
