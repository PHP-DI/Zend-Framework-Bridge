<?php
/**
 * @author  mfris
 */

namespace Test\DI\ZendFramework\Service;

use DI\Container;
use DI\ZendFramework\Service\CacheFactory\CacheFactory;
use DI\ZendFramework\Service\CacheFactory\ConfigException;
use DI\ZendFramework\Service\DIContainerFactory;
use Doctrine\Common\Cache\ArrayCache;
use Test\DI\ZendFramework\Helper\Config;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;

/**
 * Class DIContainerFactoryTest
 * @author mfris
 * @package Test\DI\ZendFramework\Service
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DIContainerFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var DIContainerFactory
     */
    private $containerFactory;

    public function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setFactory('DiCache', $this->getDiCacheStub());
        $this->containerFactory = new DIContainerFactory();
    }

    public function testCreateServiceOk()
    {
        $this->serviceManager->setService('config', Config::getWorkingConfig());
        $container = $this->containerFactory->__invoke($this->serviceManager, 'config');
        self::assertInstanceOf(Container::class, $container);
    }

    public function testCreateServiceCacheConfigException()
    {
        $this->serviceManager->setService('config', Config::getMissingCacheAdapterConfig());
        $this->expectException(ServiceNotCreatedException::class);
        $this->containerFactory->__invoke($this->serviceManager, 'config');
    }

    public function testCreateServiceUnsupportedCacheAdapterConfigException()
    {
        $this->serviceManager->setService('config', Config::getUnsupportedCacheAdapterConfig());
        $this->expectException(ServiceNotCreatedException::class);
        $this->containerFactory->__invoke($this->serviceManager, 'config');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheFactory
     */
    private function getDiCacheStub()
    {
        $stub = $this->createMock(CacheFactory::class);

        $stub->expects(self::any())
            ->method('__invoke')
            ->will(self::returnCallback([$this, 'cacheCallback']));

        return $stub;
    }

    /**
     * @return ArrayCache
     * @throws ConfigException
     * @throws RuntimeException
     */
    public function cacheCallback()
    {
        $args = func_get_args();
        /**
         * @var $serviceManager ServiceManager
         */
        $serviceManager = $args[0];
        /* @var $config array */
        $config = $serviceManager->get('config');

        if (isset($config['phpdi-zf'], $config['phpdi-zf']['cache'])) {
            if (!isset($config['phpdi-zf']['cache']['adapter'])) {
                throw ConfigException::newCacheAdapterMissingException();
            } elseif ($config['phpdi-zf']['cache']['adapter'] === 'unsupported') {
                throw ConfigException::newUnsupportedCacheAdapterException('unsupported');
            }

            return new ArrayCache();
        }

        throw new RuntimeException('Invalid state.');
    }
}
