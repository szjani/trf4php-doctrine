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
