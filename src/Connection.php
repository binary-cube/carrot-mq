<?php

namespace BinaryCube\CarrotMQ;

use Psr\Log\LoggerInterface;
use Interop\Amqp\AmqpContext;
use BinaryCube\CarrotMQ\Driver\Driver;
use Interop\Amqp\AmqpConnectionFactory;
use BinaryCube\CarrotMQ\Driver\AmqpDriver;

/**
 * Class Connection
 *
 * @package BinaryCube\CarrotMQ
 */
class Connection extends Component
{

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @const array Default connections parameters
     */
    const DEFAULTS = [
        'config' => [],
    ];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param string               $id
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $id, $config = [], $logger = null)
    {
        parent::__construct($id, $logger);

        $this->config = Config::create(static::DEFAULTS)->mergeWith($config)->toArray();

        $this->driver = new AmqpDriver((array) $this->config['config'], $this->logger);

        $this->open();
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function logger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->driver->logger($this->logger);

        return $this;
    }

    /**
     * @return Driver
     */
    public function driver()
    {
        return $this->driver;
    }

    /**
     * @return AmqpConnectionFactory
     */
    public function interop()
    {
        return $this->driver->interop();
    }

    /**
     * @param bool $new
     *
     * @return AmqpContext
     */
    public function context($new = false)
    {
        return $this->driver->context($new);
    }

    /**
     * Open the connection.
     *
     * @return $this
     */
    public function open()
    {
        $this->driver->open();

        return $this;
    }

    /**
     * Close the connection.
     *
     * @return $this
     */
    public function close()
    {
        $this->driver->close();

        return $this;
    }

    /**
     * Close and open the connection.
     *
     * @return $this
     */
    public function reconnect()
    {
        $this->driver->reconnect();

        return $this;
    }

}
