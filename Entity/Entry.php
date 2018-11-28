<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:09 AM
 */

namespace Curiosity26\AclHelperBundle\Entity;

class Entry
{
    private $id;
    private $class;
    private $objectIdentity;
    private $securityIdentity;
    private $fieldName;
    private $aceOrder;
    private $mask;
    private $granting;
    private $grantingStrategy;
    private $auditSuccess;
    private $auditFailure;

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
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass(AclClass $class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getObjectIdentity()
    {
        return $this->objectIdentity;
    }

    /**
     * @param ObjectIdentity $object_entity_id
     */
    public function setObjectIdentity(ObjectIdentity $objectIdentity)
    {
        $this->objectIdentity = $objectIdentity;
    }

    /**
     * @return mixed
     */
    public function getSecurityIdentity()
    {
        return $this->securityIdentity;
    }

    /**
     * @param mixed $securityIdentity
     */
    public function setSecurityIdentity(SecurityIdentity $securityIdentity)
    {
        $this->securityIdentity = $securityIdentity;
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param mixed $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return mixed
     */
    public function getAceOrder()
    {
        return $this->aceOrder;
    }

    /**
     * @param mixed $aceOrder
     */
    public function setAceOrder($aceOrder)
    {
        $this->aceOrder = $aceOrder;
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
        return $this->grantingStrategy;
    }

    /**
     * @param mixed $grantingStrategy
     */
    public function setGrantingStrategy($grantingStrategy)
    {
        $this->grantingStrategy = $grantingStrategy;
    }

    /**
     * @return mixed
     */
    public function getAuditSuccess()
    {
        return $this->auditSuccess;
    }

    /**
     * @param mixed $auditSuccess
     */
    public function setAuditSuccess($auditSuccess)
    {
        $this->auditSuccess = $auditSuccess;
    }

    /**
     * @return mixed
     */
    public function getAuditFailure()
    {
        return $this->auditFailure;
    }

    /**
     * @param mixed $auditFailure
     */
    public function setAuditFailure($auditFailure)
    {
        $this->auditFailure = $auditFailure;
    }
}