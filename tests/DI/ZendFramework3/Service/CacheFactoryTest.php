<?php
/**
 * @author  mfris
 */

namespace Test\DI\ZendFramework3\Service;

use DI\ZendFramework3\Service\CacheFactory;
use DI\ZendFramework3\Service\ConfigException;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Zend\ServiceManager\ServiceManager;

/**
 * Class CacheFactoryTest
 * @author mfris
 * @package Test\DI\ZendFramework3\Service
 */
class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var CacheFactory
     */
    private $cacheFactory;

    public function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $this->cacheFactory = new CacheFactory();
    }

    public function testCreateRedisCache()
    {
        $this->serviceManager->setService('config', [
            'phpdi-zf3' => [
                'cache' => [
                    'namespace' => 'quickstartxx',
                    // redis adapter
                    'adapter' => 'redis',
                    'host' => 'localhost',
                    'port' => 6379,
                ],
            ],
        ]);

        $redisCache = $this->cacheFactory->__invoke($this->serviceManager, 'redis');
        self::assertInstanceOf(RedisCache::class, $redisCache);
    }

    public function testFileSystemCache()
    {
        $this->serviceManager->setService('config', [
            'phpdi-zf3' => [
                'cache' => [
                    'namespace' => 'quickstartxx',
                    'adapter' => 'filesystem',
                ],
            ],
        ]);

        $fileSystemCache = $this->cacheFactory->__invoke($this->serviceManager, 'fs');
        self::assertInstanceOf(FilesystemCache::class, $fileSystemCache);
    }

    public function testInvalidCacheConfigWithoutAdapter()
    {
        $this->serviceManager->setService('config', [
            'phpdi-zf3' => [
                'cache' => [
                ],
            ],
        ]);

        $this->setExpectedException(ConfigException::class, 'Cache configuration - adapter missing.');
        $this->cacheFactory->__invoke($this->serviceManager, 'config');
    }

    public function testInvalidCacheConfigNonExistentAdapter()
    {
        $this->serviceManager->setService('config', [
            'phpdi-zf3' => [
                'cache' => [
                    'adapter' => 'non-existent',
                ],
            ],
        ]);

        $this->setExpectedExceptionRegExp(ConfigException::class, '/^Unsupported cache adapter - /');
        $this->cacheFactory->__invoke($this->serviceManager, 'config');
    }
}
