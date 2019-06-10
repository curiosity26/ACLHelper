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
use Doctrine\ORM\EntityManagerInterface;
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
     * @param bool $allowClassAcls
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function createAclQueryBuilder(
        QueryBuilder $queryBuilder,
        ClassMetadata $classMetadata,
        array $identities,
        int $mask,
        string $strategy = PermissionGrantingStrategy::ANY,
        $allowClassAcls = true
    ) {
        $aliases = $queryBuilder->getRootAliases();
        $idField = $classMetadata->getSingleIdentifierFieldName();
        $where   = $queryBuilder->expr()->orX(
            $queryBuilder
                ->expr()
                ->in("{$aliases[0]}.{$idField}", $this->buildAclQuery($strategy, 0)->getDQL())
        )
        ;

        if ($allowClassAcls) {
            $where->add(
                $queryBuilder
                    ->expr()
                    ->lt(0, '('.$this->buildHasClassAclQuery($strategy, 1)->getDQL().')')
            );
        }


        $queryBuilder
            ->andWhere($where)
            ->setParameter('class_type', $classMetadata->getName())
            ->setParameter('identities', $this->buildIdentities($identities))
            ->setParameter('mask', $mask)
        ;
    }

    /**
     * @param string $strategy
     * @param string $aliasSuffix
     *
     * @return QueryBuilder
     */
    public function buildAclQuery($strategy = PermissionGrantingStrategy::ANY, $aliasSuffix = '')
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->registry->getManagerForClass(ObjectIdentity::class);
        $q       = $manager->createQueryBuilder();

        $expr = $q->expr()
                  ->andX($q->expr()->eq("acl_c$aliasSuffix.classType", ':class_type'))
                  ->add($q->expr()->in("acl_s$aliasSuffix.identifier", ':identities'))
                  ->add($q->expr()->neq("acl_o$aliasSuffix.objectIdentifier", "'class'"))
        ;

        switch ($strategy) {
            case PermissionGrantingStrategy::ALL:
                $expr->add($q->expr()->eq("BIT_AND(acl_e$aliasSuffix.mask, :mask)", ':mask'));
                break;
            case PermissionGrantingStrategy::EQUAL:
                $expr->add($q->expr()->eq("acl_e$aliasSuffix.mask", ':mask'));
                break;
            default:
                $expr->add($q->expr()->neq("BIT_AND(acl_e$aliasSuffix.mask, :mask)", 0));
        }

        $q
            ->select("INT(acl_o$aliasSuffix.objectIdentifier)")
            ->distinct()
            ->from(ObjectIdentity::class, "acl_o$aliasSuffix")
            ->innerJoin(
                AclClass::class,
                "acl_c$aliasSuffix",
                Join::WITH,
                "acl_c$aliasSuffix.id = acl_o$aliasSuffix.class"
            )
            ->leftJoin(
                Entry::class,
                "acl_e$aliasSuffix",
                Join::WITH,
                "acl_e$aliasSuffix.objectIdentity = acl_o$aliasSuffix.id OR acl_e$aliasSuffix.objectIdentity IS NULL"
            )
            ->leftJoin(
                SecurityIdentity::class,
                "acl_s$aliasSuffix",
                Join::WITH,
                "acl_e$aliasSuffix.securityIdentity = acl_s$aliasSuffix.id"
            )
            ->where(
                $expr
            )
        ;

        return $q;
    }

    private function buildHasClassAclQuery($strategy = PermissionGrantingStrategy::ALL, $aliasSuffix = '')
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->registry->getManagerForClass(ObjectIdentity::class);
        $q       = $manager->createQueryBuilder();

        $expr = $q->expr()
                  ->andX($q->expr()->eq("acl_c$aliasSuffix.classType", ':class_type'))
                  ->add($q->expr()->in("acl_s$aliasSuffix.identifier", ':identities'))
                  ->add($q->expr()->eq("acl_o$aliasSuffix.objectIdentifier", "'class'"))
        ;

        switch ($strategy) {
            case PermissionGrantingStrategy::ALL:
                $expr->add($q->expr()->eq("BIT_AND(acl_e$aliasSuffix.mask, :mask)", ':mask'));
                break;
            case PermissionGrantingStrategy::EQUAL:
                $expr->add($q->expr()->eq("acl_e$aliasSuffix.mask", ':mask'));
                break;
            default:
                $expr->add($q->expr()->neq("BIT_AND(acl_e$aliasSuffix.mask, :mask)", 0));
        }

        $q
            ->select("Count(acl_o$aliasSuffix.objectIdentifier)")
            ->from(ObjectIdentity::class, "acl_o$aliasSuffix")
            ->innerJoin(
                AclClass::class,
                "acl_c$aliasSuffix",
                Join::WITH,
                "acl_c$aliasSuffix.id = acl_o$aliasSuffix.class"
            )
            ->leftJoin(
                Entry::class,
                "acl_e$aliasSuffix",
                Join::WITH,
                "acl_e$aliasSuffix.objectIdentity = acl_o$aliasSuffix.id OR acl_e$aliasSuffix.objectIdentity IS NULL"
            )
            ->leftJoin(
                SecurityIdentity::class,
                "acl_s$aliasSuffix",
                Join::WITH,
                "acl_e$aliasSuffix.securityIdentity = acl_s$aliasSuffix.id"
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