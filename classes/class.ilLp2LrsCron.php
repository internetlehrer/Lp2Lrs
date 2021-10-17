<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLp2LrsCron
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsCron extends ilCronJob
{
	const JOB_ID = 'Lp2Lrs';
	
	/**
	 * @var ilCmiXapiLrsType|ilXapiCmi5Type|null
	 */
	protected $lrsType;
	
	public function __construct()
	{
		$settings = new ilSetting(self::JOB_ID);
		$lrsTypeId = $settings->get('lrs_type_id', 0);
		
		if( $lrsTypeId )
		{
            $getFromPlugin = false;
            /*
		    if( (bool)($getFromPlugin = ilPluginAdmin::isPluginActive("xxcf")) ) {
		        ilPluginAdmin::includeClass(IL_COMP_SERVICE, 'Repository', 'robj', 'XapiCmi5', 'class.ilXapiCmi5Type.php');
            }
            */
			$this->lrsType = $getFromPlugin ? new ilXapiCmi5Type($lrsTypeId) : new ilCmiXapiLrsType($lrsTypeId);
		}
		else
		{
			$this->lrsType = null;
		}
	}
	
	public function getId()
	{
		return self::JOB_ID;
	}
	
	/**
	 * @@inheritdoc
	 */
	public function hasAutoActivation()
	{
		return false;
	}
	
	/**
	 * @@inheritdoc
	 */
	public function hasFlexibleSchedule()
	{
		return true;
	}
	
	/**
	 * @@inheritdoc
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}
	
	/**
	 * @@inheritdoc
	 */
	function getDefaultScheduleValue()
	{
		return 1;
	}
	
	protected function hasLrsType()
	{
		return $this->getLrsType() !== null;
	}
	
	protected function getLrsType()
	{
		return $this->lrsType;
	}
	
	/**
	 * @@inheritdoc
	 */
	public function run()
	{
		$cronResult = new ilCronJobResult();
		
		if( !$this->hasLrsType() )
		{
			ilLoggerFactory::getRootLogger()->alert('No lrs type configured!');
			$cronResult->setStatus(ilCronJobResult::STATUS_INVALID_CONFIGURATION);
			return $cronResult;
		}
		
		$lpChangesQueue = new ilLp2LrsChangesQueue();
		$lpChangesQueue->load();
		
		$statementListBuilder = new ilLp2LrsXapiStatementListBuilder(ilLoggerFactory::getRootLogger(), $this->getLrsType());
		$statementList = $statementListBuilder->buildStatementsList($lpChangesQueue);
		
		$lrsRequest = new ilLp2LrsXapiRequest(
			ilLoggerFactory::getRootLogger(),
			$this->getLrsType()->getLrsEndpointStatementsLink(),
			$this->getLrsType()->getLrsKey(),
			$this->getLrsType()->getLrsSecret()
		);
		
		if( $lrsRequest->send($statementList) )
		{
			if( $lpChangesQueue->hasEntries() )
			{
				$lpChangesQueue->delete();
				$cronResult->setStatus(ilCronJobResult::STATUS_OK);
			}
			else
			{
				$cronResult->setStatus(ilCronJobResult::STATUS_NO_ACTION);
			}
		}
		else
		{
			$cronResult->setStatus(ilCronJobResult::STATUS_FAIL);
		}
		
		return $cronResult;
	}
	
}
