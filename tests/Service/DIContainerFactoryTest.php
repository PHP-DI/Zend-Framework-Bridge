<?php
/**
 * @author  mfris
 */

namespace Test\DI\ZendFramework\Service;

use DI\Container;
use DI\ZendFramework\Service\CacheFactory;
use DI\ZendFramework\Service\ConfigException;
use DI\ZendFramework\Service\DIContainerFactory;
use Doctrine\Common\Cache\ArrayCache;
use Test\DI\ZendFramework\Helper\Config;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceManager;

/**
 * Class DIContainerFactoryTest
 * @author mfris
 * @package Test\DI\ZendFramework\Service
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
        $container = $this->containerFactory->createService($this->serviceManager);
        self::assertInstanceOf(Container::class, $container);
    }

    public function testCreateServiceCacheConfigException()
    {
        $this->serviceManager->setService('config', Config::getMissingCacheAdapterConfig());
        $this->setExpectedException(ServiceNotCreatedException::class);
        $this->containerFactory->createService($this->serviceManager);
    }

    public function testCreateServiceUnsupportedCacheAdapterConfigException()
    {
        $this->serviceManager->setService('config', Config::getUnsupportedCacheAdapterConfig());
        $this->setExpectedException(ServiceNotCreatedException::class);
        $this->containerFactory->createService($this->serviceManager);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheFactory
     */
    private function getDiCacheStub()
    {
        $stub = $this->getMock('\\DI\\ZendFramework\\Service\\CacheFactory', ['createService']);

        $stub->expects(self::any())
            ->method('createService')
            ->will(self::returnCallback([$this, 'cacheCallback']));

        return $stub;
    }

    /**
     * @return ArrayCache
     * @throws ConfigException
     * @throws \Exception
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

        if (isset($config['phpdi-zf']) && isset($config['phpdi-zf']['cache'])) {
            if (!isset($config['phpdi-zf']['cache']['adapter'])) {
                throw ConfigException::newCacheAdapterMissingException();
            } elseif ($config['phpdi-zf']['cache']['adapter'] === 'unsupported') {
                throw ConfigException::newUnsupportedCacheAdapterException('unsupported');
            }

            return new ArrayCache();
        }

        throw new \Exception('Invalid state.');
    }
}
