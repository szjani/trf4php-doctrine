<?php
declare(strict_types=1);

namespace trf4php\doctrine\impl;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use trf4php\doctrine\EntityManagerProxy;

/**
 * Used for transaction based EntityManagers.
 * The entity manager passed to the constructor is the default "shared" em.
 * Calling setEntityManager() is the way the change the wrapped instance,
 * unless the parameter is null: in this case the "shared" em will be restored.
 *
 * If the "shared" em is null, exception will be thrown in interaction
 * on the wrapped instance.
 *
 * @package trf4php\doctrine
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DefaultEntityManagerProxy extends EntityManagerDecorator implements EntityManagerProxy
{
    private $shared;

    /**
     * @param EntityManagerInterface $shared
     */
    public function __construct(EntityManagerInterface $shared = null)
    {
        $this->shared = $shared !== null ? $shared : new NullObject();
        $this->wrapped = $this->shared;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setWrapped(EntityManagerInterface $em = null) : void
    {
        if ($em === null) {
            $em = $this->shared;
        }
        $this->wrapped = $em;
    }

    /**
     * @return EntityManagerInterface|NullObject
     */
    public function getWrapped()
    {
        return $this->wrapped;
    }
}
