<?php

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
