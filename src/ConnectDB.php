<?php

namespace Omegaalfa\Database;


use PDO;
use Omegaalfa\Database\PDOConnector;


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
	 * @return PDO
	 */
	protected function getDb(): PDO
	{
		$this->conn = new PDOConnector();
		if(!$this->conn->isConnected()) {
			$this->conn->connect();
		}

		return $this->conn->getConnection();
	}

}
