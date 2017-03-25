<?php
/**
 * PHP-DI
 *
 * @link http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\ZendFramework3\Service;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\AbstractController;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\Factory\InvokableFactory;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\Mvc\Controller\ControllerManager as ZendControllerManager;
use Zend\Stdlib\DispatchableInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Manager for loading controllers
 *
 * Does not define any controllers by default, but does add a validator.
 */
final class ControllerManager extends ZendControllerManager
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ContainerInterface
     */
    private $zfContainer;

    /**
     * @param ContainerInterface $container
     * @param ContainerInterface $zfContainer
     * @param array $v3config = []
     */
    public function __construct(ContainerInterface $container, ContainerInterface $zfContainer, array $v3config = [])
    {
        $this->container = $container;
        $this->zfContainer = $zfContainer;

        parent::__construct($zfContainer, $v3config);
    }

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array  $options
     *
     * @return object
     *
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\ServiceNotCreatedException
     * @throws RuntimeException
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager
     * @throws ContainerException if any other error occurs
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($name, array $options = null)
    {
        $controller = null;

        if (parent::has($name)) {
            $controller = parent::get($name, $options);
        } elseif ($this->container->has($name)) {
            if (! $this->has($name)) {
                if (! $this->autoAddInvokableClass || ! class_exists($name)) {
                    throw new Exception\ServiceNotFoundException(sprintf(
                        'A plugin by the name "%s" was not found in the plugin manager %s',
                        $name,
                        get_class($this)
                    ));
                }

                $this->setFactory($name, InvokableFactory::class);
            }

            $controller = $this->container->get($name);
            $this->initialize($controller);
            $this->validate($controller);
        }

        if (!$controller) {
            throw new Exception\ServiceNotFoundException("Unable to locate service '{$name}'");
        } elseif (!($controller instanceof DispatchableInterface)) {
            throw new RuntimeException("Service '{$name}' is not a Controller.");
        }

        return $controller;
    }

    /**
     * Override: do not use peering service managers
     *
     * @param  string|array $name
     * @return bool
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function has($name)
    {
        if (is_string($name) && $this->container->has($name)) {
            return true;
        } elseif (parent::has($name)) {
            return true;
        }

        return false;
    }

    /**
     * injects Zend core services into the given controller
     *
     * @param AbstractController $controller
     */
    private function initialize(AbstractController $controller)
    {
        foreach ($this->initializers as $initializer) {
            $initializer($this->zfContainer, $controller);
        }
    }
}
