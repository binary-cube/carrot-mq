<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Entity\Queue;
use BinaryCube\CarrotMQ\Support\Collection;

/**
 * Class QueueRepository
 *
 * @method Queue[]     __invoke()
 * @method $this       put(string $id, Queue $item)
 * @method $this       forget(string $id)
 * @method Queue       get(string $id, $default = null)
 * @method Queue|mixed getIfSet(string $id, $default = null)
 * @method Queue[]     all()()
 * @method $this       clear()
 * @method bool        has(string $id)
 */
class QueueRepository extends Collection
{
    //
}
