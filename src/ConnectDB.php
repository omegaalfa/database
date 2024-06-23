<?php

declare(strict_types = 1);

namespace Omegaalfa\Database;


use PDO;
use Omegaalfa\Database\PDOConnector;


abstract class ConnectDB
{

	/**
	 * @var PDOConnector
	 */
	protected PDOConnector $conn;


	public function __construct()
	{
		$this->conn = new PDOConnector();
	}


	/**
	 * @return PDO
	 */
	protected function getDb(): PDO
	{
		return $this->conn->getConnection();
	}


	/**
	 * Retorna o id da última consulta INSERT
	 *
	 * @return false|string
	 */
	public function lastInsertId(): bool|string
	{
		return $this->getDb()->lastInsertId();
	}

	/**
	 * Inicia uma transação
	 *
	 * @return bool
	 */
	public function beginTransaction(): bool
	{
		return $this->getDb()->beginTransaction();
	}

	/**
	 * Comita uma transação
	 *
	 * @return bool
	 */
	public function commit(): bool
	{
		return $this->getDb()->commit();
	}

	/**
	 * Realiza um rollback na transação
	 *
	 * @return bool
	 */
	public function rollBack(): bool
	{
		return $this->getDb()->rollBack();
	}
}
