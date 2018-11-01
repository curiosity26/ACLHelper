<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:09 AM
 */

namespace AclHelperBundle\Entity;


class Entry
{
    private $id;
    private $class_id;
    private $object_identity_id;
    private $security_identity_id;
    private $field_name;
    private $ace_order;
    private $mask;
    private $granting;
    private $granting_strategy;
    private $audit_success;
    private $audit_failure;

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
    public function getObjectIdentityId()
    {
        return $this->object_identity_id;
    }

    /**
     * @param mixed $object_entity_id
     */
    public function setObjectIdentityId($object_identity_id)
    {
        $this->object_identity_id = $object_identity_id;
    }

    /**
     * @return mixed
     */
    public function getSecurityIdentityId()
    {
        return $this->security_identity_id;
    }

    /**
     * @param mixed $security_identity_id
     */
    public function setSecurityIdentityId($security_identity_id)
    {
        $this->security_identity_id = $security_identity_id;
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * @param mixed $field_name
     */
    public function setFieldName($field_name)
    {
        $this->field_name = $field_name;
    }

    /**
     * @return mixed
     */
    public function getAceOrder()
    {
        return $this->ace_order;
    }

    /**
     * @param mixed $ace_order
     */
    public function setAceOrder($ace_order)
    {
        $this->ace_order = $ace_order;
    }

    /**
     * @return mixed
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @param mixed $mask
     */
    public function setMask($mask)
    {
        $this->mask = $mask;
    }

    /**
     * @return mixed
     */
    public function getGranting()
    {
        return $this->granting;
    }

    /**
     * @param mixed $granting
     */
    public function setGranting($granting)
    {
        $this->granting = $granting;
    }

    /**
     * @return mixed
     */
    public function getGrantingStrategy()
    {
        return $this->granting_strategy;
    }

    /**
     * @param mixed $granting_strategy
     */
    public function setGrantingStrategy($granting_strategy)
    {
        $this->granting_strategy = $granting_strategy;
    }

    /**
     * @return mixed
     */
    public function getAuditSuccess()
    {
        return $this->audit_success;
    }

    /**
     * @param mixed $audit_success
     */
    public function setAuditSuccess($audit_success)
    {
        $this->audit_success = $audit_success;
    }

    /**
     * @return mixed
     */
    public function getAuditFailure()
    {
        return $this->audit_failure;
    }

    /**
     * @param mixed $audit_failure
     */
    public function setAuditFailure($audit_failure)
    {
        $this->audit_failure = $audit_failure;
    }
}