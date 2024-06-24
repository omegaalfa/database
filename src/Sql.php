<?php

declare(strict_types = 1);

namespace Omegaalfa\Database;


use Exception;
use Generator;
use PDO;
use PDOException;
use PDOStatement;


class Sql extends ConnectDB
{

	/**
	 * @var string
	 */
	private string $table;

	/**
	 * @var array<string|int, mixed>
	 */
	private array $fields = [];

	/**
	 * @var array<string|int, mixed>
	 */
	private array $from = [];

	/**
	 * @var array<string|int, mixed>
	 */
	private array $where = [];

	/**
	 * @var array<string|int, mixed>
	 */
	private array $like = [];

	/**
	 * @var ?array<string|int, mixed>
	 */
	private ?array $orderBy = [];

	/**
	 * @var ?array<string|int, mixed>
	 */
	private ?array $limit = [];

	/**
	 * @var array<string|int, mixed>
	 */
	private array $join = [];

	/**
	 * @var string
	 */
	private string $sql = '';

	/**
	 * @var string
	 */
	private string $alias;

	/**
	 * @var int
	 */
	private int $rowsCount;

	/**
	 * @var ?array<string|int, mixed>
	 */
	private ?array $data;

	/**
	 * @var bool
	 */
	private bool $pagination = false;


	/**
	 * @param  array<string|int, mixed>  $fields
	 *
	 * @return Sql
	 */
	public function select(array $fields = ['*']): Sql
	{
		$this->fields = $fields;

		return $this;
	}

	/**
	 * @param  string  $table
	 * @param  string  $alias
	 *
	 * @return Sql
	 */
	public function from(string $table, string $alias): Sql
	{
		$this->table = $table;
		$this->alias = $alias;
		$this->from[] = $table . ' AS ' . $alias;

		return $this;
	}


	/**
	 * @param  ?array<string|int, mixed>  $condition
	 * @param  string                     $operator
	 * @param  bool                       $alias
	 *
	 * @return Sql
	 */
	public function where(?array $condition, string $operator = "=", bool $alias = false): Sql
	{
		if(is_null($condition)) {
			$this->where = [];
		}

		if(is_array($condition) && !$alias) {
			foreach($condition as $key => $value) {
				if($key && $value) {
					$this->where[] = sprintf("%s %s '%s'", $key, $operator, $value);
				}
			}
		}

		if(is_array($condition) && $alias) {
			foreach($condition as $key => $value) {
				if($key && $value) {
					$this->where[] = sprintf("%s.%s %s '%s'", $this->alias, $key, $operator, $value);
				}
			}
		}

		return $this;
	}


	/**
	 * @param  ?array<string|int, mixed>  $condition
	 *
	 * @return Sql
	 */
	public function like(array|null $condition): Sql
	{
		if(is_null($condition)) {
			$this->like = [];
		}

		if(is_array($condition)) {
			foreach($condition as $key => $value) {
				if(is_bool($value) || is_float($value) || is_int($value) || is_string($value) || is_null($value)) {
					$this->like[] = sprintf("%s LIKE '%s%s%s'", $key, '%', $value, '%');
				}
			}
		}

		return $this;
	}


	/**
	 * @param ?array<string|int, mixed>  $condition
	 *
	 * @return $this
	 */
	public function likeLeft(array|null $condition): Sql
	{
		if(is_null($condition)) {
			$this->like = [];
		}

		if(is_array($condition)) {
			foreach($condition as $key => $value) {
				if(is_bool($value) || is_float($value) || is_int($value) || is_string($value) || is_null($value)) {
					$this->like[] = sprintf("%s LIKE '%s%s'", $key, '%', $value);
				}
			}
		}

		return $this;
	}

	/**
	 * @param  ?array<string|int, mixed>  $condition
	 *
	 * @return $this
	 */
	public function likeRight(array|null $condition): Sql
	{
		if(is_null($condition)) {
			$this->like = [];
		}

		if(is_array($condition)) {
			foreach($condition as $key => $value) {
				if(is_bool($value) || is_float($value) || is_int($value) || is_string($value) || is_null($value)) {
					$this->like[] = sprintf("%s LIKE '%s%s'", $key, $value, '%');
				}
			}
		}

		return $this;
	}


	/**
	 * @param  ?array<string|int, mixed>  $condition
	 *
	 * @return Sql
	 */
	public function orderBy(?array $condition): Sql
	{
		if(is_null($condition)) {
			$this->orderBy = [];
		}

		if(is_array($condition)) {
			foreach($condition as $key => $value) {
				if(is_bool($value) || is_float($value) || is_int($value) || is_string($value) || is_null($value)) {
					$this->orderBy[] = sprintf("%s %s", $key, $value);
				}
			}
		}

		return $this;
	}


	/**
	 * @param  string|null  $field
	 *
	 * @return mixed
	 */
	public function count(?string $field): mixed
	{
		if($this->where) {
			$this->sql = sprintf(/** @lang text */ "SELECT COUNT(%s) FROM %s WHERE %s", $field, $this->table,
				implode(' AND ', $this->where));
		}

		if(!$this->where) {
			$this->sql = sprintf(/** @lang text */ "SELECT COUNT(%s) FROM %s", $field, $this->table);
		}

		if($this->where && $this->join) {
			$this->sql = sprintf(/** @lang text */ "SELECT COUNT(%s) FROM %s %s WHERE %s", $field, $this->table,
				implode(' ', $this->join),
				implode(' AND ', $this->where),
			);
		}

		$this->query($this->sql)->getData();

		if(is_array($this->data) && $this->data) {
			return current($this->data)["COUNT({$field})"];
		}

		return null;
	}

	/**
	 * @param  bool         $current
	 * @param  string|null  $value
	 *
	 * @return ?array<string|int, mixed>
	 */
	public function getData(bool $current = false, string $value = null): ?array
	{
		if($current && $value && isset($this->data[$value]) && is_array($this->data[$value])) {
			return current($this->data[$value]);
		}

		if(!$current && $value && is_array($this->data) && $this->data) {
			return $this->data[$value] ?? null;
		}

		return $this->data ?? null;
	}

	/**
	 * @param  string  $sql
	 *
	 * @return Sql
	 */
	private function query(string $sql): Sql
	{
		try {
			$stmt = ($this->getDb()->prepare($sql));
			$stmt->execute();

			if($stmt->rowCount() > 0) {
				$this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch(Exception $e) {
			LogError($e);
		}

		return $this;
	}

	/**
	 * @param  int|null  $limit
	 * @param  int|null  $offset
	 *
	 * @return $this
	 */
	public function limit(?int $limit, ?int $offset): Sql
	{
		if(!is_null($limit) && !is_null($offset)) {
			$this->limit[] = sprintf("%s,%s", $limit, $offset);
		}

		if(is_null($limit) || is_null($offset)) {
			$this->limit = null;
		}

		return $this;
	}

	/**
	 * @param  ?array<string|int, mixed>  $condition
	 *
	 * @return Sql
	 */
	public function whereIn(?array $condition): Sql
	{
		if(is_array($condition)) {
			foreach($condition as $key => $value) {
				if(is_bool($value) || is_float($value) || is_int($value) || is_string($value) || is_null($value)) {
					$this->where[] = sprintf("%s = %s", $key, $value);
				}
			}
		}

		return $this;
	}

	/**
	 * @param  string  $table
	 * @param  string  $key
	 * @param  string  $ref
	 * @param  string  $operator
	 *
	 * @return Sql
	 */
	public function innerJoin(string $table, string $key, string $ref, string $operator = '='): Sql
	{
		$this->join[] = sprintf('INNER JOIN %s ON %s.%s%s%s',
			$table, $table, $key, $operator, $ref
		);

		return $this;
	}


	/**
	 * @param  bool  $current
	 * @param  bool  $countRows
	 * @param  bool  $pagination
	 * @param  bool  $data
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function execute(bool $current = false, bool $countRows = false, bool $pagination = false, bool $data = true): mixed
	{
		$stmt = $this->getDb()->query($this->__toString());
		$this->pagination = $pagination;
		if($pagination && isset($this->limit)) {
			return $this->paginationQuery($this->foundRows());
		}

		if($stmt instanceof PDOStatement && $stmt->rowCount() > 0) {
			if($data) {
				$this->data['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}

			if(!$data) {
				$this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}

			if($current && $data) {
				$this->data['data'] = current($this->data['data']);
			}

			if($current && !$data) {
				$this->data = current($this->data);
			}

			if($countRows) {
				$this->rowsCount = $stmt->rowCount();
			}

			return $this->data;
		}

		return [];
	}

	/**
	 * @param  int  $allRegisters
	 *
	 * @return array<string|int, mixed>
	 */
	private function paginationQuery(int $allRegisters): array
	{
		$pagination = $this->createPagination($allRegisters);
		$this->limit = [$pagination->calculate()->getLimit()];
		$pagination->setData($this->query($this->__toString())->getData());

		return $pagination->getPagination();
	}


	/**
	 * @param  int  $allRegisters
	 *
	 * @return Pagination
	 */
	public function createPagination(int $allRegisters): Pagination
	{
		$value = $this->limit[0] ?? '0,10';

		if(is_string($value)) {
			$queryLimit = explode(',', $value);
		}
		$currentPage = (int)($queryLimit[0] ?? 1);
		$limitPages = (int)($queryLimit[1] ?? 5);

		return new Pagination($allRegisters, $currentPage, $limitPages);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		$distinctClause = ($this->pagination ? 'SQL_CALC_FOUND_ROWS' : '');

		$sqlParts = [
			'SELECT',
			$distinctClause,
			implode(', ', $this->fields),
			'FROM',
			implode(', ', $this->from)
		];

		if($this->join) {
			$sqlParts[] = implode(' ', $this->join);
		}

		if($this->where) {
			$sqlParts[] = 'WHERE';
			$sqlParts[] = implode(' AND ', $this->where);
		}

		if($this->like) {
			$sqlParts[] = 'AND';
			$sqlParts[] = implode(' AND ', $this->like);
		}

		if($this->orderBy) {
			$sqlParts[] = 'ORDER BY';
			$sqlParts[] = implode(' ', $this->orderBy);
		}

		if($this->limit) {
			$sqlParts[] = 'LIMIT';
			$sqlParts[] = implode(', ', $this->limit);
		}

		return implode(' ', $sqlParts);
	}


	/**
	 * @param  bool  $pagination
	 *
	 * @return Sql
	 */
	public function setPagination(bool $pagination): Sql
	{
		$this->pagination = $pagination;
		return $this;
	}


	/**
	 * @return int
	 * @throws Exception
	 */
	protected function foundRows(): int
	{
		$sql = 'SELECT FOUND_ROWS()';
		$stmt = $this->getDb()->query($sql);
		$total = 0;

		if($stmt instanceof PDOStatement && $stmt->rowCount() > $total) {
			$total = $stmt->fetch(PDO::FETCH_COLUMN);
			if(!is_int($total)) {
				return 0;
			}
		}

		return $total;
	}

	/**
	 * @param  bool  $countRows
	 * @param  bool  $pagination
	 *
	 * @return array<string|int, mixed>|Generator
	 * @throws Exception
	 */
	public function executeLines(bool $countRows = false, bool $pagination = false): array|Generator
	{
		try {
			$stmt = $this->getDb()->query($this->__toString());

			if($pagination && isset($this->limit)) {
				return $this->paginationQuery($this->foundRows());
			}

			if($stmt instanceof PDOStatement && $stmt->rowCount() > 0) {
				if($countRows) {
					$this->rowsCount = $stmt->rowCount();
				}

				while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					yield $row;
				}
			}
		} catch(PDOException $e) {
			LogError($e);
		}

		return [];
	}

	/**
	 * @return int|null
	 */
	public function getRowsCount(): ?int
	{
		return $this->rowsCount ?? null;
	}

}
