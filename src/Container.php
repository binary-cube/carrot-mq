<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use BinaryCube\CarrotMQ\Collection\QueueRepository;
use BinaryCube\CarrotMQ\Collection\TopicRepository;
use BinaryCube\CarrotMQ\Collection\ConsumerRepository;
use BinaryCube\CarrotMQ\Collection\PublisherRepository;
use BinaryCube\CarrotMQ\Collection\ConnectionRepository;

/**
 * Class Container
 */
class Container
{

    /**
     * @var ConnectionRepository
     */
    private $connections;

    /**
     * @var TopicRepository
     */
    private $topics;

    /**
     * @var QueueRepository
     */
    private $queues;

    /**
     * @var PublisherRepository
     */
    private $publishers;

    /**
     * @var ConsumerRepository
     */
    private $consumers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connections = new ConnectionRepository();
        $this->topics      = new TopicRepository();
        $this->queues      = new QueueRepository();
        $this->publishers  = new PublisherRepository();
        $this->consumers   = new ConsumerRepository();
    }

    /**
     * @return ConnectionRepository
     */
    public function connections()
    {
        return $this->connections;
    }

    /**
     * @return TopicRepository
     */
    public function topics()
    {
        return $this->topics;
    }

    /**
     * @return QueueRepository
     */
    public function queues()
    {
        return $this->queues;
    }

    /**
     * @return PublisherRepository
     */
    public function publishers()
    {
        return $this->publishers;
    }

    /**
     * @return ConsumerRepository
     */
    public function consumers()
    {
        return $this->consumers;
    }

}
