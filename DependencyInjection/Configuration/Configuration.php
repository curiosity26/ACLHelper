<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 6/10/19
 * Time: 4:58 PM
 */

namespace Curiosity26\AclHelperBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $tree->root('curiosity26_acl_helper')
            ->children()
                ->scalarNode('allowClassAclsDefault')
                    ->cannotBeEmpty()
                    ->defaultValue(true)
                    ->validate()
                        ->ifNotInArray([true, false])
                        ->thenInvalid('allowClassAclsDefault must be a boolean value')
                    ->end()
                ->end()
            ->end();

        return $tree;
    }

}
