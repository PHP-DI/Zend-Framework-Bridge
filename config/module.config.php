<?php
/**
 * PHP-DI
 *
 * @link http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */
namespace DI\ZendFramework3;

return [
    'controllers' => [
        'factories' => [
            Controller\ConsoleController::class => Service\ConsoleControllerFactory::class,
        ]
    ],

    'service_manager' => [
        'abstract_factories' => [
            Service\PHPDIAbstractFactory::class => Service\PHPDIAbstractFactory::class,
        ],

        'factories' => [
            'ControllerManager' => Service\ControllerManagerFactory::class,
            'DiCache' => Service\CacheFactory\CacheFactory::class,
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'php-di-clear-cache' => [
                    'options' => [
                        'route'    => 'php-di-clear-cache',
                        'defaults' => [
                            'controller' => Controller\ConsoleController::class,
                            'action'     => 'clearCache',
                            '__NAMESPACE__' => __NAMESPACE__,
                        ]
                    ]
                ],
            ]
        ],
    ],

    'phpdi-zf3' => [
        'useAnntotations' => false,
    ],
];
