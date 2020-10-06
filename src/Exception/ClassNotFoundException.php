<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Exception;

/**
 * Class ClassNotFoundException
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
