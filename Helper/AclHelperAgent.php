<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 11:31 AM
 */

namespace Curiosity26\AclHelperBundle\Helper;

use Curiosity26\ACLHelperBundle\QueryBuilder\AclHelperQueryBuilder;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class AclHelperAgent
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AclHelperQueryBuilder
     */
    private $queryBuilder;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    public function __construct(
        string $class,
        EntityManagerInterface $entityManager,
        AclHelperQueryBuilder $queryBuilder
    ) {
        $this->class         = $class;
        $this->entityManager = $entityManager;
        $this->queryBuilder  = $queryBuilder;
        $this->classMetadata = $this->entityManager->getClassMetadata($this->class);
    }

    /**
     * @param int $mask
     * @param UserSecurityIdentity|RoleSecurityIdentity|UserInterface|Role $identity
     * @param array $criteria
     * @param null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string $strategy
     *
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function findBy(
        int $mask,
        $identity,
        array $criteria = [],
        $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        string $strategy = PermissionGrantingStrategy::ANY
    ) {
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select('e')
                ->from($this->class, 'e')
        ;

        if (!empty($criteria)) {
            $predicates = [];
            foreach ($criteria as $field => $criterion) {
                $predicates[] = is_array($criterion)
                    ? $builder->expr()->in("e,$field", ":$field")
                    : $builder->expr()->eq("e.$field", ":$field");
                $builder->setParameter(":$field", $criterion);
            }

            $builder->where($predicates);
        }

        if (null !== $orderBy) {
            if ($orderBy instanceof OrderBy) {
                $builder->orderBy($orderBy);
            } elseif (is_array($orderBy)) {
                $sort = key($orderBy);
                $builder->orderBy("e.$sort", $orderBy[$sort]);
            } else {
                $builder->orderBy("e.$orderBy");
            }
        }

        if (null !== $limit) {
            $builder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $builder->setFirstResult($offset);
        }

        $identities = [$identity];

        if ($identity instanceof UserInterface) {
            foreach ($identity->getRoles() as $role) {
                $identities[] = $role;
            }
        }

        $this->queryBuilder->createAclQueryBuilder(
            $builder,
            $this->classMetadata,
            $identities,
            $mask,
            $strategy
        );

        $query = $builder->getQuery();

        return $query->getResult();
    }

    /**
     * @param int $mask
     * @param $identity
     * @param array $criteria
     * @param string $strategy
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function findOneBy(
        int $mask,
        $identity,
        array $criteria = [],
        string $strategy = PermissionGrantingStrategy::ANY
    ) {
        $result = $this->findBy($mask, $identity, $criteria, null, 1, 0, $strategy);

        if (is_array($result)) {
            return false !== ($entity = reset($result)) ? $entity : null;
        }

        return $result instanceof Collection ? $result->first() : null;
    }

    /**
     * @param int $mask
     * @param $identity
     * @param string $strategy
     *
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function findAll(int $mask, $identity, string $strategy = PermissionGrantingStrategy::ANY)
    {
        return $this->findBy($mask, $identity, [], null, null, null, $strategy);
    }
}
