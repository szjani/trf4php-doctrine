<?php
declare(strict_types=1);

namespace trf4php\doctrine;

use Closure;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use trf4php\AbstractObservableTransactionManager;
use trf4php\TransactionException;

/**
 * Doctrine implementation of TransactionManager.
 * EntityManager is used and wrapped.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DoctrineTransactionManager extends AbstractObservableTransactionManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getEntityManager() : EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function beginTransactionInner() : void
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * @throws TransactionException
     */
    protected function commitInner() : void
    {
        try {
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (ConnectionException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws TransactionException
     */
    protected function rollbackInner() : void
    {
        try {
            $this->entityManager->close();
            $this->entityManager->rollback();
        } catch (ConnectionException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Closure $func
     * @return mixed
     * @throws TransactionException
     * @throws Exception Thrown by $func
     */
    protected function transactionalInner(Closure $func)
    {
        try {
            return $this->entityManager->transactional($func);
        } catch (ConnectionException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
