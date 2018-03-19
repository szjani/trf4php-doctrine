<?php
declare(strict_types=1);

namespace trf4php\doctrine\impl;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use trf4php\doctrine\EntityManagerFactory;

class DefaultEntityManagerFactory implements EntityManagerFactory
{
    /**
     * @var mixed
     */
    private $conn;

    /**
     * @var \Doctrine\ORM\Configuration
     */
    private $config;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * @param $conn
     * @param Configuration $config
     * @param EventManager $eventManager
     */
    public function __construct($conn, Configuration $config, EventManager $eventManager = null)
    {
        $this->conn = $conn;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * Factory method to create EntityManager instances.
     *
     * @return EntityManagerInterface The created EntityManager.
     *
     * @throws \InvalidArgumentException
     * @throws ORMException
     */
    public function create() : EntityManagerInterface
    {
        return EntityManager::create($this->conn, $this->config, $this->eventManager);
    }
}
