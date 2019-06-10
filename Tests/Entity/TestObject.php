<?php

namespace Curiosity26\AclHelperBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var TestObject|null
     * @ORM\ManyToOne(targetEntity="Curiosity26\AclHelperBundle\Tests\Entity\TestObject", inversedBy="parent")
     */
    private $parent;

    /**
     * @var Collection|TestObject[]
     * @ORM\OneToMany(targetEntity="Curiosity26\AclHelperBundle\Tests\Entity\TestObject", mappedBy="parent",
     *     cascade={"all"})
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

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

    /**
     * @return TestObject|null
     */
    public function getParent(): ?TestObject
    {
        return $this->parent;
    }

    /**
     * @param TestObject|null $parent
     *
     * @return TestObject
     */
    public function setParent(?TestObject $parent): TestObject
    {
        $oldParent    = $this->parent;
        $this->parent = $parent;

        if (null !== $oldParent && $oldParent !== $parent) {
            $oldParent->removeChild($this);
        }

        if (null !== $parent && $oldParent !== $parent) {
            $parent->addChild($this);
        }

        return $this;
    }

    /**
     * @return TestObject[]|Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param TestObject[]|Collection $children
     *
     * @return TestObject
     */
    public function setChildren($children): TestObject
    {
        $this->children = new ArrayCollection();

        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @param TestObject $child
     *
     * @return TestObject
     */
    public function addChild(TestObject $child): TestObject
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @param TestObject $child
     *
     * @return TestObject
     */
    public function removeChild(TestObject $child): TestObject
    {
        if ($this->children->contains($child)) {
            $this->children->remove($child);
            $child->setParent(null);
        }

        return $this;
    }
}
