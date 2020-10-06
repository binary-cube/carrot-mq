<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Entity\Topic;
use BinaryCube\CarrotMQ\Support\Collection;

/**
 * Class TopicRepository
 *
 * @method Topic[]     __invoke()
 * @method $this       put(string $id, Topic $item)
 * @method $this       forget(string $id)
 * @method Topic       get(string $id, $default = null)
 * @method Topic|mixed getIfSet(string $id, $default = null)
 * @method Topic[]     all()()
 * @method $this       clear()
 * @method bool        has(string $id)
 */
class TopicRepository extends Collection
{
    //
}
