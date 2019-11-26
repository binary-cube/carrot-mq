<?php

namespace BinaryCube\CarrotMQ\Extension;

use BinaryCube\CarrotMQ\Component;

/**
 * Class Extension
 *
 * @package BinaryCube\CarrotMQ\Extension
 */
abstract class Extension extends Component implements ExtensionInterface
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

}
