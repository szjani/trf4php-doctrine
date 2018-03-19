<?php
declare(strict_types=1);

namespace trf4php\doctrine\impl;

use RuntimeException;
use trf4php\doctrine\DoctrineTransactionManager;
use trf4php\doctrine\EntityManagerFactory;
use trf4php\doctrine\EntityManagerProxy;
use trf4php\ObservableTransactionManager;
use trf4php\TransactionManagerObserver;

class TransactionalEntityManagerReloader implements TransactionManagerObserver
{
    /**
     * @var EntityManagerFactory
     */
    protected $emFactory;

    /**
     * @param EntityManagerFactory $emFactory
     */
    public function __construct(EntityManagerFactory $emFactory)
    {
        $this->emFactory = $emFactory;
    }

    public function update(ObservableTransactionManager $manager, $event) : void
    {
        if (!($manager instanceof DoctrineTransactionManager)) {
            throw new RuntimeException("It can be used with DoctrineTransactionManager");
        }
        $proxy = $manager->getEntityManager();
        if (!($proxy instanceof EntityManagerProxy)) {
            throw new RuntimeException("EntityManagerProxy instance must be used with DoctrineTransactionManager");
        }
        switch ($event) {
            case ObservableTransactionManager::PRE_BEGIN_TRANSACTION:
            case ObservableTransactionManager::PRE_TRANSACTIONAL:
                $proxy->setWrapped($this->emFactory->create());
                break;
            case ObservableTransactionManager::POST_ROLLBACK:
            case ObservableTransactionManager::ERROR_ROLLBACK:
            case ObservableTransactionManager::POST_COMMIT:
            case ObservableTransactionManager::POST_TRANSACTIONAL:
                $proxy->setWrapped(null);
                break;
        }
    }
}
