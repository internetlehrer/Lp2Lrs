<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLp2LrsConfig
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class ilLp2LrsConfigGUI extends ilPluginConfigGUI
{
	/**
	 * @var ilLp2LrsPlugin
	 */
	protected $plugin_object;
	
	public function performCommand($cmd)
	{
		$this->{$cmd}();
	}
	
	protected function configure(ilPropertyFormGUI $form = null)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( !count(ilCmiXapiLrsTypeList::getTypesData(false)) ) {
            ilUtil::sendFailure($DIC->language()->txt('rep_robj_xxcf_type_not_set'));
            return;
        }

		if( $form === null )
		{
			$form = $this->buildForm();
		}
		
		$DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function save()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = $this->buildForm();
		
		if( !$form->checkInput() )
		{
			return $this->configure($form);
		}
		
		$this->writeLrsTypeId($form->getInput('lrs_type_id'));
		
		$DIC->ctrl()->redirect($this, 'configure');
	}
	
	protected function buildForm(): ilPropertyFormGUI
    {
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = new ilPropertyFormGUI();
		
		$form->setFormAction($DIC->ctrl()->getFormAction($this));
		$form->addCommandButton('save', $DIC->language()->txt('save'));
		
		$form->setTitle('Configuration');
		
		$item = new ilRadioGroupInputGUI('LRS-Type', 'lrs_type_id');
		$item->setRequired(true);
		
		#$types = ilPluginAdmin::isPluginActive("xxcf") ? ilLp2LrsChangesQueue::getTypesData() : ilCmiXapiLrsTypeList::getTypesData(false);
        $types = ilCmiXapiLrsTypeList::getTypesData(false);
		
		foreach ($types as $type)
		{
			$option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
			$item->addOption($option);
		}
		
		$item->setValue($this->readLrsTypeId());
		
		$form->addItem($item);
		
		return $form;
	}
	
	protected function readLrsTypeId()
	{
		$settings = new ilSetting(ilLp2LrsCron::JOB_ID);
		return $settings->get('lrs_type_id', 0);
	}
	
	protected function writeLrsTypeId($lrsTypeId)
	{
		$settings = new ilSetting(ilLp2LrsCron::JOB_ID);
		$settings->set('lrs_type_id', $lrsTypeId);
	}

}
