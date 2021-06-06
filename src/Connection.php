<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ;

use Psr\Log\LoggerInterface;
use Interop\Amqp\AmqpContext;
use BinaryCube\CarrotMQ\Driver\Driver;
use Interop\Amqp\AmqpConnectionFactory;
use BinaryCube\CarrotMQ\Driver\AmqpDriver;
use BinaryCube\CarrotMQ\Support\Collection;
use BinaryCube\CarrotMQ\Support\LoggerAwareTrait;

/**
 * Class Connection
 */
class Connection extends Component
{
    use LoggerAwareTrait;

    /**
     * @const array Default connections parameters
     */
    const DEFAULTS = [
        'config' => [],
    ];

    /**
     * @var Driver
     */
    protected $driver;

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
    public function __construct(string $id, array $config = [], ?LoggerInterface $logger = null)
    {
        parent::__construct($id, $logger);

        $this->config = Collection::make(static::DEFAULTS)->merge($config)->all();
        $this->driver = new AmqpDriver((array) $this->config['config'], $this->logger);

        $this->open();
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->driver->setLogger($this->logger);

        return $this;
    }

    /**
     * @return Driver
     */
    public function driver(): Driver
    {
        return $this->driver;
    }

    /**
     * @return AmqpConnectionFactory
     */
    public function interop(): AmqpConnectionFactory
    {
        return $this->driver->interop();
    }

    /**
     * @param bool $new
     *
     * @return AmqpContext
     */
    public function context(bool $new = false): AmqpContext
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

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();

        unset(
            $this->driver,
            $this->config
        );
    }

}
