<?php
/**
 * PHP-DI
 *
 * @link http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\ZendFramework\Service;

use Acclimate\Container\Exception\ContainerException;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use DI\Container;

/**
 * Abstract factory responsible of trying to build services from the PHP DI container
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Martin Fris
 */
final class PHPDIAbstractFactory implements AbstractFactoryInterface
{
    const CONTAINER_NAME = Container::class;

    /**
     * lazy loaded instance of the PHP DI container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     * @throws \Zend\ServiceManager\Exception\InvalidServiceException
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws ContainerException if any other error occurs
     * @throws InvalidServiceException
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if ($requestedName === self::CONTAINER_NAME) {
            return true;
        }

        return $this->getContainer($container)->has($requestedName);
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return ContainerInterface
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws InvalidServiceException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->getContainer($container)->get($requestedName);
    }

    /**
     * @param  ContainerInterface $container
     *
     * @return ContainerInterface
     *
     * @throws \Zend\ServiceManager\Exception\InvalidServiceException
     * @throws ContainerException
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    private function getContainer(ContainerInterface $container)
    {
        if ($this->container !== null) {
            return $this->container;
        }

        $this->container = $container->get(static::CONTAINER_NAME);

        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }

        throw new InvalidServiceException(sprintf(
            'Container "%s" is not a valid DI\\ContainerInterface, "%s" found',
            Container::class,
            is_object($this->container) ? get_class($this->container) : gettype($this->container)
        ));
    }
}
