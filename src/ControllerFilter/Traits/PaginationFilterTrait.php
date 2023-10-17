<?php

declare(strict_types=1);

namespace App\ControllerFilter\Traits;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait PaginationFilterTrait
{
	/**
	 * How many items to show per page.
	 *
	 * @var integer
	 */
	#[Assert\Range(min: 1, max: 100)]
	private $perPage = 10;

	/**
	 * The current page number.
	 *
	 * @var integer
	 */
	#[Assert\Range(min: 1)]
	private $page = 1;

	/**
	 * Get how many items to show per page.
	 *
	 * @return  integer
	 */
	public function getPerPage()
	{
		return $this->perPage;
	}

	/**
	 * Set how many items to show per page.
	 *
	 * @param  integer  $perPage  How many items to show per page.
	 *
	 * @return  self
	 */
	public function setPerPage($perPage)
	{
		$this->perPage = $perPage;

		return $this;
	}

	/**
	 * Get the current page number.
	 *
	 * @return  integer
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * Set the current page number.
	 *
	 * @param  integer  $page  The current page number.
	 *
	 * @return  self
	 */
	public function setPage($page)
	{
		$this->page = $page;

		return $this;
	}
}
