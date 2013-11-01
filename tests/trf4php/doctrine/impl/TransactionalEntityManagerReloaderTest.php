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

use Closure;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use trf4php\doctrine\DoctrineTransactionManager;
use trf4php\doctrine\EntityManagerProxy;
use trf4php\ObservableTransactionManager;

class TransactionalEntityManagerReloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionalEntityManagerReloader
     */
    private $reloader;

    private $emFactory;

    public function setUp()
    {
        parent::setUp();
        $this->emFactory = $this->getMock('\trf4php\doctrine\EntityManagerFactory');
        $this->reloader = new TransactionalEntityManagerReloader($this->emFactory);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotDoctrineTransactionManager()
    {
        $manager = $this->getMock('\trf4php\ObservableTransactionManager');
        $this->reloader->update($manager, 'any');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotProxy()
    {
        $em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $manager = new DoctrineTransactionManager($em);
        $this->reloader->update($manager, 'any');
    }

    public function testStartTransaction()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $trEm = $this->getMock('\Doctrine\ORM\EntityManagerInterface');

        $this->emFactory
            ->expects(self::once())
            ->method('create')
            ->will(self::returnValue($trEm));
        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with($trEm);

        $manager->beginTransaction();
    }

    public function testCommit()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with(null);
        $manager->commit();
    }

    public function testRollback()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $em
            ->expects(self::once())
            ->method('close');

        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with(null);

        $manager->rollback();
    }

    /**
     * @expectedException \trf4php\TransactionException
     */
    public function testCommitFails()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $em
            ->expects(self::once())
            ->method('commit')
            ->will(self::throwException(new ConnectionException()));

        $em
            ->expects(self::once())
            ->method('close');

        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with(null);

        try {
            $manager->commit();
        } catch (Exception $e) {
            $manager->rollback();
            throw $e;
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testImplicitFailedTransaction()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $em
            ->expects(self::once())
            ->method('close');

        $em
            ->expects(self::exactly(2))
            ->method('setWrapped');

        try {
            $manager->beginTransaction();
            throw new Exception();
        } catch (Exception $e) {
            $manager->rollback();
            throw $e;
        }
    }

    /**
     * @expectedException \trf4php\TransactionException
     */
    public function testRollbackFails()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $em
            ->expects(self::once())
            ->method('rollback')
            ->will(self::throwException(new ConnectionException()));

        $em
            ->expects(self::once())
            ->method('close');

        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with(null);

        $manager->rollback();
    }

    public function testTransactional()
    {
        $proxy = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($proxy);
        $manager->attach($this->reloader);

        $trEm = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->emFactory
            ->expects(self::once())
            ->method('create')
            ->will(self::returnValue($trEm));
        $proxy
            ->expects(self::exactly(2))
            ->method('setWrapped');

        $manager->transactional(
            function (EntityManagerProxy $em) use ($proxy, $trEm) {
                TransactionalEntityManagerReloaderTest::assertSame($proxy, $em);
                TransactionalEntityManagerReloaderTest::assertSame($trEm, $em->getWrapped());
            }
        );
    }

    public function testTransactionalFails()
    {
        $proxy = new DefaultEntityManagerProxy();
        $manager = new DoctrineTransactionManager($proxy);
        $manager->attach($this->reloader);

        $trEm = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->emFactory
            ->expects(self::once())
            ->method('create')
            ->will(self::returnValue($trEm));

        $trEm
            ->expects(self::once())
            ->method('transactional')
            ->will(
                self::returnCallback(
                    function (Closure $closure) use ($proxy) {
                        return call_user_func($closure, $proxy);
                    }
                )
            );

        $manager->transactional(
            function (EntityManagerProxy $em) use ($proxy, $trEm) {
                TransactionalEntityManagerReloaderTest::assertSame($proxy, $em);
                TransactionalEntityManagerReloaderTest::assertSame($trEm, $em->getWrapped());
            }
        );
    }
}
