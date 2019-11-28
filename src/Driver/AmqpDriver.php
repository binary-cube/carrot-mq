<?php

namespace BinaryCube\CarrotMQ\Driver;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\Config;
use Interop\Amqp\AmqpConnectionFactory;
use Enqueue\AmqpLib\AmqpConnectionFactory   as AMQPLibConnectionFactory;
use Enqueue\AmqpExt\AmqpConnectionFactory   as AMQPExtConnectionFactory;
use Enqueue\AmqpBunny\AmqpConnectionFactory as AMQPBunnyConnectionFactory;
use BinaryCube\CarrotMQ\Exception\Exception;
use BinaryCube\CarrotMQ\Exception\ClassNotFoundException;
use BinaryCube\CarrotMQ\Exception\InvalidConfigException;

/**
 * Class AmqpDriver
 *
 * @package BinaryCube\CarrotMQ\Driver
 */
class AmqpDriver extends Driver
{

    /**
     * @see https://github.com/php-amqplib/php-amqplib
     */
    const EXTENSION_AMQP_LIB = 'amqp-lib';

    /**
     * @see https://pecl.php.net/package/amqp
     */
    const EXTENSION_AMQP_EXT = 'amqp-ext';

    /**
     * @see https://github.com/jakubkulhan/bunny
     */
    const EXTENSION_AMQP_BUNNY = 'amqp-bunny';

    /**
     * @const array Default driver parameters
     */
    const DEFAULTS = [
        'extension'          => self::EXTENSION_AMQP_LIB,

        'dsn'                => null,
        'host'               => '127.0.0.1',
        'port'               => 5672,
        'username'           => 'guest',
        'password'           => 'guest',
        'vhost'              => '/',

        'stream'             => true,

        'read_timeout'       => 3.,
        'write_timeout'      => 3.,
        'connection_timeout' => 3.,
        'heartbeat'          => null,
        'persisted'          => null,

        'lazy'               => null,

        'qos_global'         => null,
        'qos_prefetch_size'  => null,
        'qos_prefetch_count' => null,

        'ssl_on'             => null,
        'ssl_verify'         => null,
        'ssl_cacert'         => null,
        'ssl_cert'           => null,
        'ssl_key'            => null,
    ];

    /**
     * List of supported AMQP interop extensions.
     *
     * @var string[]
     */
    protected $extensions = [
        self::EXTENSION_AMQP_LIB   => AMQPLibConnectionFactory::class,
        self::EXTENSION_AMQP_EXT   => AMQPExtConnectionFactory::class,
        self::EXTENSION_AMQP_BUNNY => AMQPBunnyConnectionFactory::class,
    ];

    /**
     * Constructor.
     *
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct($config = [], $logger = null)
    {
        parent::__construct($config, $logger);

        $this->logger->debug(\vsprintf('Instance of "%s" has been created', [self::class]));
    }

    /**
     * @return AmqpConnectionFactory
     *
     * @throws ClassNotFoundException
     * @throws InvalidConfigException
     */
    protected function build()
    {
        $config = $this->config;

        if ($diff = \array_diff(\array_keys($config), \array_keys(static::DEFAULTS))) {
            throw new InvalidConfigException(
                \vsprintf(
                    'Cannot create driver %s, received unknown arguments: %s!',
                    [
                        (string) self::class,
                        \implode(', ', $diff),
                    ]
                )
            );
        }

        $config = Config::create(static::DEFAULTS)->mergeWith($config)->toArray();

        $extension = $config['extension'];

        if (empty($extension) || !isset($this->extensions[$extension])) {
            throw new InvalidConfigException(
                \vsprintf(
                    'The given extension "%s" is not supported. Extensions supported are "%s"',
                    [
                        $extension,
                        \implode('", "', \array_keys($this->extensions)),
                    ]
                )
            );
        }

        $config['user'] = $config['username'];
        $config['pass'] = $config['password'];

        // Remove unused properties.
        unset($config['extension'], $config['username'], $config['password']);

        // Remove the attributes with null value.
        $config = \array_filter(
            $config,
            function ($value) {
                return null !== $value;
            }
        );

        $class = $this->extensions[$extension];

        if (!\class_exists($class)) {
            throw new ClassNotFoundException(\vsprintf('Class %s not found.', [$class]));
        }

        /* @var AmqpConnectionFactory $connection */
        $connection = new $class($config);

        return $connection;
    }

}
