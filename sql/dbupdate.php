<#1>
<?php
if(!$ilDB->tableExists('lp2lrs_queue_lpchanged'))
{
	$fields = array (
		"queue_id" => array (
			"notnull" => true
			,"length" => 8
			,"default" => "0"
			,"type" => "integer"
		)
	    ,"ref_id" => array (
			"notnull" => false
			,"length" => 4
			,"default" => null
			,"type" => "integer"
		)
	    ,"obj_id" => array (
			"notnull" => true
			,"length" => 4
			,"default" => "0"
			,"type" => "integer"
		)
		,"usr_id" => array (
			"notnull" => true
			,"length" => 4
			,"default" => "0"
			,"type" => "integer"
		)
		,"status" => array (
			"notnull" => true
			,"length" => 1
			,"default" => "0"
			,"type" => "integer"
		)
		,"status_changed" => array (
			"notnull" => false
			,"type" => "timestamp"
		)
		,"percentage" => array (
			"notnull" => false
			,"length" => 1
			,"type" => "integer"
		)
	);
	$ilDB->createTable("lp2lrs_queue_lpchanged", $fields);
	$ilDB->addPrimaryKey("lp2lrs_queue_lpchanged", array("queue_id"));
	$ilDB->createSequence("lp2lrs_queue_lpchanged");
}
?>
