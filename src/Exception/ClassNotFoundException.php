<?php

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class ClassNotFoundException
 *
 * @package BinaryCube\CarrotMQ\Exception
 */
class ClassNotFoundException extends Exception
{

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Class Not Found';
    }

}
