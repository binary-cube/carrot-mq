<?php

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Consumer;

/**
 * Class ConsumerList
 *
 * @package BinaryCube\CarrotMQ\Collection
 *
 * @method Consumer[] __invoke()
 * @method $this add(string $id, Consumer $item)
 * @method $this remove(string $id)
 * @method Consumer get(string $id)
 * @method Consumer[] all()()
 * @method $this clear()
 * @method bool has(string $id)
 */
class ConsumerList extends BaseList
{
}
