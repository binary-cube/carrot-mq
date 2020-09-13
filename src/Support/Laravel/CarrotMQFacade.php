<?php

namespace BinaryCube\CarrotMQ\Support\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Class CarrotMQFacade
 */
class CarrotMQFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'carrot.mq';
    }

}
