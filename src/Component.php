<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;

use function uniqid;
use function vsprintf;

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
    public static function generateUniqueId(): string
    {
        return vsprintf('%s.%s', [static::class, uniqid('', true)]);
    }

    /**
     * Constructor.
     *
     * @param string|null          $id
     * @param LoggerInterface|null $logger
     */
    public function __construct(?string $id = null, ?LoggerInterface $logger = null)
    {
        $this->id     = (! empty($id) ? $id : self::generateUniqueId());
        $this->logger = (! empty($logger) ? $logger : new NullLogger());
    }

    /**
     * @return string
     */
    public function id(): string
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
