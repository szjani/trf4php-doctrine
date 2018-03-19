<?php
declare(strict_types=1);

namespace trf4php\doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

abstract class AbstractEntityRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $entityManager, $entityClass)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($entityClass));
    }
}
