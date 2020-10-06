<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Driver;

use Psr\Log\LoggerInterface;
use Interop\Amqp\AmqpContext;
use BinaryCube\CarrotMQ\Component;
use Interop\Amqp\AmqpConnectionFactory;
use BinaryCube\CarrotMQ\Support\LoggerAwareTrait;

/**
 * Class Driver
 */
abstract class Driver extends Component
{
    use LoggerAwareTrait;

    /**
     * @const array Default driver parameters
     */
    const DEFAULTS = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var AmqpConnectionFactory
     */
    protected $interop;

    /**
     * @var AmqpContext
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct($config = [], $logger = null)
    {
        parent::__construct(null, $logger);

        $this->config = $config;
    }

    /**
     * @return AmqpConnectionFactory
     */
    abstract protected function build();

    /**
     * @return AmqpConnectionFactory
     */
    public function interop()
    {
        return $this->interop;
    }

    /**
     * @param bool $new
     *
     * @return AmqpContext
     */
    public function context($new = false)
    {
        if (empty($this->context) || $new) {
            $this->context = $this->interop->createContext();
        }

        return $this->context;
    }

    /**
     * @return $this
     */
    public function open()
    {
        if (! empty($this->interop)) {
            return $this;
        }

        $this->interop = $this->build();

        return $this;
    }

    /**
     * @return $this
     */
    public function close()
    {
        if ($this->context) {
            try {
                $this->context->close();
            } catch (\Exception $e) {
                /* Ignore on shutdown. */
            }
        }

        $this->context = null;
        $this->interop = null;

        return $this;
    }

    /**
     * @return $this
     */
    public function reconnect()
    {
        $this->close()->open();

        return $this;
    }

}
