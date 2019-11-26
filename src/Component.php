<?php

namespace BinaryCube\CarrotMQ;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Component
 *
 * @package BinaryCube\CarrotMQ
 */
class Component
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var EventDispatcher
     */
    protected $events;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return string
     */
    public static function generateUniqueId()
    {
        return \vsprintf('%s.%s', [static::class, \uniqid('', true)]);
    }

    /**
     * Constructor.
     *
     * @param string|null          $id
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $id = null, $logger = null)
    {
        $this->id     = (!empty($id) ? $id : self::generateUniqueId());
        $this->events = new EventDispatcher();
        $this->logger = empty($logger) ? new NullLogger() : $logger;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function resetEvents()
    {
        $this->events = new EventDispatcher();

        return $this;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function logger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

}
