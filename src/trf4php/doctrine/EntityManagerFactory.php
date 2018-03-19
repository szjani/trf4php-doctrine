<?php
declare(strict_types=1);

namespace trf4php\doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

interface EntityManagerFactory
{
    /**
     * Factory method to create EntityManager instances.
     *
     * @return EntityManagerInterface The created EntityManager.
     *
     * @throws \InvalidArgumentException
     * @throws ORMException
     */
    public function create() : EntityManagerInterface;
}
