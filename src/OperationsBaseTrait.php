<?php

declare(strict_types = 1);

namespace Omegaalfa\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;


trait OperationsBaseTrait
{

	/**
	 * @var array<string|int, mixed>
	 */
	protected array $content;

	/**
	 * @var string
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
	 * @param  array<string|int, mixed>  $entity
	 * @param  string                    $field
	 * @param  mixed                     $value
	 * @param  bool                      $addslashes
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function updated(array $entity, string $field, mixed $value, bool $addslashes = true): bool
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
			$entity = array_map(static function($value) {
				if(is_string($value)) {
					return addslashes($value);
				}
			}, $entity);
		}

		$stmt->execute(array_values($entity));

		return $stmt->rowCount() > 0;
	}

	/**
	 * @param  array<string|int, mixed>  $entity
	 * @param  bool                      $addslashes
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
				$entity = array_map(static function($value) {
					if(is_string($value)) {
						return addslashes($value);
					}
				}, $entity);
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
	 * @param  array<string|int, mixed>  $entity
	 * @param  bool                      $addslashes
	 *
	 * @return bool|array<string|int, mixed>
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
	 * @param  array<string|int, mixed>  $entity
	 * @param  bool                      $addslashes
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
				$entity = array_map(static function($value) {
					if(is_string($value)) {
						return addslashes($value);
					}
				}, $entity);
			}
			$stmt->execute(array_values($entity));

			if($stmt instanceof PDOStatement) {
				return $stmt;
			}
		} catch(Exception|PDOException $e) {
			LogError($e, true, param: $this->table);
		}

		return false;
	}


	/**
	 * @return mixed
	 * @throws Exception
	 */
	protected function foundRows(): mixed
	{
		$sql = 'SELECT FOUND_ROWS()';
		$stmt = $this->getDb()->query($sql);

		if($stmt instanceof \PDOStatement && $stmt->rowCount() > 0) {
			return $stmt->fetch(PDO::FETCH_COLUMN);
		}

		return false;
	}


	/**
	 * @param  string      $fieldId
	 * @param  string|int  $value
	 *
	 * @return bool
	 * @throws Exception
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
	 * @return array<string|int, mixed>|bool
	 * @throws Exception
	 */
	protected function exSql(string $sql): bool|array
	{
		$stmt = $this->getDb()->query($sql);

		if($stmt instanceof \PDOStatement && $stmt->rowCount() > 0) {
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return false;
	}


	/**
	 * @param  string                    $sql
	 * @param  array<string|int, mixed>  $params
	 *
	 * @return array<string|int, mixed>
	 * @throws Exception
	 */
	protected function exSqlPrepare(string $sql, array $params): array
	{
		$stmt = $this->getDb()->prepare($sql);
		$stmt->execute($params);

		if($stmt instanceof \PDOStatement && $stmt->rowCount() > 0) {
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return [];
	}


	/**
	 * @param  mixed  $parameter
	 *
	 * @return bool
	 */
	public function isset(mixed $parameter): bool
	{
		return isset($this->content[$parameter]);
	}

	/**
	 * @param  mixed  $parameter
	 *
	 * @return bool
	 */
	public function unset(mixed $parameter): bool
	{
		if(isset($parameter)) {
			unset($this->content[$parameter]);

			return true;
		}

		return false;
	}
}
