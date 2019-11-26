<?php

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class StopConsumerException
 *
 * @package BinaryCube\CarrotMQ\Exception
 */
class StopConsumerException extends Exception
{

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Stop Consumer';
    }

}
