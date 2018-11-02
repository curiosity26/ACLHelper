<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 11:22 AM
 */

namespace Curiosity26\AclHelperBundle\Helper;

use Curiosity26\AclHelperBundle\QueryBuilder\AclHelperQueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AclHelper
{
    /**
     * @var AclHelperQueryBuilder
     */
    private $queryBuilder;

    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry, AclHelperQueryBuilder $queryBuilder)
    {
        $this->registry     = $registry;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param string $className
     *
     * @return AclHelperAgent
     */
    public function createAgent(string $className): AclHelperAgent
    {
        $manager = $this->registry->getManagerForClass($className);

        return new AclHelperAgent($className, $manager, $this->queryBuilder);
    }
}
