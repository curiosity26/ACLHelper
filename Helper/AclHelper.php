<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 11:22 AM
 */

namespace Curiosity26\AclHelperBundle\Helper;

use Curiosity26\AclHelperBundle\QueryBuilder\AclHelperQueryBuilder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;

class AclHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var AclHelperQueryBuilder
     */
    private $queryBuilder;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var bool
     */
    private $allowClassAclsDefault = true;

    public function __construct(
        RegistryInterface $registry,
        AclHelperQueryBuilder $queryBuilder,
        MutableAclProviderInterface $provider,
        bool $allowClassAclsDefault = true
    ) {
        $this->registry              = $registry;
        $this->queryBuilder          = $queryBuilder;
        $this->aclProvider           = $provider;
        $this->allowClassAclsDefault = $allowClassAclsDefault;
    }

    /**
     * @param string $className
     *
     * @return AclHelperAgent
     */
    public function createAgent(string $className): AclHelperAgent
    {
        $manager = $this->registry->getManagerForClass($className);

        return new AclHelperAgent(
            $className,
            $manager,
            $this->queryBuilder,
            $this->allowClassAclsDefault,
            $this->logger
        );
    }

    /**
     * @return AclManager
     */
    public function createAclManager()
    {
        return new AclManager($this->aclProvider);
    }

    /**
     * @return MutableAclProviderInterface
     */
    public function getAclProvider(): MutableAclProviderInterface
    {
        return $this->aclProvider;
    }
}
