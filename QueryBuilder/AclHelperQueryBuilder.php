<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:16 AM
 */

namespace Curiosity26\AclHelperBundle\QueryBuilder;

use Curiosity26\AclHelperBundle\Entity\AclClass;
use Curiosity26\AclHelperBundle\Entity\Entry;
use Curiosity26\AclHelperBundle\Entity\ObjectIdentity;
use Curiosity26\AclHelperBundle\Entity\SecurityIdentity;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AclHelperQueryBuilder
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry, ?RoleHierarchyInterface $roleHierarchy = null)
    {
        $this->registry      = $registry;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param ClassMetadata $classMetadata
     * @param array $identities
     * @param int $mask
     * @param string $strategy
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function createAclQueryBuilder(
        QueryBuilder $queryBuilder,
        ClassMetadata $classMetadata,
        array $identities,
        int $mask,
        string $strategy = PermissionGrantingStrategy::ANY
    ) {
        $aliases = $queryBuilder->getRootAliases();

        $idField = $classMetadata->getSingleIdentifierFieldName();

        $queryBuilder
            ->andWhere(
                $queryBuilder
                    ->expr()
                    ->in("{$aliases[0]}.{$idField}", $this->buildAclQuery($strategy)->getDQL())
            )
            ->setParameter('class_type', $classMetadata->getName())
            ->setParameter('identities', $this->buildIdentities($identities))
            ->setParameter('mask', $mask)
        ;
    }

    /**
     * @param string $strategy
     *
     * @return QueryBuilder
     * @throws \Doctrine\DBAL\DBALException
     */
    public function buildAclQuery($strategy = PermissionGrantingStrategy::ANY)
    {
        $manager = $this->registry->getEntityManagerForClass(ObjectIdentity::class);
        $q       = $manager->createQueryBuilder();

        $expr = $q->expr()
                  ->andX($q->expr()->eq('acl_c.classType', ':class_type'))
                  ->add($q->expr()->in('acl_s.identifier', ':identities'))
                  ->add($q->expr()->neq('acl_o.objectIdentifier', "'class'"))
            ;

        switch ($strategy) {
            case PermissionGrantingStrategy::ALL:
                $expr->add($q->expr()->eq('BIT_AND(acl_e.mask, :mask)', ':mask'));
                break;
            case PermissionGrantingStrategy::EQUAL:
                $expr->add($q->expr()->eq('acl_e.mask', ':mask'));
                break;
            default:
                $expr->add($q->expr()->neq('BIT_AND(acl_e.mask, :mask)', 0));
        }

        $q
            ->select('INT(acl_o.objectIdentifier)')
            ->distinct()
            ->from(ObjectIdentity::class, 'acl_o')
            ->innerJoin(AclClass::class, 'acl_c', Join::WITH, 'acl_c.id = acl_o.class')
            ->leftJoin(
                Entry::class,
                'acl_e',
                Join::WITH,
                'acl_e.objectIdentity = acl_o.id OR acl_e.objectIdentity IS NULL'
            )
            ->leftJoin(
                SecurityIdentity::class,
                'acl_s',
                Join::WITH,
                'acl_e.securityIdentity = acl_s.id'
            )
            ->where(
                $expr
            )
        ;

        return $q;
    }

    private function buildIdentities(array $identities)
    {
        $ret = [];

        foreach ($identities as $id) {
            if ($id instanceof UserSecurityIdentity) {
                $ret[] = "{$id->getClass()}-{$id->getUsername()}";
            } elseif ($id instanceof RoleSecurityIdentity) {
                $roles = [];

                foreach ($this->getRoles([$id->getRole()]) as $role) {
                    $roles[] = $role->getRole();
                }

                $ret = array_merge($ret, $roles);
            } elseif ($id instanceof UserInterface) {
                $ret[] = get_class($id)."-{$id->getUsername()}";
            } elseif ($id instanceof Role) {
                foreach ($this->getRoles($id) as $role) {
                    $ret = array_merge($ret, $this->buildIdentities([new RoleSecurityIdentity($role)]));
                }
            } elseif (is_string($id) && preg_match('/^ROLE_/', $id) != false) {
                foreach ($this->getRoles([new Role($id)]) as $role) {
                    $ret = array_merge($ret, $this->buildIdentities([new RoleSecurityIdentity($role)]));
                }
            } elseif (is_string($id)) {
                $ret[] = $id;
            }
        }

        return $ret;
    }

    protected function getRoles(array $roles)
    {
        $roleLookup = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $roleLookup[] = new Role($role);
            } else {
                $roleLookup[] = $role;
            }
        }

        if ($this->roleHierarchy instanceof RoleHierarchyInterface) {
            return $this->roleHierarchy->getReachableRoles($roleLookup);
        }

        return $roleLookup;
    }
}