<?php
/**
 * @author     mfris
 * @copyright  Pixel federation
 * @license    Internal use only
 */

namespace DI\ZendFramework\Service;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * simple trait for getting php di config data from zf2 config array
 * @author  mfris
 * @package DI\ZendFramework\Service
 */
trait ConfigTrait
{

    /**
     * @param ContainerInterface $container
     *
     * @return array
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    private function getConfig(ContainerInterface $container)
    {
        /* @var $config array */
        $zendConfig = $container->get('config');
        $config = [];

        if (isset($zendConfig['phpdi-zf'])) {
            $config = $zendConfig['phpdi-zf'];
        }

        return $config;
    }
}
