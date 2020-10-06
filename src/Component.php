<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;

/**
 * Class Component
 */
class Component
{

    /**
     * @var string
     */
    protected $id;

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
        $this->id     = (! empty($id) ? $id : self::generateUniqueId());
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
     * @return void
     */
    public function __destruct()
    {
        unset($this->id, $this->logger);
    }

}
