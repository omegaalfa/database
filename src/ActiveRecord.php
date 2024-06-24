<?php

declare(strict_types = 1);

namespace Omegaalfa\Database;


use Exception;


class ActiveRecord extends ConnectDB
{
	use OperationsBaseTrait;

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
		protected string $dbConnect,
		protected string $dbHost,
		protected string $dbPort,
		protected string $dbase,
		protected string $dbUsername,
		protected string $dbPassword,
		protected string $dbCharset
	) {
		parent::__construct(
			$dbConnect,
			$dbHost,
			$dbPort,
			$dbase,
			$dbUsername,
			$dbPassword,
			$dbCharset,
		);
	}

	/**
	 * @return Sql
	 * @throws Exception
	 */
	protected function sql(): Sql
	{
		return new Sql(
			$this->dbConnect,
			$this->dbHost,
			$this->dbPort,
			$this->dbase,
			$this->dbUsername,
			$this->dbPassword,
			$this->dbCharset
		);
	}
}
