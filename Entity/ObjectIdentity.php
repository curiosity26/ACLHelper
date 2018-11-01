<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:02 AM
 */

namespace AclHelperBundle\Entity;


class ObjectIdentity
{
    private $id;
    private $parent_object_identity_id;
    private $class_id;
    private $object_identifier;
    private $entries_inheriting;

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
        return $this->parent_object_identity_id;
    }

    /**
     * @param mixed $parent_object_identity_id
     */
    public function setParentObjectIdentityId($parent_object_identity_id)
    {
        $this->parent_object_identity_id = $parent_object_identity_id;
    }

    /**
     * @return mixed
     */
    public function getClassId()
    {
        return $this->class_id;
    }

    /**
     * @param mixed $class_id
     */
    public function setClassId($class_id)
    {
        $this->class_id = $class_id;
    }

    /**
     * @return mixed
     */
    public function getObjectIdentifier()
    {
        return $this->object_identifier;
    }

    /**
     * @param mixed $object_identifier
     */
    public function setObjectIdentifier($object_identifier)
    {
        $this->object_identifier = $object_identifier;
    }

    /**
     * @return mixed
     */
    public function getEntriesInheriting()
    {
        return $this->entries_inheriting;
    }

    /**
     * @param mixed $entries_inheriting
     */
    public function setEntriesInheriting($entries_inheriting)
    {
        $this->entries_inheriting = $entries_inheriting;
    }
}