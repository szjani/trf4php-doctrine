<?php
declare(strict_types=1);

namespace trf4php\doctrine\entities;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 *
 * @Entity
 * @Table(name="user")
 */
class User
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue
     * @var int
     */
    private $id;

    /**
     * @Column(name="name", type="string", length=255, nullable=false)
     * @var string
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
