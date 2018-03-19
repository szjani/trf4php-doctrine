<?php
declare(strict_types=1);

namespace trf4php\doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use trf4php\doctrine\entities\User;

require_once __DIR__ . '/entities/User.php';

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DoctrineTransactionManagerTest extends TestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DoctrineTransactionManager
     */
    private $transactionManager;

    private function initMembers()
    {
        $dbParams = array(
            'driver' => 'pdo_sqlite',
            'memory' => true
        );
        $config = new Configuration();
        $metadataDriver = $config->newDefaultAnnotationDriver(array(__DIR__ . '/entities'));
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setProxyDir(__DIR__ . '/entities');
        $config->setProxyNamespace(__NAMESPACE__ . '\entities');
        $this->entityManager = EntityManager::create($dbParams, $config);
        $this->transactionManager = new DoctrineTransactionManager($this->entityManager);
    }

    private function initDb()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = array(
            $this->entityManager->getClassMetadata(__NAMESPACE__ . '\entities\User')
        );
        $tool->createSchema($classes);
    }

    public function setUp()
    {
        $this->initMembers();
        $this->initDb();
    }

    public function tearDown()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = array(
            $this->entityManager->getClassMetadata(__NAMESPACE__ . '\entities\User')
        );
        $tool->dropSchema($classes);
    }

    public function testCreateTable()
    {
        $this->transactionManager->beginTransaction();
        $user = new User();
        $user->setName(__METHOD__);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->transactionManager->commit();
        self::assertNotNull($user->getId());
    }

    public function testRollback()
    {
        $this->transactionManager->beginTransaction();
        $user = new User();
        $user->setName(__METHOD__);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->transactionManager->commit();

        $this->transactionManager->beginTransaction();
        $user->setName('mod');
        $this->entityManager->flush();
        $this->transactionManager->rollback();
        self::assertFalse($this->entityManager->isOpen());
        self::assertEquals(UnitOfWork::STATE_DETACHED, $this->entityManager->getUnitOfWork()->getEntityState($user));
    }

    public function testAnonymousFunction()
    {
        $user = new User();
        $user->setName(__METHOD__);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $result = $this->transactionManager->transactional(
            function () use ($user) {
                $user->setName('mod');
                return 'return value';
            }
        );
        $this->entityManager->refresh($user);
        self::assertEquals('mod', $user->getName());
        self::assertEquals('return value', $result);
    }
}
