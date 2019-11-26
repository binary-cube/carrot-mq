<?php

namespace BinaryCube\CarrotMQ;

/**
 * Interface ConsumerInterface
 *
 * @package BinaryCube\CarrotMQ
 */
interface ConsumerInterface
{

    /**
     * @return integer
     */
    public function consume();

}
