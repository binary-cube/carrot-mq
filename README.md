# 🥕 Carrot-MQ :: Base on [Enqueue][link-enqueue]


<p align="center">~ Enjoy your :coffee: ~</p>

[![Minimum PHP Version `PHP >= 7.1`][ico-php-require]][link-php-site]
[![Latest Stable Version][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![Code Coverage][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![License][ico-license]][link-license]

-----



## Requirements
This package requires the following
- php >= 7.0
- ext-bcmath
- ext-sockets




## Installing

- **via "composer require"**:

    ``` shell
    composer require binary-cube/carrot-mq
    ```

- **via composer (manually)**:

    If you're using Composer to manage dependencies, you can include the following
    in your `composer.json` file:

    ```json
    {
        "require": {
            "binary-cube/carrot-mq": "0.*"
        }
    }
    ```

## Example of usage
```php
<?php

use BinaryCube\CarrotMQ\CarrotMQ;
use BinaryCube\CarrotMQ\Driver\AmqpDriver;
use BinaryCube\CarrotMQ\Processor\Processor;
use BinaryCube\CarrotMQ\Extension\SignalExtension;

include __DIR__ . '/vendor/autoload.php';

$config = [
    'connections' => [
        'default' => [
            'config' => [
                'extension' => AmqpDriver::EXTENSION_AMQP_LIB,
                'host'      => '127.0.0.1',
                'port'      => '5672',
                'username'  => 'guest',
                'password'  => 'guest',
                'vhost'     => '/',
                'persisted' => false,
            ],
        ],
    ],

    'topics' => [
        'topic-id-1' => [
            'name'       => 't1',
            'connection' => 'default',
        ],
    ],

    'queues' => [
        'queue-id-1' => [
            'name'       => 'foo',
            'connection' => 'default',
            'config' => [
                'durable' => true,
                'bind' => [
                    [
                        'topic'       => 't1',
                        'routing_key' => 'some-routing-key',
                    ],
                 ],
            ],
        ],
    ],

    'publishers' => [
        'publisher-1' => [
            'topic'  => 'topic-id-1',
            'config' => [],
        ],
    ],

    'consumers' => [
        'c1' => [
            'queue'      => 'queue-id-1',
            'processor'  => function (\Interop\Amqp\AmqpMessage $message) {
                echo \vsprintf('Message body size: %s', [\mb_strlen($message->getBody())]) . PHP_EOL;
                return Processor::REQUEUE;
            },
            'config'     => [
                'receive_timeout' => 30,
                'qos' => [
                    'enabled'        => false,
                    'prefetch_size'  => 0,
                    'prefetch_count' => 0,
                    'global'         => false,
                ]
            ],
        ],
    ],
];

$carrot = new CarrotMQ($config);

$consumer = $carrot->container()->consumers()->get('c1');

$extension = new SignalExtension();


/**
 * @var BinaryCube\CarrotMQ\Consumer $consumer
 */
$consumer->extensions()->add($extension::name(), $extension);

$consumer->consume();
```


## Bugs and feature requests

Have a bug or a feature request? 
Please first read the issue guidelines and search for existing and closed issues. 
If your problem or idea is not addressed yet, [please open a new issue][link-new-issue].




## Contributing guidelines

All contributions are more than welcomed. 
Contributions may close an issue, fix a bug (reported or not reported), add new design blocks, 
improve the existing code, add new feature, and so on. 
In the interest of fostering an open and welcoming environment, 
we as contributors and maintainers pledge to making participation in our project and our community a harassment-free experience for everyone, 
regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, 
personal appearance, race, religion, or sexual identity and orientation. 
[Read the full Code of Conduct][link-code-of-conduct].




#### Versioning

Through the development of new versions, we're going use the [Semantic Versioning][link-semver]. 

Example: `1.0.0`.
- Major release: increment the first digit and reset middle and last digits to zero. Introduces major changes that might break backward compatibility. E.g. 2.0.0
- Minor release: increment the middle digit and reset last digit to zero. It would fix bugs and also add new features without breaking backward compatibility. E.g. 1.1.0
- Patch release: increment the third digit. It would fix bugs and keep backward compatibility. E.g. 1.0.1




## Authors

* **Banciu N. Cristian Mihai**

See also the list of [contributors][link-contributors] who participated in this project.




## License

This project is licensed under the MIT License - see the [LICENSE][link-license] file for details.




<!-- Links -->
[ico-php-require]:          https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg?style=flat-square
[ico-version]:              https://img.shields.io/packagist/v/binary-cube/carrot-mq.svg?style=flat-square
[ico-downloads]:            https://img.shields.io/packagist/dt/binary-cube/carrot-mq.svg?style=flat-square
[ico-travis]:               https://img.shields.io/travis/binary-cube/carrot-mq/master.svg?style=flat-square
[ico-scrutinizer]:          https://img.shields.io/scrutinizer/coverage/g/binary-cube/carrot-mq.svg?style=flat-square
[ico-code-quality]:         https://img.shields.io/scrutinizer/g/binary-cube/carrot-mq.svg?style=flat-square
[ico-license]:              https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-domain]:              https://binary-cube.com
[link-homepage]:            https://binary-cube.com
[link-git-source]:          https://github.com/binary-cube/carrot-mq
[link-packagist]:           https://packagist.org/packages/binary-cube/carrot-mq
[link-downloads]:           https://packagist.org/packages/binary-cube/carrot-mq
[link-php-site]:            https://php.net
[link-semver]:              https://semver.org
[link-code-of-conduct]:     https://github.com/binary-cube/carrot-mq/blob/master/code-of-conduct.md
[link-license]:             https://github.com/binary-cube/carrot-mq/blob/master/LICENSE
[link-contributors]:        https://github.com/binary-cube/carrot-mq/graphs/contributors
[link-new-issue]:           https://github.com/binary-cube/carrot-mq/issues/new
[link-travis]:              https://travis-ci.org/binary-cube/carrot-mq
[link-scrutinizer]:         https://scrutinizer-ci.com/g/binary-cube/carrot-mq/code-structure
[link-code-quality]:        https://scrutinizer-ci.com/g/binary-cube/carrot-mq
[link-enqueue]:             https://github.com/php-enqueue
