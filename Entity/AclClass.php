<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:17 AM
 */

namespace Curiosity26\AclHelperBundle\Entity;

class AclClass
{
    private $id;
    private $classType;

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
    public function getClassType()
    {
        return $this->classType;
    }

    /**
     * @param mixed $classType
     */
    public function setClassType($classType)
    {
        $this->classType = $classType;
    }
}