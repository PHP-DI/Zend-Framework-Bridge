<?php
/**
 * @author     mfris
 */

namespace DI\ZendFramework3\Service\CacheFactory;

use DI\ZendFramework3\Service\ConfigTrait;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Factory for php di definitions cache
 *
 * @author  mfris
 * @package \DI\ZendFramework3\Service\Cache
 */
class CacheFactory implements FactoryInterface
{
    use ConfigTrait;

    /**
     * @var array
     */
    private $adapterClasses = [
        'filesystem' => FileSystemFactory::class,
        'redis' => RedisFactory::class,
        'memcached' => MemcachedFactory::class,
    ];

    /**
     * @var CacheFactoryInterface[]
     */
    private $adapters = [];

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return Cache|CacheProvider|null
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     * @throws ConfigException
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container);

        if (!isset($config['cache'])) {
            return null;
        }

        $config = $config['cache'];
        /* @var $cache CacheProvider|Cache */
        $cache = null;
        $cacheFactory = $this->getCacheFactory($config);
        $cache = $cacheFactory->newInstance($config);

        if (isset($config['namespace'])) {
            $cache->setNamespace(trim($config['namespace']));
        }

        return $cache;
    }

    /**
     * @param array $config
     * @return CacheFactoryInterface
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getCacheFactory(array $config)
    {
        if (!isset($config['adapter'])) {
            throw ConfigException::newCacheAdapterMissingException();
        }

        $adapter = $config['adapter'];

        if (!isset($this->adapterClasses[$adapter])) {
            throw ConfigException::newUnsupportedCacheAdapterException($adapter);
        }

        if (!isset($this->adapters[$adapter])) {
            $this->adapters[$adapter] = new $this->adapterClasses[$adapter]();
        }

        /* @var CacheFactoryInterface $cacheFactory */
        return $this->adapters[$adapter];
    }
}
