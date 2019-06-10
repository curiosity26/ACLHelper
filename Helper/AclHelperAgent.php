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
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class AclHelperAgent
{
    use LoggerAwareTrait;

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

    /**
     * @var bool
     */
    private $allowClassAclsDefault = true;

    public function __construct(
        string $class,
        EntityManagerInterface $entityManager,
        AclHelperQueryBuilder $queryBuilder,
        bool $allowClassAcls = true,
        ?LoggerInterface $logger = null
    ) {
        $this->class                 = $class;
        $this->entityManager         = $entityManager;
        $this->queryBuilder          = $queryBuilder;
        $this->allowClassAclsDefault = $allowClassAcls;
        $this->classMetadata         = $this->entityManager->getClassMetadata($this->class);

        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * @param int $mask
     * @param UserSecurityIdentity|RoleSecurityIdentity|UserInterface|Role $identity
     * @param array $criteria
     * @param null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param string $strategy
     * @param bool|null $allowClassAcls
     * @param bool $criteriaAnd
     *
     * @return mixed
     */
    public function findBy(
        int $mask,
        $identity,
        array $criteria = [],
        $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        string $strategy = PermissionGrantingStrategy::ANY,
        ?bool $allowClassAcls = null,
        $criteriaAnd = true
    ) {
        $builder = $this->entityManager->createQueryBuilder();
        $aliases = [];

        $builder->select('e')
                ->from($this->class, 'e')
        ;

        if (!empty($criteria)) {
            $predicates = $criteriaAnd ? $builder->expr()->andX() : $builder->expr()->orX();

            foreach ($criteria as $field => $criterion) {
                $prefix = 'e';

                $col = self::aliasField($field, $builder, $aliases, $prefix.'.');

                if (($pos = stripos($col, '.')) !== false) {
                    $prefix = substr($col, 0, $pos);
                    $field  = substr($col, $pos + 1);
                }

                self::compilePredicate($predicates, $builder, $prefix, $field, $criterion);
            }

            if ($predicates->count() > 0) {
                $builder->where($predicates);
            }
        }

        if (null !== $orderBy) {
            if ($orderBy instanceof OrderBy) {
                $builder->orderBy($orderBy);
            } elseif (is_array($orderBy)) {
                $sort = key($orderBy);
                $col  = self::aliasField($sort, $builder, $aliases, 'e.');
                $builder->orderBy($col, $orderBy[$sort]);
            } else {
                $col = self::aliasField($orderBy, $builder, $aliases, 'e.');
                $builder->orderBy($col);
            }
        }

        if (null !== $limit) {
            $builder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $builder->setFirstResult($offset);
        }

        $identities = is_array($identity) ? $identity : [$identity];

        foreach ($identities as $id) {
            if ($id instanceof UserInterface) {
                foreach ($id->getRoles() as $role) {
                    $identities[] = $role instanceof Role ? $role->getRole() : $role;
                }
            }
        }

        try {
            $this->queryBuilder->createAclQueryBuilder(
                $builder,
                $this->classMetadata,
                $identities,
                $mask,
                $strategy,
                null !== $allowClassAcls ? $allowClassAcls : $this->allowClassAclsDefault
            );

            $query = $builder->getQuery();

            return $query->getResult();
        } catch (MappingException $e) {
            $this->logger->error($e->getMessage());
            $this->logger->debug($e->getTraceAsString());
        }

        return [];
    }

    /**
     * @param int $mask
     * @param $identity
     * @param array $criteria
     * @param string $strategy
     * @param bool|null $allowClassAcls
     *
     * @return mixed|null
     */
    public function findOneBy(
        int $mask,
        $identity,
        array $criteria = [],
        string $strategy = PermissionGrantingStrategy::ANY,
        ?bool $allowClassAcls = null
    ) {
        $result = $this->findBy($mask, $identity, $criteria, null, 1, 0, $strategy, $allowClassAcls);

        if (is_array($result)) {
            return false !== ($entity = reset($result)) ? $entity : null;
        }

        return $result instanceof Collection ? $result->first() : null;
    }

    /**
     * @param int $mask
     * @param $identity
     * @param string $strategy
     * @param bool|null $allowClassAcls
     *
     * @return mixed
     */
    public function findAll(
        int $mask,
        $identity,
        string $strategy = PermissionGrantingStrategy::ANY,
        ?bool $allowClassAcls = null
    ) {
        return $this->findBy($mask, $identity, [], null, null, null, $strategy, $allowClassAcls);
    }

    /**
     * @param Expr\Composite $predicate
     * @param QueryBuilder $qb
     * @param $prefix
     * @param $field
     * @param $value
     */
    public static function compilePredicate(Expr\Composite $predicate, QueryBuilder $qb, $prefix, $field, $value)
    {
        if (is_array($value)) {
            $predicate->add(self::parseComplexPredicate($qb, $prefix, $field, $value));
        } elseif ($value instanceof Expr) {
            $predicate->add($value);
        } else {
            $predicate->add("$prefix.$field = :$field");
            $qb->setParameter(":$field", $value);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param $prefix
     * @param $field
     * @param $value
     *
     * @return Expr\Comparison|Expr\Func|Expr\Orx
     */
    protected static function parseComplexPredicate(QueryBuilder $qb, $prefix, $field, array $value)
    {
        $keywords = ['=', '!=', '<', '>', '<=', '>=', 'IN', 'LIKE', 'BETWEEN'];
        $keys     = array_keys($value);
        $op       = $keys[0];

        if (count($keys) === 1 && in_array((string)$keys[0], $keywords)) {
            $value = $value[$op];

            if ('BETWEEN' == $op) {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException(
                        "The value used for a BETWEEN operation must 
                                be an array consisting of a low and high value."
                    );
                }
                $qb->setParameter(":${field}_x", $value[0]);
                $qb->setParameter(":${field}_y", $value[1]);
            } elseif ('LIKE' == $op) {
                $qb->setParameter($field, strtolower($value));
            } else {
                $qb->setParameter($field, $value);
            }

            return self::convertOpToExpr($qb->expr(), $op, $prefix, $field);
        }

        if (!empty($value)) {
            if (in_array(key($value), $keywords)) {
                $orX = $qb->expr()->orX();

                foreach ($value as $op => $v) {
                    self::compilePredicate($orX, $qb, $prefix, $field, $v);
                }

                return $orX;
            } else {
                $qb->setParameter(":$field", $value);

                return $qb->expr()->in("$prefix.$field", ":$field");
            }
        }
    }

    /**
     * @param Expr $expr
     * @param $op
     * @param $prefix
     * @param $field
     *
     * @return Expr\Comparison|Expr\Func
     */
    protected static function convertOpToExpr(Expr $expr, $op, $prefix, $field)
    {
        switch ($op) {
            case '=':
                return $expr->eq("$prefix.$field", ":$field");
            case '!=':
                return $expr->neq("$prefix.$field", ":$field");
            case '<':
                return $expr->lt("$prefix.$field", ":$field");
            case '>':
                return $expr->gt("$prefix.$field", ":$field");
            case '<=':
                return $expr->lte("$prefix.$field", ":$field");
            case '>=':
                return $expr->gte("$prefix.$field", ":$field");
            case 'IN':
                return $expr->in("$prefix.$field", ":$field");
            case 'LIKE':
                return $expr->like("LOWER($prefix.$field)", ":$field");
            case 'BETWEEN':
                return $expr->between("$prefix.$field", ":${field}_x", ":${field}_y");
            default:
                return $expr->eq("$prefix.$field", ":$field");
        }
    }

    /**
     * @param $field
     * @param QueryBuilder $builder
     * @param array $aliases
     * @param string $initialPrefix
     *
     * @return string
     */
    protected static function aliasField($field, QueryBuilder $builder, array &$aliases, $initialPrefix = 'e.')
    {
        $part   = $field;
        $prefix = $initialPrefix;

        while (($start = strpos($part, '.')) !== false) {
            $field = substr($field, 0, $start);
            if (strlen($field) === 0) {
                continue;
            }

            $col = $prefix.$field;
            if (!array_key_exists($col, $aliases)) {
                $aliases[$col] = 'j'.count($aliases);
            }

            $alias = $aliases[$col];
            $builder->join($col, $alias);
            $prefix = $alias.'.';
            $part   = str_replace($field.'.', '', $part);
        }

        return "$prefix$part";
    }
}
