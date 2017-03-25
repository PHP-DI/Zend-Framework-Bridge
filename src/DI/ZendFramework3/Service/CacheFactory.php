<?php
/**
 * @author     mfris
 */

namespace DI\ZendFramework3\Service;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Redis;

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
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return Cache|null
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

        if (!isset($config['adapter'])) {
            throw ConfigException::newCacheAdapterMissingException();
        }

        $adapter = $config['adapter'];

        /* @var $cache CacheProvider */
        $cache = null;

        switch ($adapter) {
            case 'filesystem':
                $cache = $this->getFilesystemCache($config);
                break;

            case 'redis':
                $cache = $this->getRedisCache($config);
                break;

            case 'memcached':
                $cache = $this->getMemcachedCache($config);
                break;

            default:
                throw ConfigException::newUnsupportedCacheAdapterException($adapter);
        }

        if (isset($config['namespace'])) {
            $cache->setNamespace(trim($config['namespace']));
        }

        return $cache;
    }

    /**
     * creates file system cache
     *
     * @param array $config
     *
     * @return FilesystemCache
     */
    private function getFilesystemCache(array $config)
    {
        $directory = getcwd() . '/data/php-di/cache';

        if (isset($config['directory'])) {
            $directory = $config['directory'];
        }

        return new FilesystemCache($directory);
    }

    /**
     * creates redis cache
     *
     * @param array $config
     * @return RedisCache
     */
    private function getRedisCache(array $config)
    {
        $host = 'localhost';
        $port = 6379;
        $database = 0;

        if (isset($config['host'])) {
            $host = $config['host'];
        }

        if (isset($config['port'])) {
            $port = $config['port'];
        }

        if (isset($config['database'])) {
            $database = (int) $config['database'];
        }

        $redis = new Redis();
        $redis->connect($host, $port);

        if ($database) {
            $redis->select($database);
        }

        $cache = new RedisCache();
        $cache->setRedis($redis);

        return $cache;
    }

    /**
     * creates memcached cache
     *
     * @param array $config
     * @return MemcachedCache
     */
    private function getMemcachedCache(array $config)
    {
        $host = 'localhost';
        $port = 11211;

        if (isset($config['host'])) {
            $host = $config['host'];
        }

        if (isset($config['port'])) {
            $port = $config['port'];
        }

        $cache = new MemcachedCache();
        $memcache = new \Memcached;
        $memcache->addServer($host, $port);
        $cache->setMemcached($memcache);

        return $cache;
    }
}
