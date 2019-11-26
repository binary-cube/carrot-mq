<?php

namespace BinaryCube\CarrotMQ;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Collection\BaseList;
use BinaryCube\CarrotMQ\Collection\QueueList;
use BinaryCube\CarrotMQ\Collection\TopicList;
use BinaryCube\CarrotMQ\Collection\ConsumerList;
use BinaryCube\CarrotMQ\Collection\PublisherList;
use BinaryCube\CarrotMQ\Collection\ConnectionList;

/**
 * Class Container
 *
 * @package BinaryCube\CarrotMQ
 */
class Container
{

    /**
     * @var ConnectionList
     */
    private $connections;

    /**
     * @var TopicList
     */
    private $topics;

    /**
     * @var QueueList
     */
    private $queues;

    /**
     * @var PublisherList
     */
    private $publishers;

    /**
     * @var ConsumerList
     */
    private $consumers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connections = new ConnectionList();
        $this->topics      = new TopicList();
        $this->queues      = new QueueList();
        $this->publishers  = new PublisherList();
        $this->consumers   = new ConsumerList();
    }

    /**
     * @return ConnectionList
     */
    public function connections()
    {
        return $this->connections;
    }

    /**
     * @return TopicList
     */
    public function topics()
    {
        return $this->topics;
    }

    /**
     * @return QueueList
     */
    public function queues()
    {
        return $this->queues;
    }

    /**
     * @return PublisherList
     */
    public function publishers()
    {
        return $this->publishers;
    }

    /**
     * @return ConsumerList
     */
    public function consumers()
    {
        return $this->consumers;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this;
     */
    public function logger(LoggerInterface $logger)
    {
        $contexts = [
            'connections',
            'topics',
            'queues',
            'publishers',
            'consumers',
        ];

        foreach ($contexts as $context) {
            /* @var BaseList $context */
            foreach ($this->{$context}->all() as $entry) {
                /* @var Component $entry */
                $entry->logger($logger);
            }
        }

        return $this;
    }

}
