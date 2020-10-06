<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

/**
 * Interface ConsumerInterface
 */
interface ConsumerInterface
{

    /**
     * @return integer
     */
    public function consume();

}
