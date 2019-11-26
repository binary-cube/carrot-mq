<?php

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Entity\Queue;

/**
 * Class QueueList
 *
 * @package BinaryCube\CarrotMQ\Collection
 *
 * @method Queue[] __invoke()
 * @method $this add(string $id, Queue $item)
 * @method $this remove(string $id)
 * @method Queue get(string $id)
 * @method Queue[] all()()
 * @method $this clear()
 * @method bool has(string $id)
 */
class QueueList extends BaseList
{
}
