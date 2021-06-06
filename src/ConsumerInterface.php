<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

/**
 * Interface ConsumerInterface
 */
interface ConsumerInterface
{

    /**
     * @return int
     */
    public function consume(): int;

}
