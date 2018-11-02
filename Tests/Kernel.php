<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 1:49 PM
 */
namespace Curiosity26\AclHelperBundle\Tests;

use Curiosity26\AclHelperBundle\Curiosity26AclHelperBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Symfony\Bundle\AclBundle\AclBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new NelmioAliceBundle(),
            new FidryAliceDataFixturesBundle(),
            new SecurityBundle(),
            new AclBundle(),
            new Curiosity26AclHelperBundle(),
        ];
    }

    /**
     * @param LoaderInterface $loader
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/Resources/config/config.yml');
        $loader->load(__DIR__ . '/Resources/config/security.yml');
    }
}
