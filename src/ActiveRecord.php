<?php

namespace Omegaalfa\Database;


use Exception;
use PDO;
use PDOException;
use PDOStatement;


abstract class ActiveRecord extends ConnectDB
{

	/**
	 * @var mixed
	 */
	protected mixed $content;

	/**
	 * @var mixed
	 */
	protected string $table;

	/**
	 * @var mixed
	 */
	protected mixed $idField = null;

	/**
	 * @var mixed
	 */
	protected mixed $field = null;

	/**
	 * @param  array   $entity
	 * @param  string  $field
	 * @param  mixed   $value
	 * @param  bool    $addslashes
	 *
	 * @return bool
	 */
	protected function updated(array $entity, string $field, mixed $value, bool $addslashes = true): bool
	{
		$statement = '';

		foreach($entity as $key => $v) {
			$statement .= $key . '=?,';
		}

		$statement = substr($statement, 0, -1);
		$sql = sprintf(/** @lang text */ "UPDATE %s SET %s WHERE %s = ?;", $this->table, $statement, $field);
		$stmt = $this->getDb()->prepare($sql);
		$entity[] = $value;

		if($addslashes) {
			$entity = array_map('addslashes', $entity);
		}

		$stmt->execute(array_values($entity));

		return $stmt->rowCount() > 0;
	}

	/**
	 * @param  array  $entity
	 * @param  bool   $addslashes
	 *
	 * @return bool|PDOStatement
	 */
	protected function select(array $entity, bool $addslashes = true): bool|PDOStatement
	{
		$condition = [];
		foreach($entity as $key => $value) {
			$condition[] = $key . ' = ?';
		}

		$sql = sprintf(
		/** @lang text */ "SELECT * FROM {$this->table} WHERE %s ;",
			implode(" AND ", $condition)
		);

		try {
			$db = $this->getDb();
			$stmt = $db->prepare($sql);
			if($addslashes) {
				$entity = array_map('addslashes', $entity);
			}

			$stmt->execute(array_values($entity));
		} catch(Exception|PDOException $e) {
			LogError($e, false);
			return false;
		}

		if($stmt instanceof PDOStatement) {
			return $stmt;
		}

		return false;
	}

	/**
	 * @param  array  $entity
	 * @param  bool   $addslashes
	 *
	 * @return array|false
	 */
	protected function sqlInsertedWherePrepare(array $entity, bool $addslashes = true): bool|array
	{
		$column = [];
		$statement = [];
		$whereSelect = $this->select($entity, $addslashes);

		if($whereSelect instanceof PDOStatement && $whereSelect->rowCount() > 0) {
			return false;
		}

		foreach($entity as $key => $value) {
			$column[] = $key;
			$statement[] = '?';
		}


		$sql = sprintf(
		/** @lang text */ "INSERT INTO {$this->table} (%s) VALUES (%s);",
			implode(', ', $column),
			implode(', ', $statement)
		);


		return [
			'sql'    => $sql,
			'values' => json_encode(array_values($entity)),
		];
	}


	/**
	 * @param  array  $entity
	 * @param  bool   $addslashes
	 *
	 * @return false|PDOStatement
	 */
	protected function inserted(array $entity, bool $addslashes = true): bool|PDOStatement
	{
		$column = [];
		$statement = [];

		foreach($entity as $key => $value) {
			$column[] = $key;
			$statement[] = '?';
		}

		$sql = sprintf(
		/** @lang text */ "INSERT INTO {$this->table} (%s) VALUES (%s);",
			implode(', ', $column),
			implode(', ', $statement)
		);

		try {
			$db = $this->getDb();
			$stmt = $db->prepare($sql);
			if($addslashes) {
				$entity = array_map('addslashes', $entity);
			}
			$stmt->execute(array_values($entity));
		} catch(Exception|PDOException $e) {
			LogError($e, true, param: $this->table);
		}

		if($stmt instanceof PDOStatement) {
			return $stmt;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	protected function foundRows(): bool
	{
		$sql = 'SELECT FOUND_ROWS()';
		$stmt = $this->getDb()->query($sql);

		if($stmt->rowCount() > 0) {
			return $stmt->fetch(PDO::FETCH_COLUMN);
		}

		return false;
	}


	/**
	 * @param  string      $fieldId
	 * @param  string|int  $value
	 *
	 * @return bool
	 */
	protected function deleted(string $fieldId, string|int $value): bool
	{
		$sql = sprintf(/** @lang text */ "DELETE FROM %s WHERE %s  = ?;", $this->table, $fieldId);
		$stmt = $this->getDb()->prepare($sql);
		$stmt->execute([$value]);

		return $stmt->rowCount() > 0;
	}

	/**
	 * @param  string  $sql
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	protected function exSql(string $sql): bool|array
	{
		$stmt = $this->getDb()->query($sql);

		if($stmt->rowCount() > 0) {
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return false;
	}


	/**
	 * @param  string  $sql
	 * @param  array   $params
	 *
	 * @return array
	 */
	protected function exSqlPrepare(string $sql, array $params): array
	{
		$stmt = $this->getDb()->prepare($sql);
		$stmt->execute($params);

		if($stmt->rowCount() > 0) {
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return [];
	}

	/**
	 * @return Sql
	 */
	protected function sql(): Sql
	{
		return new Sql();
	}

	/**
	 *
	 */
	private function __clone()
	{
		if($this->isset($this->content[$this->idField])) {
			$this->unset($this->content[$this->idField]);
		}
	}

	/**
	 * @param $parameter
	 *
	 * @return bool
	 */
	public function isset($parameter): bool
	{
		return isset($this->content[$parameter]);
	}

	/**
	 * @param $parameter
	 *
	 * @return bool
	 */
	public function unset($parameter): bool
	{
		if(isset($parameter)) {
			unset($this->content[$parameter]);

			return true;
		}

		return false;
	}

}
