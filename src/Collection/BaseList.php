<?php

namespace BinaryCube\CarrotMQ\Collection;

/**
 * Class BaseList
 */
class BaseList
{

    /**
     * @var mixed[]
     */
    private $items = [];

    /**
     * @return mixed[]
     */
    public function __invoke()
    {
        return $this->all();
    }

    /**
     * @param string $id
     * @param mixed  $item
     *
     * @return $this
     */
    public function add($id, $item)
    {
        $this->items[$id] = $item;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function remove($id)
    {
        unset($this->items[$id]);

        return $this;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function get($id)
    {
        return $this->items[$id];
    }

    /**
     * @return mixed[]
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->items = [];

        return $this;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return (isset($this->items[$id]) || \array_key_exists($id, $this->items));
    }

}
