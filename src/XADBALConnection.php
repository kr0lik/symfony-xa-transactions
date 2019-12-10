<?php

namespace kr0lik\XATransaction;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Connection as DriverConnection;

class XADBALConnection extends Connection
{
    /**
     * @var string|null
     */
    private $xaTransactionId;

    /**
     * @var DriverConnection
     */
    protected $_conn;

    /**
     * @param string|null $xaTransactionId
     */
    public function setXaTransactionId(?string $xaTransactionId = null): void
    {
        $this->xaTransactionId = $xaTransactionId;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction()
    {
        if($this->xaTransactionId) {
            $this->finishGlobalTransactions();
            $this->beginXATransaction();

            return;
        }

        parent::beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        if($this->xaTransactionId) {
            $this->endXaTransaction();
            $this->prepareXaTransaction();
            $this->reConnect();

            return;
        }

        parent::commit();
    }

    /**
     * Start XA transaction
     *
     * @return void
     */
    private function beginXATransaction(): void
    {
        $this->checkXaTransaction();

        $logger = $this->_config->getSQLLogger();

        if($logger) {
            $logger->startQuery('XA START ' . $this->xaTransactionId);
        }

        $this->_conn->exec('XA START "'.$this->xaTransactionId.'"');

        if ($logger) {
            $logger->stopQuery();
        }
    }

    /**
     * End XA transaction
     *
     * @return void
     */
    private function endXaTransaction(): void
    {
        $this->checkXaTransaction();

        $logger = $this->_config->getSQLLogger();

        if($logger) {
            $logger->startQuery('XA END ' . $this->xaTransactionId);
        }

        $this->_conn->exec('XA END "'.$this->xaTransactionId.'"');

        if ($logger) {
            $logger->stopQuery();
        }
    }

    /**
     * Rollback XA transaction
     *
     * @return void
     */
    public function rollbackXaTransaction()
    {
        $this->checkXaTransaction();

        $logger = $this->_config->getSQLLogger();

        if($logger) {
            $logger->startQuery('XA ROLLBACK ' . $this->xaTransactionId);
        }

        $this->_conn->exec('XA ROLLBACK "' . $this->xaTransactionId . '"');

        if ($logger) {
            $logger->stopQuery();
        }
    }

    /**
     * Prepare XA transaction
     *
     * @return void
     */
    public function prepareXaTransaction()
    {
        $this->checkXaTransaction();

        $logger = $this->_config->getSQLLogger();

        if($logger) {
            $logger->startQuery('XA PREPARE ' . $this->xaTransactionId);
        }

        $this->_conn->exec('XA PREPARE "' . $this->xaTransactionId . '"');

        if ($logger) {
            $logger->stopQuery();
        }
    }

    /**
     * Commit XA transaction
     *
     * @return void
     */
    public function commitXaTransaction()
    {
        $this->checkXaTransaction();

        $logger = $this->_config->getSQLLogger();

        if($logger) {
            $logger->startQuery('XA COMMIT ' . $this->xaTransactionId);
        }

        $this->_conn->exec('XA COMMIT "' . $this->xaTransactionId . '"');

        if ($logger) {
            $logger->stopQuery();
        }
    }

    /**
     * @return void
     */
    private function reConnect(): void
    {
        $this->close();
        $this->connect();
    }

    /**
     * @throws ConnectionException
     *
     * @return void
     */
    private function finishGlobalTransactions(): void
    {
        while ($this->getTransactionNestingLevel() !== 0) {
            parent::commit();
        }
    }

    /**
     * @return void
     */
    private function checkXaTransaction(): void
    {
        if(!$this->xaTransactionId) {
            throw new \LogicException('Unable to rollback XA transaction without XA transaction id');
        }
    }
}