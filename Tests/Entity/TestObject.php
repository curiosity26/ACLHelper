<?php

namespace Curiosity26\AclHelperBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/2/18
 * Time: 2:51 PM
 */

/**
 * Class TestObject
 *
 * @package Curiosity26\AclHelperBundle\Tests\Entity
 * @ORM\Entity()
 * @ORM\Table(name="test_object")
 */
class TestObject
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true, nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=80, nullable=false)
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return TestObject
     */
    public function setId(int $id): TestObject
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return TestObject
     */
    public function setName(string $name): TestObject
    {
        $this->name = $name;

        return $this;
    }
}
