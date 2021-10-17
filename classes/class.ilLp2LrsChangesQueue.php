<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;


/**
 * Class ilLp2LrsQueue
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsChangesQueue implements Iterator
{
    CONST TYPES_DB_XXCF = "xxcf_data_types";

	/**
	 * @var ilLp2LrsChangesQueueEntry[]
	 */
	protected $entries;
	
	public function __construct()
	{
		$this->entries = [];
	}
	
	public function addEntry(ilLp2LrsChangesQueueEntry $entry)
	{
		$this->entries[] = $entry;
	}
	
	public function hasEntries()
	{
		return (bool)count($this->entries);
	}
	
	public function load()
	{
		global $DIC; /* @var Container $DIC */
		
		$res = $DIC->database()->query("
			SELECT *
			FROM lp2lrs_queue_lpchanged
			ORDER BY queue_id ASC
		");
		
		while($row = $DIC->database()->fetchAssoc($res))
		{
			$entry = new ilLp2LrsChangesQueueEntry(
				$row['queue_id'],
				$row['ref_id'],
				$row['obj_id'],
				$row['usr_id'],
				new ilCmiXapiDateTime($row['status_changed'], IL_CAL_DATETIME),
				$row['status'],
				$row['percentage']
			);
			
			$this->addEntry($entry);
		}
	}
	
	public function delete()
	{
		global $DIC; /* @var Container $DIC */
		
		$IN_queueIds = $DIC->database()->in('queue_id', $this->getCurrentQueueIds(), false, 'integer');
		
		$DIC->database()->manipulate("DELETE FROM lp2lrs_queue_lpchanged WHERE $IN_queueIds");
	}
	
	/**
	 * @return array
	 */
	public function getCurrentQueueIds()
	{
		$queueIds = [];
		
		foreach($this as $entry)
		{
			$queueIds[] = $entry->getQueueId();
		}
		
		return $queueIds;
	}
	
	/**
	 * @return ilLp2LrsChangesQueueEntry
	 */
	public function current()
	{
		return current($this->entries);
	}
	
	/**
	 * @return ilLp2LrsChangesQueueEntry
	 */
	public function next()
	{
		return next($this->entries);
	}
	
	/**
	 * @return int|mixed|string|null
	 */
	public function key()
	{
		return key($this->entries);
	}
	
	/**
	 * @return bool
	 */
	public function valid()
	{
		return $this->key() !== null;
	}
	
	/**
	 * @return ilLp2LrsChangesQueueEntry
	 */
	public function rewind()
	{
		return reset($this->entries);
	}


    public static function getTypesData(?int $a_availability = null): array
    {
		global $DIC;
        $ilDB = $DIC->database();

        $query = "SELECT * FROM " . self::TYPES_DB_XXCF;
        if (isset($a_availability)) {
            $query .= " WHERE availability=" . $ilDB->quote($a_availability, 'integer');
        }
        $query .= " ORDER BY title";
        $res = $ilDB->query($query);

        $data = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $row['lrs_type_id'] = $row['type_id']; // indeed it is an lrs-type-id

            $data[] = $row;
        }
        return $data;
    }
	
	
}
