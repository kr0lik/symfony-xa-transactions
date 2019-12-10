<?php

namespace kr0lik\XATransaction;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class XATransactionService
 * @package App\Service
 */
final class XATransactionService
{
    /**
     * @var XADBALConnection
     */
    private $connection;

    /**
     * XATransactionService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        /**
         * @var XADBALConnection $connection
         */
        $connection = $em->getConnection();

        $this->connection = $connection;
    }

    /**
     * @param string $xaTransactionId
     *
     * @return void
     */
    public function switchToXaTransaction(string $xaTransactionId): void
    {
        $this->checkConnection();
        $this->connection->setXaTransactionId($xaTransactionId);
    }

    /**
     *  @return void
     */
    public function switchToGlobalTransaction(): void
    {
        $this->connection->setXaTransactionId();
    }

    /**
     * @param string $xaTransactionId
     *
     * @return void
     */
    public function commit(string $xaTransactionId): void
    {
        $this->switchToXaTransaction($xaTransactionId);
        $this->connection->commitXaTransaction();
        $this->switchToGlobalTransaction();
    }

    /**
     * @param string $xaTransactionId
     *
     * @return void
     */
    public function rollback(string $xaTransactionId): void
    {
        $this->switchToXaTransaction($xaTransactionId);
        $this->connection->rollbackXaTransaction();
        $this->switchToGlobalTransaction();
    }

    /**
     * @return void
     */
    private function checkConnection(): void
    {
        if(!$this->connection instanceof XADBALConnection) {
            throw new \RuntimeException('To use xa transactions requires XADBALConnection');
        }
    }
}