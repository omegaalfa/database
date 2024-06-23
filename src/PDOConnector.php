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
				$this->valideValue(\App\env('DB_CONNECTION')),
				$this->valideValue(\App\env('DB_HOST')),
				$this->valideValue(\App\env('DB_PORT')),
				$this->valideValue(\App\env('DB_DATABASE')),
				$this->valideValue(\App\env('DB_CHARSET'))
			);


			if(!$this->isConnected()) {
				$this->instance = new PDO
				(
					$dns,
					$this->valideValue(\App\env('DB_USERNAME')),
					$this->valideValue(\App\env('DB_PASSWORD')),
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
	 * @param  mixed  $value
	 *
	 * @return string|null
	 */
	private function valideValue(mixed $value): ?string
	{
		if(is_string($value)) {
			return $value;
		}

		return null;
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
	 */
	public function connect(): PDOConnector
	{
		return new PDOConnector();
	}


	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return ($this->instance instanceof PDO);
	}
}
