<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLp2LrsChangesQueueEntry
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsChangesQueueEntry
{
	/**
	 * @var int
	 */
	protected $queueId;
	
	/**
	 * @var int
	 */
	protected $refId;
	
	/**
	 * @var int
	 */
	protected $objId;
	
	/**
	 * @var int
	 */
	protected $usrId;
	
	/**
	 * @var ilCmiXapiDateTime
	 */
	protected $statusChanged;
	
	/**
	 * @var int
	 */
	protected $status;
	
	/**
	 * @var int|null
	 */
	protected $percentage;
	
	/**
	 * ilLp2LrsChangesQueueEntry constructor.
	 * @param int $queueId
	 * @param $refId
	 * @param int $objId
	 * @param int $usrId
	 * @param ilCmiXapiDateTime $statusChanged
	 * @param int $status
	 * @param $percentage
	 */
	public function __construct(int $queueId, $refId, int $objId, int $usrId, ilCmiXapiDateTime $statusChanged, int $status, $percentage)
	{
		$this->queueId = $queueId;
		$this->refId = $refId;
		$this->objId = $objId;
		$this->usrId = $usrId;
		$this->statusChanged = $statusChanged;
		$this->status = $status;
		$this->percentage = $percentage;
	}
	
	/**
	 * @return int
	 */
	public function getQueueId(): int
	{
		return $this->queueId;
	}
	
	/**
	 * @return int
	 */
	public function getRefId()
	{
		return $this->refId;
	}
	
	/**
	 * @return int
	 */
	public function getObjId(): int
	{
		return $this->objId;
	}
	
	/**
	 * @return int
	 */
	public function getUsrId(): int
	{
		return $this->usrId;
	}
	
	/**
	 * @return ilDateTime
	 */
	public function getStatusChanged(): ilDateTime
	{
		return $this->statusChanged;
	}
	
	/**
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->status;
	}
	
	/**
	 * @return int|null
	 */
	public function getPercentage()
	{
		return $this->percentage;
	}
}
