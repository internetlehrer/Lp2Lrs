<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLp2LrsXapiStatementList
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsXapiStatementList implements JsonSerializable
{
	/**
	 * @var ilLp2LrsXapiStatement[]
	 */
	protected $statements = [];
	
	/**
	 * @param ilLp2LrsXapiStatement $statement
	 */
	public function addStatement(ilLp2LrsXapiStatement $statement)
	{
		$this->statements[] = $statement;
	}
	
	/**
	 * @return ilLp2LrsXapiStatement[]
	 */
	public function getStatements(): array
	{
		return $this->statements;
	}
	
	/**
	 * @return string
	 */
	public function getPostBody()
	{
		if(DEVMODE)
		{
			return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
		}
		
		return json_encode($this->jsonSerialize());
	}
	
	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		$jsonSerializable = [];
		
		foreach($this->statements as $statement)
		{
			$jsonSerializable[] = $statement->jsonSerialize();
		}

		return $jsonSerializable;
	}
}
