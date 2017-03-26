<?php
/**
 * @author     mfris
 * @copyright  Pixel federation
 * @license    Internal use only
 */

namespace DI\ZendFramework\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * simple trait for getting php di config data from zf config array
 * @author  mfris
 * @package DI\ZendFramework\Service
 */
trait ConfigTrait
{

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return array
     */
    private function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $config array */
        $config = $serviceLocator->get('config');
        if (isset($config['phpdi-zf'])) {
            $config = $config['phpdi-zf'];
        } else {
            $config = [];
        }

        return $config;
    }
}
