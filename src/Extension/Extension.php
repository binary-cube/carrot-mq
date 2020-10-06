<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Extension;

use BinaryCube\CarrotMQ\Component;
use BinaryCube\CarrotMQ\Support\LoggerAwareTrait;

/**
 * Class Extension
 */
abstract class Extension extends Component implements ExtensionInterface
{
    use LoggerAwareTrait;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

}
