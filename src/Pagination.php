<?php


namespace Omegaalfa\Database;


/**
 * Class Pagination
 *
 * @package src\database\orm
 */
class Pagination
{

	/**
	 * @var int
	 */
	private int $limitPages;

	/**
	 * @var int
	 */
	private int $total;

	/**
	 * @var int
	 */
	private int $pages;

	/**
	 * @var int
	 */
	private int $offset;

	/**
	 * @var int
	 */
	private int $currentPage;

	/**
	 * @var array
	 */
	private array $data;


	/**
	 * Pagination constructor.
	 *
	 * @param  int  $allRegisters
	 * @param  int  $currentPage
	 * @param  int  $limitPages
	 */
	public function __construct(int $allRegisters, int $currentPage = 1, int $limitPages = 5)
	{
		$this->limitPages = $limitPages;
		$this->total = $allRegisters;
		$this->currentPage = (is_int($currentPage) and $currentPage > 0) ? $currentPage : 1;
	}

	/**
	 * @param  int  $limitPages
	 *
	 * @return $this
	 */
	public function setLimitPages(int $limitPages): self
	{
		$this->limitPages = $limitPages;

		return $this;
	}


	/**
	 * @param  int  $allRegisters
	 *
	 * @return $this
	 */
	public function setTotals(int $allRegisters): self
	{
		$this->total = $allRegisters;

		return $this;
	}

	/**
	 * @param  int  $pages
	 *
	 * @return $this
	 */
	public function setPages(int $pages): self
	{
		$this->pages = $pages;

		return $this;
	}


	/**
	 * @param  int  $currentPage
	 *
	 * @return $this
	 */
	public function setCurrentPage(int $currentPage): self
	{
		$this->currentPage = $currentPage;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimitPages(): int
	{
		return $this->limitPages;
	}

	/**
	 * @return int
	 */
	public function getTotals(): int
	{
		return $this->total;
	}

	/**
	 * @return int
	 */
	public function getPages(): int
	{
		return $this->pages;
	}

	/**
	 * @return int
	 */
	public function getCurrentPage(): int
	{
		return $this->currentPage;
	}


	/**
	 * @return $this
	 */
	public function calculate(): Pagination
	{
		$this->pages = $this->total > 0 ? ceil($this->total / $this->limitPages) : 1;
		$this->currentPage = $this->currentPage <= $this->pages ? $this->currentPage : $this->pages;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLimit()
	{
		$this->offset = ($this->limitPages * ($this->currentPage - 1));

		return sprintf("%s,%s", $this->offset, $this->limitPages);
	}

	/**
	 * @param  array|null  $data
	 */
	public function setData(?array $data): void
	{
		$this->data = $data ?? [];
	}


	/**
	 * @return array
	 */
	public function getPagination()
	{
		return get_object_vars($this);
	}
}
