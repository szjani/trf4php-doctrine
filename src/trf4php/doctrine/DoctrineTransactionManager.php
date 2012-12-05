<?php
/*
 * Copyright (c) 2012 Szurovecz János
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
use Doctrine\ORM\EntityManager;
use Exception;
use trf4php\TransactionException;
use trf4php\TransactionManager;

/**
 * Doctrine implementation of TransactionManager.
 * EntityManager is used and wrapped.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DoctrineTransactionManager implements TransactionManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * @throws TransactionException
     */
    public function commit()
    {
        try {
            $this->entityManager->commit();
        } catch (ConnectionException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws TransactionException
     */
    public function rollback()
    {
        try {
            $this->entityManager->rollback();
        } catch (ConnectionException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Closure $func
     * @return mixed
     * @throws TransactionException
     * @throws Exception Throwed by $func
     */
    public function transactional(Closure $func)
    {
        try {
            return $this->entityManager->transactional($func);
        } catch (ConnectionException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
