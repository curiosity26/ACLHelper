<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:16 AM
 */

namespace AclHelperBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AclHelperRepository extends EntityRepository
{
    private $roleHierarchy;

    /**
     * @param RoleHierarchyInterface $roleHierarchy
     * @return $this
     */
    public function setRoleHierarchy(RoleHierarchyInterface $roleHierarchy) {
        $this->roleHierarchy = $roleHierarchy;

        return $this;
    }

    public function createAclQueryBuilder($alias, array $identities, $mask, $idField = 'id') {
        $q = $this->createQueryBuilder($alias);

        $q
            ->where(
                $q
                    ->expr()
                    ->in("$alias.$idField", $this->buildAclQuery()->getDQL())
            )
            ->setParameter('class_type', $this->getClassName())
            ->setParameter('identities', $this->buildIdentities($identities))
            ->setParameter('mask', $mask);

        return $q;
    }

    /**
     * @param string $class_type
     * @param array $identities
     * @param int $mask
     */
    protected function buildAclQuery() {
        $q = $this->_em->createQueryBuilder();
        $q
            ->select('acl_o.object_identifier')
            ->distinct()
            ->from('AclHelperBundle:ObjectIdentity', 'acl_o')
            ->innerJoin('AclHelperBundle:AclClass', 'acl_c', Join::INNER_JOIN, 'acl_c.id = acl_o.class_id')
            ->leftJoin('AclHelperBundle:Entry', 'acl_e', Join::LEFT_JOIN, 'acl_e.object_identity_id = acl_o.id OR (acl_e.object_identity_id IS NULL)')
            ->leftJoin('AclHelperBundle:SecurityIdentity', 'acl_s', Join::LEFT_JOIN, 'acl_e.security_identity_id = acl_s.id')
            ->where(
                $q->expr()
                    ->andX($q->expr()->eq('acl_c.class_type', ':class_type'))
                    ->add($q->expr()->in('acl_s.identifier', ':identities'))
                    ->add($q->expr()->gte('acl_e.mask', ':mask'))

            );

        return $q;
    }

    private function buildIdentities(array $identities) {
        $ret = array();

        foreach ($identities as $id) {
            if ($id instanceof UserSecurityIdentity) {
                $ret[] = "{$id->getClass()}-{$id->getUsername()}";
            } elseif ($id instanceof RoleSecurityIdentity) {
                $ret = array_merge($ret, $this->getRoles([$id->getRole()]));
            } elseif ($id instanceof UserInterface) {
                $ret[] = get_class($id)."-{$id->getUsername()}";
            } elseif ($id instanceof RoleInterface) {
                $ret = array_merge($ret, $this->getRoles([$id]));
            } elseif (is_string($id) && preg_match('/^ROLE_', $id) !== false) {
                $id = new Role($id);
                $ret = array_merge($ret, $this->getRoles([$id]));
            } elseif (is_string($id)) {
                $ret[] = $id;
            }
        }

        return $ret;
    }

    protected function getRoles($roles) {
        if ($this->roleHierarchy instanceof RoleHierarchyInterface) {
            return $this->roleHierarchy->getReachableRoles((array)$roles);
        }

        return (array)$roles;
    }
}