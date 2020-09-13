<?php

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class Exception
 */
class Exception extends \Exception
{

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Exception';
    }

}
