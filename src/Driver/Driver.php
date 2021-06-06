<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Driver;

use Throwable;
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

    const STATE_OPEN  = 'open';
    const STATE_CLOSE = 'close';

    /**
     * @var string
     */
    private $state = self::STATE_CLOSE;

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
    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        parent::__construct(null, $logger);

        $this->config = $config;
    }

    /**
     * @return AmqpConnectionFactory
     */
    public function interop(): AmqpConnectionFactory
    {
        return $this->interop;
    }

    /**
     * @param bool $new
     *
     * @return AmqpContext
     */
    public function context(bool $new = false): AmqpContext
    {
        if ($new || empty($this->context)) {
            $this->context = $this->interop->createContext();
        }

        return $this->context;
    }

    /**
     * @return string
     */
    public function state(): string
    {
        return $this->state;
    }

    /**
     * @return $this
     */
    public function open()
    {
        if ($this->state === self::STATE_OPEN) {
            return $this;
        }

        $this->interop = $this->build();
        $this->state   = self::STATE_OPEN;

        return $this;
    }

    /**
     * @return $this
     */
    public function close()
    {
        try {
            $this->context->close();
        } catch (Throwable $e) {
            /* Ignore on shutdown. */
        }

        $this->context = null;
        $this->interop = null;
        $this->state   = self::STATE_CLOSE;

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

    /**
     * @return AmqpConnectionFactory
     */
    abstract protected function build(): AmqpConnectionFactory;

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();

        unset(
            $this->state,
            $this->config,
            $this->context,
            $this->interop
        );
    }

}
