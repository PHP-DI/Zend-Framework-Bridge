<?php
/**
 * PHP-DI
 *
 * @link http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\ZendFramework3\Service;

use Acclimate\Container\ContainerAcclimator;
use Acclimate\Container\Exception\InvalidAdapterException;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Abstract factory responsible of trying to build services from the PHP DI container
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Martin Fris
 */
final class DIContainerFactory implements FactoryInterface
{

    use ConfigTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return Container
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     * @throws InvalidAdapterException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($this->container !== null) {
            return $this->container;
        }

        $builder = new ContainerBuilder();
        $config = $this->getConfig($container);
        $configFile = $this->getDefinitionsFilePath($config);
        $builder->addDefinitions($configFile);

        $useAnnotations = $this->shouldUseAnnotations($config);
        $builder->useAnnotations($useAnnotations);

        $acclimator = new ContainerAcclimator();
        $zfContainer = $acclimator->acclimate($container);
        $builder->wrapContainer($zfContainer);

        /**
         * @var $cache Cache
         */
        $cache = $this->getCache($container, $config);

        if ($cache) {
            $builder->setDefinitionCache($cache);
        }

        $this->container = $builder->build();

        return $this->container;
    }

    /**
     * return definitions file path
     *
     * @param array $config
     *
     * @return string
     * @throws RuntimeException
     */
    private function getDefinitionsFilePath(array $config)
    {
        $filePath = __DIR__ . '/../../../../../../../config/php-di.config.php';

        if (isset($config['definitionsFile'])) {
            $filePath = $config['definitionsFile'];
        }

        if (!file_exists($filePath)) {
            throw new RuntimeException('DI definitions file missing.');
        }

        return $filePath;
    }

    /**
     * returns true, if annotations should be used
     *
     * @param array $config
     * @return bool
     */
    private function shouldUseAnnotations(array $config)
    {
        if (!isset($config['useAnntotations']) || $config['useAnntotations'] !== true) {
            return false;
        }

        return true;
    }

    /**
     * returns cache adapter, if configured properly
     *
     * @param ContainerInterface $container
     * @param array $config
     * @return Cache|null
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    private function getCache(ContainerInterface $container, array $config)
    {
        if (!isset($config['cache'])) {
            return null;
        }

        /**
         * @var $cache Cache
         */
        return $container->get('DiCache');
    }
}
