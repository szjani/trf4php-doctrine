trf4php-doctrine
==============

This is a Doctrine binding for [trf4php](https://github.com/szjani/trf4php)

Using trf4php-doctrine
----------------------

### Configuration

```php
<?php
/* @var $em \Doctrine\ORM\EntityManager */
$tm = new DoctrineTransactionManager($em);
```

### Using transactions

```php
<?php
/* @var $tm TransactionManager */
try {
    $tm->beginTransaction();
    // database modifications
    $tm->commit();
} catch (TransactionException $e) {
    $tm->rollback();
}
```

Transactional EntityManager
---------------------------

If a transaction fails, you have to close your EntityManager. Doctrine says that after closing an EM,
you have to create another one if you want to use database. `TransactionalEntityManagerReloader` does it automatically.

To enable this feature, you have to do the following steps:

* Use `EntityManagerProxy` in `DoctrineTransactionManager`
* Attach `TransactionalEntityManagerReloader` observer to `DoctrineTransactionManager`

```php
$tm = new DoctrineTransactionManager(new DefaultEntityManagerProxy());
$emFactory = new DefaultEntityManagerFactory($conn, $config);
$tm->attach(new TransactionalEntityManagerReloader($emFactory));
```

If you would like to use a shared, non-transactional EntityManager, pass it to the constructor of `DefaultEntityManagerProxy`.
In this case you can use the proxy object without starting a transaction, which is not recommended, but sometimes necessary.

This feature is also useful in integration tests. You can rollback in `tearDown()` thus you don't need to reinitialize the database. It highly speed-up your tests.

History
-------

### 1.2

#### Transactional EntityManager

Create an EntityManager right after you start a transaction.