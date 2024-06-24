<?php

declare(strict_types = 1);

namespace Omegaalfa\Database;


use Exception;
use PDO;
use PDOException;

class PDOConnector
{

	/**
	 * @var PDO|null
	 */
	private ?PDO $instance = null;

	/**
	 * @var array<string|int, mixed>
	 */
	protected array $setAttribute;

	/**
	 * @throws Exception
	 */
	public function __construct(
		protected string $dbConnect,
		protected string $dbHost,
		protected string $dbPort,
		protected string $dbase,
		protected string $dbCharset,
		protected string $dbUsername,
		protected string $dbPassword
	) {
		try {
			$this->setAttribute = [
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				PDO::ATTR_PERSISTENT         => true,
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
			];

			$dns = sprintf(
			/** @lang text */ '%s:host=%s;port=%s;dbname=%s;charset=%s',
				$dbConnect,
				$dbHost,
				$dbPort,
				$dbase,
				$dbCharset
			);

			if(!$this->isConnected()) {
				$this->instance = new PDO($dns, $dbUsername, $dbPassword, $this->setAttribute);
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
	 *
	 */
	public function disconnect(): void
	{
		if($this->isConnected()) {
			$this->instance = null;
		}
	}

	/**
	 * @return PDO
	 * @throws Exception
	 */
	public function getConnection(): PDO
	{
		if($this->instance === null || !$this->isConnected()) {
			$this->connect();
		}

		if($this->instance instanceof PDO) {
			return $this->instance;
		}

		return new PDO('');
	}

	/**
	 * @return PDOConnector
	 * @throws Exception
	 */
	public function connect(): PDOConnector
	{
		return new PDOConnector(
			$this->dbConnect,
			$this->dbHost,
			$this->dbPort,
			$this->dbase,
			$this->dbCharset,
			$this->dbUsername,
			$this->dbPassword,

		);
	}


	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return ($this->instance instanceof PDO);
	}
}
