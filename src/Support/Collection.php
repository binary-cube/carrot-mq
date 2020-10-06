<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Support;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Class Collection
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param array $items
     *
     * @return static
     */
    public static function make(array $items = []): self
    {
        return (new static($items));
    }

    /**
     * Constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Results array of items from Collection.
     *
     * @param mixed $items
     *
     * @return array
     */
    protected function getArrayableItems($items)
    {
        $arr = $items;

        if (\is_array($items)) {
            return $items;
        } elseif ($items instanceof Collection) {
            $arr = $items->all();
        } elseif ($items instanceof Traversable) {
            $arr = \iterator_to_array($items);
        }

        return static::mergeWith([], (array) $arr);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return static
     */
    public function keys()
    {
        return new static(\array_keys($this->items));
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return static
     */
    public function values()
    {
        return new static(\array_values($this->items));
    }

    /**
     * @param mixed $values
     *
     * @return $this
     */
    public function add($values): self
    {
        $assoc = self::isAssociative($this->items);

        foreach ($values as $key => $value) {
            $this->offsetSet($assoc ? $key : null, $value);
        }

        return $this;
    }

    /**
     * @param string|null $key
     * @param mixed       $value
     *
     * @return $this
     */
    public function put(?string $key, $value): self
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return $default;
    }

    /**
     * @param string     $id
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function getIfSet(string $id, $default = null)
    {
        if ($this->has($id)) {
            return $this->items[$id];
        }

        return $default;
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function overwrite(array $items): self
    {
        $this->items = $this->getArrayableItems($items);

        return $this;
    }

    /**
     * Delete the given key or keys.
     *
     * @param integer|string|array $keys
     *
     * @return $this
     */
    public function forget($keys): self
    {
        $this->offsetUnset($keys);

        return $this;
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
    public function merge(array $a): self
    {
        $args = $this->getArrayableItems(\func_get_args());

        \array_unshift($args, $this->items);

        $this->items = \forward_static_call_array([static::class, 'mergeWith'], $args);

        return $this;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        $keys = (array) $key;

        foreach ($keys as $value) {
            if (! $this->offsetExists($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return (
            isset($this->items[$key])
            || \array_key_exists($key, $this->items)
        );
    }

    /**
     * @param mixed $key
     *
     * @return mixed|void
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (! isset($key)) {
            $this->items[] = $value;
            return;
        }

        $this->items[$key] = $value;
    }

    /**
     * Delete the given key or keys.
     *
     * @param $keys
     *
     * @return void
     */
    public function offsetUnset($keys)
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            if ($this->offsetExists($key)) {
                unset($this->items[$key]);
            }
        }
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Returns a value indicating whether the given array is an associative array.
     *
     * An array is associative if all its keys are strings. If `$allStrings` is false,
     * then an array will be treated as associative if at least one of its keys is a string.
     *
     * Note that an empty array will NOT be considered associative.
     *
     * @param array $array      the array being checked
     * @param bool  $allStrings whether the array keys must be all strings in order for
     *                          the array to be treated as associative.
     *
     * @return bool whether the array is associative
     */
    protected static function isAssociative(array $array, bool $allStrings = true)
    {
        if (! \is_array($array) || empty($array)) {
            return false;
        }

        if ($allStrings) {
            foreach ($array as $key => $value) {
                if (! \is_string($key)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($array as $key => $value) {
            if (\is_string($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return array|mixed
     */
    protected static function mergeWith(array $a, array $b)
    {
        $args = \func_get_args();
        $res  = \array_shift($args);

        while (! empty($args)) {
            foreach (\array_shift($args) as $k => $v) {
                if (\is_int($k)) {
                    if (\array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (\is_array($v) && isset($res[$k]) && \is_array($res[$k])) {
                    $res[$k] = static::mergeWith($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

}
