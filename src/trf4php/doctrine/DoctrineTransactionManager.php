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
 * @author Szurovecz János <szjani@szjani.hu>
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
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function beginTransactionInner()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * @throws TransactionException
     */
    protected function commitInner()
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
    protected function rollbackInner()
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
