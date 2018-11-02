<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:02 AM
 */

namespace Curiosity26\AclHelperBundle\Entity;

class ObjectIdentity
{
    private $id;
    private $parentObjectIdentityId;
    private $classId;
    private $objectIdentifier;
    private $entriesInheriting;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getParentObjectIdentityId()
    {
        return $this->parentObjectIdentityId;
    }

    /**
     * @param mixed $parentObjectIdentityId
     */
    public function setParentObjectIdentityId($parentObjectIdentityId)
    {
        $this->parentObjectIdentityId = $parentObjectIdentityId;
    }

    /**
     * @return mixed
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * @param mixed $classId
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
    }

    /**
     * @return mixed
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @param mixed $objectIdentifier
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;
    }

    /**
     * @return mixed
     */
    public function getEntriesInheriting()
    {
        return $this->entriesInheriting;
    }

    /**
     * @param mixed $entriesInheriting
     */
    public function setEntriesInheriting($entriesInheriting)
    {
        $this->entriesInheriting = $entriesInheriting;
    }
}