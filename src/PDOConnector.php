<?php

namespace Omegaalfa\Database;


use Exception;
use PDO;
use PDOException;

/**
 * Class PDOConnector
 *
 * @package src\database
 */
class PDOConnector
{

	/**
	 * @var mixed
	 */
	private $instance;

	/**
	 * @var array
	 */
	protected array $setAttribute;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		try {
			$this->setAttribute = [
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				PDO::ATTR_PERSISTENT         => true,
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
			];

			$dns = sprintf(
			/** @lang text */ '%s:host=%s;port=%s;dbname=%s;charset=%s',
				\App\env('DB_CONNECTION'),
				\App\env('DB_HOST'),
				\App\env('DB_PORT'),
				\App\env('DB_DATABASE'),
				\App\env('DB_CHARSET'),
			);


			if(!$this->isConnected()) {
				$this->instance = new PDO
				(
					$dns,
					\App\env('DB_USERNAME'),
					\App\env('DB_PASSWORD'),
					$this->setAttribute
				);
			}
		} catch(PDOException $e) {
			echo 'Error: ' . $e->getmessage();
			die;
		}
	}


	/**
	 *
	 */
	private function __clone()
	{
	}


	/**
	 * @return PDOConnector
	 */
	public function connect(): PDOConnector
	{
		return new PDOConnector();
	}


	/**
	 *
	 */
	public function disconnect(): void
	{
		if($this->isConnected()) {
			$this->instance = null;
		}
	}


	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return ($this->instance instanceof PDO);
	}


	/**
	 * @return PDO
	 */
	public function getConnection(): PDO
	{
		if($this->instance == null) {
			$this->connect();
		}
		return $this->instance;
	}
}

