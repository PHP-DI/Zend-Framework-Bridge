<?php

/**
 * @author Martin Fris <rasta@lj.sk>
 */

namespace DI\ZendFramework3\Service\CacheFactory;

use Doctrine\Common\Cache\Cache;

/**
 * Interface CacheFactoryInterface
 * @package DI\ZendFramework3\Service\CacheFactory
 */
interface CacheFactoryInterface
{

    /**
     * @param array $config
     * @return Cache
     */
    public function newInstance(array $config);
}
