<?php

namespace BinaryCube\CarrotMQ;

/**
 * Class Config
 *
 * @package BinaryCube\CarrotMQ
 */
class Config
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     *
     * @return static
     */
    public static function create(array $config = [])
    {
        return (new static($config));
    }

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function set(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->toArray();
    }

    /**
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     *
     * @param array $a array to be merged from. You can specify additional
     *                 arrays via second argument, third argument, fourth argument etc.
     *
     * @return $this
     */
    public function mergeWith($a)
    {
        $this->config = $this->merge($this->config, $a);

        return $this;
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return array|mixed
     */
    protected static function merge($a, $b)
    {
        $args = \func_get_args();
        $res  = \array_shift($args);

        while (!empty($args)) {
            foreach (\array_shift($args) as $k => $v) {
                if (\is_int($k)) {
                    if (\array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (\is_array($v) && isset($res[$k]) && \is_array($res[$k])) {
                    $res[$k] = static::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

}
