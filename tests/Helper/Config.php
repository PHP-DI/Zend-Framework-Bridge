<?php
/**
 * @author  mfris
 */

namespace Test\DI\ZendFramework\Helper;

/**
 * Class Config
 * @author mfris
 */
class Config
{

    /**
     * @return array
     */
    public static function getWorkingConfig()
    {
        return [
            'phpdi-zf' => [
                'definitionsFile' => realpath(__DIR__ . '/php-di.config.php'),
                'useAnntotations' => true,
                'cache' => [
                    'namespace' => 'quickstart',
                    'adapter' => 'filesystem',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getMissingCacheAdapterConfig()
    {
        return [
            'phpdi-zf' => [
                'definitionsFile' => realpath(__DIR__ . '/php-di.config.php'),
                'useAnntotations' => true,
                'cache' => [
                    'namespace' => 'quickstart',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getUnsupportedCacheAdapterConfig()
    {
        return [
            'phpdi-zf' => [
                'definitionsFile' => realpath(__DIR__ . '/php-di.config.php'),
                'useAnntotations' => true,
                'cache' => [
                    'namespace' => 'quickstart',
                    'adapter' => 'unsupported',
                ],
            ],
        ];
    }
}
