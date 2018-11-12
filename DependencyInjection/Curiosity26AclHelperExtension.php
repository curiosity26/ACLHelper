<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 2:00 PM
 */

namespace Curiosity26\AclHelperBundle\DependencyInjection;

use Curiosity26\AclHelperBundle\Doctrine\DQL\CastAsInt;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Curiosity26AclHelperExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator([__DIR__.'/../Resources/config/']));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('doctrine');

        if (!empty($configs) && array_key_exists('orm', $configs[0])) {
            $config                                                                         = $configs[0];
            $default                                                                        = array_key_exists(
                'default_entity_manager',
                $config['orm']
            ) ? $config['orm']['default_entity_manager'] : 'default';
            $config['orm']['entity_managers'][$default]['dql']['string_functions']['INT'] = CastAsInt::class;
            $container->prependExtensionConfig('doctrine', $config);
        }
    }
}
