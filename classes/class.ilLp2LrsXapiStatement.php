<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class illp2lrsPlugin
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsXapiStatement implements JsonSerializable
{
	protected static $XAPI_VERBS = [
		'http://adlnet.gov/expapi/verbs/failed' => 'failed',
		'http://adlnet.gov/expapi/verbs/completed' => 'completed',
		'http://adlnet.gov/expapi/verbs/attempted' => 'attempted'
	];
	
	protected static $VERBS_BY_LP = [
		ilLPStatus::LP_STATUS_FAILED_NUM => 'http://adlnet.gov/expapi/verbs/failed',
		ilLPStatus::LP_STATUS_COMPLETED_NUM => 'http://adlnet.gov/expapi/verbs/completed',
		ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => 'http://adlnet.gov/expapi/verbs/attempted',
		ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => 'http://adlnet.gov/expapi/verbs/attempted'
	];
	
	protected static $RELEVANT_PARENTS = ['cat', 'crs', 'grp', 'root'];
	
	const CATEGORY_DEFINITION_TYPE_TAG = 'http://id.tincanapi.com/activitytype/tag';
	
	const DEFAULT_LOCALE = 'en-US';
	
	/**
	 * @var ilCmiXapiLrsType
	 */
	protected $lrsType;
	
	/**
	 * @var ilObject
	 */
	protected $object;
	
	/**
	 * @var ilObjUser
	 */
	protected $user;
	
	/**
	 * @var ilCmiXapiDateTime
	 */
	protected $lpStatusChanged;
	
	/**
	 * @var int
	 */
	protected $lpStatus;
	
	/**
	 * @var int
	 */
	protected $percentage;
	
	/**
	 * ilLp2LrsXapiStatement constructor.
	 * @param ilCmiXapiLrsType|ilXapiCmi5Type  $lrsType
	 * @param ilObject $object
	 * @param ilObjUser $user
	 * @param ilCmiXapiDateTime|ilXapiCmi5DateTime $lpStatusChanged
	 * @param int $lpStatus
	 * @param null|int $percentage
	 */
	public function __construct(
		$lrsType,
		ilObject $object,
		ilObjUser $user,
		$lpStatusChanged,
		int $lpStatus,
		?int $percentage
	)
	{
		$this->lrsType = $lrsType;
		$this->object = $object;
		$this->user = $user;
		$this->lpStatusChanged = $lpStatusChanged;
		$this->lpStatus = $lpStatus;
		$this->percentage = $percentage;
	}
	
	/**
	 * @return string
	 */
	protected function buildTimestamp()
	{
		return $this->lpStatusChanged->toXapiTimestamp();
	}
	
	/**
	 * @return array
	 */
	protected function buildActor()
	{
		if(isset(array_flip(get_class_methods($this->lrsType))['getPrivacyName'])) //ILIAS 7
		{
			$identMode = $this->lrsType->getPrivacyIdent();
			$nameMode = $this->lrsType->getPrivacyName();
		} else {
			$identMode = $this->lrsType->getUserIdent();
			$nameMode = $this->lrsType->getUserName();
		}
		return [
			'objectType' => 'Agent',
        	'mbox' => 'mailto:'.ilCmiXapiUser::getIdent($identMode ,$this->user),
        	'name' => ilCmiXapiUser::getName($nameMode ,$this->user)
		];
	}
	
	/**
	 * @return array
	 */
	protected function buildVerb()
	{
		return [
			'id' => $this->getVerbId(),
			'display' => [ $this->getLocale() => $this->getVerbName() ]
		];
	}
	
	protected function hasResult()
	{
		return $this->percentage !== null;
	}
	
	/**
	 * @return array
	 */
	protected function buildResult()
	{
		$score = $this->getScore();
		
		return [
			'score' => [
				'scaled' => $score,
				'raw' => $score,
				'min' => 0,
				'max' => 1,
			]
		];
	}
	
	/**
	 * @return array
	 */
	protected function buildObject()
	{
		return $this->getObjectProperties($this->object);
	}
	
	/**
	 * @return array
	 */
	protected function buildContext()
	{
		$context = [
			'contextActivities' => []
		];
		
		$parent = $this->getContextParent($this->object);
		
		if( $parent )
		{
			$context['contextActivities']['parent'] = $this->getObjectProperties($parent);
		}

		$categories = $this->getObjectCategories($this->object);
		if( $categories )
		{
            $context['contextActivities']['category'] = $categories;
        }
		
		return $context;
	}
	
	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		$statement = [];
		
		$statement['timestamp'] = $this->buildTimestamp();
		
		$statement['actor'] = $this->buildActor();
		
		$statement['verb'] = $this->buildVerb();
		
		if( $this->hasResult() )
		{
			$statement['result'] = $this->buildResult();
		}
		
		$statement['object'] = $this->buildObject();
		
		$statement['context'] = $this->buildContext();
		
		return $statement;
	}
	
	/**
	 * @return string
	 */
	protected function getVerbId()
	{
		return self::$VERBS_BY_LP[$this->lpStatus];
	}
	
	/**
	 * @return string
	 */
	protected function getVerbName()
	{
		return self::$XAPI_VERBS[$this->getVerbId()];
	}
	
	/**
	 * @return float
	 */
	protected function getScore()
	{
		return $this->percentage / 100;
	}
	
	/**
	 * @return string
	 */
	protected function getObjectType(ilObject $object)
	{
		switch( $object->getType() )
		{
			case 'cat':
			case 'crs':
			case 'grp':
			case 'fold':
            case 'root':
				return 'Group';
			
			default:
				return 'Activity';
		}
	}
	
	/**
	 * @return string
	 */
	protected function getObjectId(ilObject $object)
	{
		switch( $object->getType() )
		{
			case 'cmix':
				
				/* @var ilObjCmiXapi $object */
				
				if( strlen($object->getActivityId()) )
				{
					return $object->getActivityId();
				}
				else
				{
					return $this->getFallbackObjectId($object);
				}
				
			case 'lti':
				
				/* @var ilObjLTIConsumer $object */

				if( $object->getUseXapi() && strlen($object->getActivityId()) )
				{
					return $object->getActivityId();
				}
				else
				{
					return $this->getFallbackObjectId($object);
				}
				
			default:
				
				return $this->getFallbackObjectId($object);
		}
	}
	
	/**
	 * @return string
	 */
	protected function getFallbackObjectId(ilObject $object)
	{
		$settings = new ilSetting('cmix');
		$iliasUid = $settings->get('ilias_uuid');
		
		return 'http://ilias.local/'.$iliasUid.'/'.$object->getId();
	}
	
	/**
	 * @return string
	 */
	protected function getObjectDefinitionType(ilObject $object)
	{
		switch($object->getType())
		{
			case 'cat':
				
				return 'http://id.tincanapi.com/activitytype/category';
				
			case 'crs':
			case 'grp':
				
				return 'http://adlnet.gov/expapi/activities/course';
		}
		
		return 'http://adlnet.gov/expapi/activities/module';
	}
	
	/**
	 * @return string
	 */
	protected function getObjectMoreInfo(ilObject $object)
	{
		return ilLink::_getLink($object->getRefId(), $object->getType());
	}
	
	protected function getObjectProperties(ilObject $object)
	{
		$objectProperties = [
			'id' => $this->getObjectId($object),
                'definition' => [
                    'name' => [$this->getLocale() => $object->getTitle()],
                    'type' => $this->getObjectDefinitionType($object)
                ]
		];
        if( $object->getDescription() != '')
        {
            $objectProperties['definition']['description'] = [$this->getLocale() => $object->getDescription()];
        }

		if( $object->getRefId() )
		{
			$objectProperties['definition']['moreInfo'] = $this->getObjectMoreInfo($object);
		}
		
		return $objectProperties;
	}
	
	/**
	 * @param ilObject $object
	 * @return bool|object|null
	 */
	protected function getContextParent(ilObject $object)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( !$object->getRefId() )
		{
			return null;
		}

		$parents = self::$RELEVANT_PARENTS;
		if( $object->getType() == 'crs' )
        {
            $parents = ['cat', 'root'];
        }

		$pathNodes = array_reverse($DIC->repositoryTree()->getNodePath($object->getRefId()));
		
		foreach($pathNodes as $nodeData)
		{
			if( !in_array($nodeData['type'], $parents) )
			{
				continue;
			}
			
			return ilObjectFactory::getInstanceByObjId($nodeData['obj_id'], false);
		}
		
		return null;
	}
	
	/**
	 * @param ilObject $object
	 * @return array
	 */
	protected function getObjectCategories(ilObject $object)
	{
		$categories = [];
		
		foreach($this->getKeywords($object) as $keyword)
		{
			$categories[] = [
				'id' => 'http://ilias.local/keyword/'.rawurlencode($keyword),
				'definition' => [
				    'name' => [$this->getLocale() => $keyword],
					'type' => self::CATEGORY_DEFINITION_TYPE_TAG
				]
			];
		}
		
		return $categories;
	}
	
	/**
	 * @param ilObject $object
	 * @return array
	 */
	protected function getKeywords(ilObject $object)
	{
		$keywords = [];
		
		$metadata = new ilMD($object->getId(), $object->getId(), $object->getType());
		
		if( !$metadata->getGeneral() )
		{
			ilLoggerFactory::getRootLogger()->debug(
				'No keywords found for object '.$object->getType().$object->getId()
			);
			
			return $keywords;
		}
		
		foreach($metadata->getGeneral()->getKeywordIds() as $keywordId)
		{
		    if ($metadata->getGeneral()->getKeyword($keywordId)->getKeyword() != "") {
                $keywords[] = $metadata->getGeneral()->getKeyword($keywordId)->getKeyword();
            }
		}
		
		ilLoggerFactory::getRootLogger()->debug(
			'Found keywords for object '.$object->getType().$object->getId()."\n".implode(',', $keywords)
		);
		
		
		return $keywords;
	}
	
	protected function getLocale()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$ilLocale = $DIC->settings()->get('locale', '');
		
		if( strlen($ilLocale) )
		{
			return str_replace('_', '-', $ilLocale);
		}
		
		return self::DEFAULT_LOCALE;
	}
}
