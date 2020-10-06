<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Publisher;
use BinaryCube\CarrotMQ\Support\Collection;

/**
 * Class PublisherRepository
 *
 * @method Publisher[]     __invoke()
 * @method $this           put(string $id, Publisher $item)
 * @method $this           forget(string $id)
 * @method Publisher       get(string $id, $default = null)
 * @method Publisher|mixed getIfSet(string $id, $default = null)
 * @method Publisher[]     all()()
 * @method $this           clear()
 * @method bool            has(string $id)
 */
class PublisherRepository extends Collection
{
    //
}
