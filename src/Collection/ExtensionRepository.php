<?php

declare(strict_types=1);

namespace BinaryCube\CarrotMQ\Collection;

use BinaryCube\CarrotMQ\Extension\Extension;
use BinaryCube\CarrotMQ\Support\Collection;

/**
 * Class ExtensionRepository
 *
 * @method Extension[]     __invoke()
 * @method $this           put(string $id, Extension $item)
 * @method $this           forget(string $id)
 * @method Extension       get(string $id, $default = null)
 * @method Extension|mixed getIfSet(string $id, $default = null)
 * @method Extension[]     all()()
 * @method $this           clear()
 * @method bool            has(string $id)
 */
class ExtensionRepository extends Collection
{
    //
}
