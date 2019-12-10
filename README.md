# Symfony-XA-Transactions
Mysql xa-transactions for symfony

# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require kr0lik/symfony-xa-transactions
```

# Usage

1. Add XADBALConnection to doctrine.yaml:

```
doctrine:
    dbal:
        # configure these for your database server_ci
        default_connection:       default
        connections:
            default:
                url: '%env(resolve:DATABASE_URL)%'
                driver: 'pdo_mysql'
                server_version: '8.0'
                wrapper_class: 'kr0lik\XATransaction\XADBALConnection'
                charset: utf8mb4
                default_table_options:
                            charset: utf8mb4
                            collate: utf8mb4_unicode_ci
```

2. Im services.yaml:

```
    kr0lik\XATransaction\XATransactionService: ~
```

3. In your code, when you need xa-transactions:

```php
namespace App\SomeDir;

use Doctrine\ORM\EntityManagerInterface;
use kr0lik\XATransaction\XATransactionService;

class SomeYourClass
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var XATransactionService
     */
    private $transactionService;

    /**
     * XaTransactionController constructor.
     * @param EntityManagerInterface $em
     * @param XATransactionService $transactionService
     */
    public function __construct(
        EntityManagerInterface $em,
        XATransactionService $transactionService
    ) {
        $this->em = $em;
        $this->transactionService = $transactionService;
    }
    
    /**
     * Create transaction example
     */
    protected function create(): void
    {
        $entity = new Entity();

        $this->em->persist($entity);

        $transactionId = md5('test transaction');
        $this->transactionService->switchToXaTransaction($transactionId);

        $this->em->flush();

        $this->transactionService->switchToGlobalTransaction();
    }

    /**
     * Commit transaction example
     */
    protected function commit(GatewayDto $gatewayDto): void
    {
        $transactionId = md5('test transaction');

        $this->transactionService->commit($transactionId);
    }

    /**
     * Rollback transaction example
     */
    protected function rollback(GatewayDto $gatewayDto): void
    {
        $transactionId = md5('test transaction');

        $this->transactionService->rollback($transactionId);
    }
}
```