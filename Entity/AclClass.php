<?php
/**
 * Created by PhpStorm.
 * User: acb222
 * Date: 11/9/16
 * Time: 11:17 AM
 */

namespace AclHelperBundle\Entity;


class AclClass
{
    private $id;
    private $class_type;

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
        return $this->class_type;
    }

    /**
     * @param mixed $class_type
     */
    public function setClassType($class_type)
    {
        $this->class_type = $class_type;
    }
}