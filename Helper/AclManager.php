<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/5/18
 * Time: 12:30 PM
 */

namespace Curiosity26\AclHelperBundle\Helper;

use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Class AclManager
 *
 * @package Curiosity26\AclHelperBundle\Helper
 */
class AclManager
{
    /**
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var MutableAclInterface
     */
    private $acl;

    /**
     * AclManager constructor.
     *
     * @param MutableAclProviderInterface $provider
     */
    public function __construct(MutableAclProviderInterface $provider)
    {
        $this->aclProvider = $provider;
    }

    /**
     * @param $object
     *
     * @return $this
     */
    public function aclFor($object)
    {
        if (!$object instanceof ObjectIdentityInterface) {
            $object = ObjectIdentity::fromDomainObject($object);
        }

        try {
            $this->acl = $this->aclProvider->findAcl($object);
        } catch (AclNotFoundException $e) {
            $this->acl = $this->aclProvider->createAcl($object);
        }

        return $this;
    }

    /**
     * @param SecurityIdentityInterface $identity
     * @param int $mask
     * @param int $index
     * @param bool $granting
     * @param null|string $strategy
     *
     * @return $this
     */
    public function insertClassAce(
        SecurityIdentityInterface $identity,
        int $mask,
        $index = 0,
        bool $granting = true,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->insertClassAce($identity, $mask, $index, $granting, $strategy);

        return $this;
    }

    /**
     * @param SecurityIdentityInterface $identity
     * @param int $mask
     * @param int $index
     * @param bool $granting
     * @param null|string $strategy
     *
     * @return $this
     */
    public function insertObjectAce(
        SecurityIdentityInterface $identity,
        int $mask,
        $index = 0,
        bool $granting = true,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->insertObjectAce($identity, $mask, $index, $granting, $strategy);

        return $this;
    }

    /**
     * @param string $field
     * @param SecurityIdentityInterface $identity
     * @param int $mask
     * @param int $index
     * @param bool $granting
     * @param null|string $strategy
     *
     * @return $this
     */
    public function insertClassFieldAce(
        string $field,
        SecurityIdentityInterface $identity,
        int $mask,
        $index = 0,
        bool $granting = true,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->insertClassFieldAce($field, $identity, $mask, $index, $granting, $strategy);

        return $this;
    }

    /**
     * @param string $field
     * @param SecurityIdentityInterface $identity
     * @param int $mask
     * @param int $index
     * @param bool $granting
     * @param null|string $strategy
     *
     * @return $this
     */
    public function insertObjectFieldAce(
        string $field,
        SecurityIdentityInterface $identity,
        int $mask,
        $index = 0,
        bool $granting = true,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->insertObjectFieldAce($field, $identity, $mask, $index, $granting, $strategy);

        return $this;
    }

    /**
     * @param $index
     * @param int $mask
     * @param null|string $strategy
     *
     * @return $this
     */
    public function updateClassAce(
        $index,
        int $mask,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->updateClassAce($index, $mask, $strategy);

        return $this;
    }

    /**
     * @param $index
     * @param int $mask
     * @param null|string $strategy
     *
     * @return $this
     */
    public function updateObjectAce(
        $index,
        int $mask,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->updateObjectAce($index, $mask, $strategy);

        return $this;
    }

    /**
     * @param $index
     * @param string $field
     * @param int $mask
     * @param null|string $strategy
     *
     * @return $this
     */
    public function updateClassFieldAce(
        $index,
        string $field,
        int $mask,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->updateClassFieldAce($index, $field, $mask, $strategy);

        return $this;
    }

    /**
     * @param $index
     * @param string $field
     * @param int $mask
     * @param null|string $strategy
     *
     * @return $this
     */
    public function updateObjectFieldAce(
        $index,
        string $field,
        int $mask,
        ?string $strategy = null
    ) {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->updateObjectFieldAce($index, $field, $mask, $strategy);

        return $this;
    }

    /**
     * @param $index
     *
     * @return $this
     */
    public function deleteClassAce($index)
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->deleteClassAce($index);

        return $this;
    }

    /**
     * @param $index
     *
     * @return $this
     */
    public function deleteObjectAce($index)
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->deleteObjectAce($index);

        return $this;
    }

    /**
     * @param $index
     * @param $field
     *
     * @return $this
     */
    public function deleteClassFieldAce($index, $field)
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->deleteClassFieldAce($index, $field);

        return $this;
    }

    /**
     * @param $index
     * @param $field
     *
     * @return $this
     */
    public function deleteObjectFieldAce($index, $field)
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->acl->deleteObjectFieldAce($index, $field);

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $this->aclProvider->updateAcl($this->acl);

        return $this;
    }

    /**
     * @return array|Entry[]
     */
    public function getClassAces(): array
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        return $this->acl->getClassAces();
    }

    /**
     * @param string $field
     *
     * @return array|Entry[]
     */
    public function getClassFieldAces(\string $field): array
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        return $this->acl->getClassFieldAces($field);
    }

    /**
     * @return array|Entry[]
     */
    public function getObjectAces(): array
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        return $this->acl->getObjectAces();
    }

    /**
     * @param string $field
     *
     * @return array|Entry[]
     */
    public function getObjectFieldAces(\string $field): array
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        return $this->acl->getObjectFieldAces($field);
    }

    /**
     * @return ObjectIdentityInterface
     */
    public function getObjectIdentity(): ObjectIdentityInterface
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        return $this->acl->getObjectIdentity();
    }

    /**
     * @return null|MutableAclInterface
     */
    public function getAcl(): ?MutableAclInterface
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        return $this->acl;
    }

    /**
     * Deletes the ACL
     *
     * @return $this
     */
    public function delete()
    {
        if (null === $this->acl) {
            throw new \RuntimeException("Find or create an ACL using aclFor() first.");
        }

        $identity = $this->getObjectIdentity();

        $this->aclProvider->deleteAcl($identity);

        $this->acl = null;

        return $this;
    }
}
