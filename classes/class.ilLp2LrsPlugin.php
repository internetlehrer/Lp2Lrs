<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class illp2lrsPlugin
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsPlugin extends ilCronHookPlugin {

	const PLUGIN_ID = "xlpc";
	const PLUGIN_NAME = "Lp2Lrs";
	const PLUGIN_CLASS_NAME = ilLp2LrsPlugin::class;

    /** @var Container|null $dic */
    private $dic = null;

    /** @var bool $preventHandler */
    public $preventHandler = false;

    /** @var stdClass $trackingData */
    private $trackingData;

    /** @var array $storedParams */
    private $storedParams = [];




    /**
	 * @inheritdoc
	 */
	public function __construct() {
		parent::__construct();
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function getCronJobInstances() {
		return [
			new ilLp2LrsCron()
		];
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function getCronJobInstance($a_job_id)
	{
		switch ($a_job_id)
		{
			case ilLp2LrsCron::JOB_ID:
				return new ilLp2LrsCron();

			default:
				return null;
		}
	}


	/**
	 * @inheritdoc
	 */
	protected function deleteData()
	{
		// Nothing to delete
	}

    /**
     * @param string $component
     * @param string $event
     * @param array $parameters
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function handleEvent(string $component, string $event, array $parameters) {
        global $DIC; /** @var Container $DIC */
        if( is_null($this->dic)) {
            $this->dic = $DIC;
        }

        $tree = [];
        $this->trackingData = new stdClass();

        if (!$this->preventHandler && $component === 'Services/Tracking' && $event === 'updateStatus') {

            switch (true) {
                case $this->preventHandler:
                    #$this->log('preventHandler')->writeLog();
                    break;
                default: # handle event
                    # GET TREE NODES AND ITS PARAMETERS
                    if (!isset($parameters['ref_id']) && isset($parameters['obj_id'])) {
                        $parameters['ref_id'] = ilObjectFactory::getInstanceByObjId($parameters['obj_id'])->ref_id;
                    }
                    if (!isset($parameters['ref_id']) && isset($parameters['obj_id'])) {
                        $this->dic->logger()->root()->info('RefId was not set by instantiating objId');
                        $refs = ilObject::_getAllReferences($parameters['obj_id']);
                        $parameters['ref_id'] = array_pop($refs);;
                    }
                    if (!isset($parameters['ref_id'])) {
                        break;
                    }

                    $tree = array_reverse($DIC->repositoryTree()->getPathFull($parameters['ref_id']));
                    $keys = array_keys($tree);

                    if( ilPluginAdmin::isPluginActive("xlpp") ) {
                        $checkPrivacyForRefId = 0;
                        foreach( $tree as $key => $node ) {
                            if ($node['type'] === 'crs') {
                                $checkPrivacyForRefId = $node['ref_id'];
                                // var_dump($node);
                                #exit;
                                break;  // foreach
                            }
                        }

                        /** @var ilLp2LrsPrivacyPlugin $checkPrivacyPluginObj */
                        $checkPrivacyPluginObj = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'Lp2LrsPrivacy');
                        if( (bool)$checkPrivacyForRefId && !$checkPrivacyPluginObj->getConfig()->getCheck('lp2lrscy_' . $checkPrivacyForRefId . '_' . $parameters['usr_id'] ) ) {
                            break; // switch default
                        }
                    }

                    $parameters['component'] = $component;
                    $parameters['event'] = $event;
					
					$timestamp = date("Y-m-d H:i:s"); //hätt gern ms
                    $notifyParam = [
                        'queue_id' => array('integer', $this->dic->database()->nextId('lp2lrs_queue_lpchanged')),
                        'ref_id' => array('integer', $parameters['ref_id']),
                        'obj_id' => array('integer', $parameters['obj_id']),
                        'usr_id' => array('integer', $parameters['usr_id']),
                        'status' => array('integer', $parameters['status']),
                        'status_changed' => array('timestamp', $timestamp)
                    ];
                    if (isset($parameters['percentage']) && $parameters['percentage']!='') {
                        $notifyParam['percentage'] = array('integer', $parameters['percentage']);
                    }
					$newParams = [$parameters['obj_id'], $parameters['usr_id'], $parameters['status'], $timestamp];
					if ($this->storedParams != $newParams) {
						$this->dic->database()->insert('lp2lrs_queue_lpchanged', $notifyParam);
					}
					$this->storedParams = $newParams;
                    break; # EOF switch default: handle event
            } # EOF switch ... default: handle event
        }
    }
	
	protected function afterUninstall()
    {
		global $DIC;
        $ilDB = $DIC->database();

        if( $ilDB->tableExists('lp2lrs_queue_lpchanged') ) {
            $ilDB->dropTable('lp2lrs_queue_lpchanged');
        }
        if( $ilDB->tableExists('lp2lrs_queue_lpchanged_seq') ) {
            $ilDB->dropTable('lp2lrs_queue_lpchanged_seq');
        }
    }
}
