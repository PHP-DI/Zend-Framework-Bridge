<?php
/**
 * @author     mfris
 */

namespace DI\ZendFramework\Controller;

use Doctrine\Common\Cache\Cache;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\Common\Cache\FlushableCache;

/**
 * Cotroller for handling of the console commands
 *
 * @author mfris
 * @package \DI\ZendFramework\Controller
 */
final class ConsoleController extends AbstractActionController
{

    /**
     * @var Cache
     */
    private $cache;

    /**
     * ConsoleController constructor.
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * flushes php di definitions cache
     */
    public function clearCacheAction()
    {
        if ($this->cache instanceof FlushableCache) {
            $this->cache->flushAll();
        }

        echo "PHP DI definitions cache was cleared." . PHP_EOL . PHP_EOL;
    }
}
