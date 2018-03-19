<?php
declare(strict_types=1);

namespace trf4php\doctrine\impl;

use Closure;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use trf4php\doctrine\DoctrineTransactionManager;
use trf4php\doctrine\EntityManagerFactory;
use trf4php\doctrine\EntityManagerProxy;
use trf4php\ObservableTransactionManager;

class TransactionalEntityManagerReloaderTest extends TestCase
{
    /**
     * @var TransactionalEntityManagerReloader
     */
    private $reloader;

    private $emFactory;

    public function setUp()
    {
        parent::setUp();
        $this->emFactory = $this->getMockBuilder(EntityManagerFactory::class)->getMock();
        $this->reloader = new TransactionalEntityManagerReloader($this->emFactory);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotDoctrineTransactionManager()
    {
        $manager = $this->getMockBuilder(ObservableTransactionManager::class)->getMock();
        $this->reloader->update($manager, 'any');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotProxy()
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $manager = new DoctrineTransactionManager($em);
        $this->reloader->update($manager, 'any');
    }

    public function testStartTransaction()
    {
        $em = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
        $manager = new DoctrineTransactionManager($em);
        $manager->attach($this->reloader);

        $trEm = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

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
        $em = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
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
        $em = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
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
        $em = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
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
        $em = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
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
        $em = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
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
        $proxy = $this->getMockBuilder(EntityManagerProxy::class)->getMock();
        $manager = new DoctrineTransactionManager($proxy);
        $manager->attach($this->reloader);

        $trEm = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
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

        $trEm = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
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
