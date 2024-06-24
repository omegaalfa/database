<?php

declare(strict_types = 1);

namespace Omegaalfa\Database;


use Exception;
use PDO;
use src\database\PDOConnector;


/**
 * Class ConnectDB
 *
 * @package src\database\orm
 */
abstract class ConnectDB
{

	/**
	 * @var PDOConnector
	 */
	protected PDOConnector $conn;


	/**
	 * @param  string  $dbConnect
	 * @param  string  $dbHost
	 * @param  string  $dbPort
	 * @param  string  $dbase
	 * @param  string  $dbUsername
	 * @param  string  $dbPassword
	 * @param  string  $dbCharset
	 *
	 * @throws Exception
	 */
	public function __construct(
		string $dbConnect,
		string $dbHost,
		string $dbPort,
		string $dbase,
		string $dbUsername,
		string $dbPassword,
		string $dbCharset = 'utf8'
	) {
		$this->conn = new PDOConnector(
			dbConnect: $dbConnect,
			dbHost: $dbHost,
			dbPort: $dbPort,
			dbase: $dbase,
			dbCharset: $dbCharset,
			dbUsername: $dbUsername,
			dbPassword: $dbPassword
		);
	}


	/**
	 * @return PDO
	 * @throws Exception
	 */
	protected function getDb(): PDO
	{
		return $this->conn->getConnection();
	}


	/**
	 * @return bool|string
	 * @throws Exception
	 */
	public function lastInsertId(): bool|string
	{
		return $this->getDb()->lastInsertId();
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function beginTransaction(): bool
	{
		return $this->getDb()->beginTransaction();
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function commit(): bool
	{
		return $this->getDb()->commit();
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function rollBack(): bool
	{
		return $this->getDb()->rollBack();
	}
}
