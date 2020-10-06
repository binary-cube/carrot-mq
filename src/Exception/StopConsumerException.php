<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class StopConsumerException
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
