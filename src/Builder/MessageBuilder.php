<?php

namespace BinaryCube\CarrotMQ\Builder;

use Interop\Amqp\Impl\AmqpMessage;

/**
 * Class MessageBuilder
 */
class MessageBuilder
{

    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function body(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function properties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function headers(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return AmqpMessage
     */
    public function build()
    {
        $message = new AmqpMessage();

        $message->setBody($this->body);
        $message->setProperties($this->properties);
        $message->setHeaders($this->headers);

        return $message;
    }

}
