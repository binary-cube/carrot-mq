<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Consumer;
use BinaryCube\CarrotMQ\Support\Collection;

/**
 * Class ConsumerRepository
 *
 * @method Consumer[]     __invoke()
 * @method $this          put(string $id, Consumer $item)
 * @method $this          forget(string $id)
 * @method Consumer       get(string $id, $default = null)
 * @method Consumer|mixed getIfSet(string $id, $default = null)
 * @method Consumer[]     all()()
 * @method $this          clear()
 * @method bool           has(string $id)
 */
class ConsumerRepository extends Collection
{
    //
}
