<?php
declare(strict_types=1);

namespace trf4php\doctrine;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerProxy extends EntityManagerInterface
{
    public function setWrapped(EntityManagerInterface $em = null) : void;

    public function getWrapped();
}
