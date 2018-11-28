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
    private $parentObjectIdentity;
    private $ancestor;
    private $class;
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
    public function getParentObjectIdentity()
    {
        return $this->parentObjectIdentity;
    }

    /**
     * @param mixed $parentObjectIdentity
     */
    public function setParentObjectIdentity($parentObjectIdentity)
    {
        $this->parentObjectIdentity = $parentObjectIdentity;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
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

    /**
     * @return mixed
     */
    public function getAncestor()
    {
        return $this->ancestor;
    }

    /**
     * @param mixed $ancestor
     *
     * @return ObjectIdentity
     */
    public function setAncestor(ObjectIdentity $ancestor)
    {
        $this->ancestor = $ancestor;

        return $this;
    }
}