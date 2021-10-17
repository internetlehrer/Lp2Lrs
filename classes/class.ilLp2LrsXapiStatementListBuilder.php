<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLp2LrsXapiStatementListBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsXapiStatementListBuilder
{
	/**
	 * @var ilLogger
	 */
	protected $log;
	
	/**
	 * @var ilCmiXapiLrsType
	 */
	protected $lrsType;

    /**
     * ilLp2LrsXapiStatementListBuilder constructor.
     * @param ilLogger $log
     * @param ilCmiXapiLrsType|ilXapiCmi5Type $lrsType
     */
	public function __construct(ilLogger $log, $lrsType)
	{
		$this->log = $log;
		$this->lrsType = $lrsType;
	}
	
	/**
	 * @param ilLp2LrsChangesQueue $lpChangesQueue
	 * @return ilLp2LrsXapiStatementList
	 */
	public function buildStatementsList(ilLp2LrsChangesQueue $lpChangesQueue)
	{
		$statementsList = new ilLp2LrsXapiStatementList();
		
		foreach($lpChangesQueue as $entry)
		{
			/* @var ilObject $object */
			$object = ilObjectFactory::getInstanceByObjId($entry->getObjId(), false);
			
			if( $entry->getRefId() )
			{
				$object->setRefId($entry->getRefId());
			}
			elseif( in_array($object->getType(), ['crs', 'grp']) )
			{
				$object->setRefId( current(ilObject::_getAllReferences($object->getId())) );
			}
			
			if( $object === null )
			{
				$this->log->warning('unknown object: '.$entry->getObjId());
				continue;
			}

			/* @var ilObjUser $user */
			$user = ilObjectFactory::getInstanceByObjId($entry->getUsrId(), false);
			
			if( $user === null )
			{
				$this->log->warning('unknown user: '.$entry->getUsrId());
				continue;
			}
			
			$statement = new ilLp2LrsXapiStatement(
				$this->lrsType,
				$object,
				$user,
				$entry->getStatusChanged(),
				$entry->getStatus(),
				$entry->getPercentage()
			);
			
			$statementsList->addStatement($statement);
		}
		
		return $statementsList;
	}
}
