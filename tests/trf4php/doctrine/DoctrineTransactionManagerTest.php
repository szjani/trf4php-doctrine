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

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use trf4php\doctrine\entities\User;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/entities/User.php';

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DoctrineTransactionManagerTest extends PHPUnit_Framework_TestCase
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
        $this->entityManager->refresh($user);
        $this->entityManager->close();

        self::assertEquals(__METHOD__, $user->getName());
    }

    public function testAnonymousFunction()
    {
        $user = new User();
        $user->setName(__METHOD__);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->transactionManager->transactional(
            function () use ($user) {
                $user->setName('mod');
            }
        );
        $this->entityManager->refresh($user);
        self::assertEquals('mod', $user->getName());
    }
}
