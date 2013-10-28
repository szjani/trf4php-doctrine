<?php
/*
 * Copyright (c) 2013 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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

    public function update(ObservableTransactionManager $manager, $event)
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
                $proxy->close();
            case ObservableTransactionManager::POST_COMMIT:
            case ObservableTransactionManager::POST_TRANSACTIONAL:
                $proxy->setWrapped(null);
        }
    }
}
