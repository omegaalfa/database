<?php


namespace Omegaalfa\Database;


use PDO;
use PDOStatement;

class Database
{
	/**
	 * @var mixed
	 */
	private static $instance;

	/**
	 * Conexão com o banco de dados
	 *
	 * @var PDO
	 */
	private static $connection;

	/**
	 * Construtor privado da classe singleton
	 */
	protected function __construct()
	{
		try {
			$opcoes = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
				PDO::ATTR_PERSISTENT         => true
			);
			self::$connection = new PDO("mysql:host=" . env('DB_HOST') . "; dbname=" . env('DB_DATABASE') . "; charset=" . env('DB_CHARSET') . ";",
				env('DB_USERNAME'), env('DB_PASSWORD'), $opcoes);
		} catch(\PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
	 * @return mixed|Database
	 */
	public static function getInstance(): mixed
	{
		if(empty(self::$instance)) {
			self::$instance = new Database();
		}
		return self::$instance;
	}

	/**
	 * Retorna a conexão PDO com o banco de dados
	 *
	 * @return PDO
	 */
	public static function getConn(): PDO
	{
		self::getInstance();
		return self::$connection;
	}

	/**
	 * Prepara a SQl para ser executada posteriormente
	 *
	 * @param  String  $sql
	 *
	 * @return PDOStatement stmt
	 */
	public static function prepare(string $sql): PDOStatement
	{
		return self::getConn()->prepare($sql);
	}

	/**
	 * Retorna o id da última consulta INSERT
	 *
	 * @return int
	 */
	public static function lastInsertId(): int
	{
		return self::getConn()->lastInsertId();
	}

	/**
	 * Inicia uma transação
	 *
	 * @return bool
	 */
	public static function beginTransaction(): bool
	{
		return self::getConn()->beginTransaction();
	}

	/**
	 * Comita uma transação
	 *
	 * @return bool
	 */
	public static function commit(): bool
	{
		return self::getConn()->commit();
	}

	/**
	 * Realiza um rollback na transação
	 *
	 * @return bool
	 */
	public static function rollBack(): bool
	{
		return self::getConn()->rollBack();
	}

}
