<?php

namespace Listener;

class Remode extends \Classes\Listener\Base
{
	private $apiUri = "";
	
	public function execute($data)
	{
		//$args = $this->getArguments($data);
		//$usersource = explode('!', $args[0]);
		#$this->connection->sendData('SETHOST corp.google.com');
		#$this->connection->sendData('MODE Ghost -x');
		$this->connection->sendData('SAMODE #NCA +v Ghost');
	}
	
	public function getKeywords()
	{
		return array("MODE");
	}
}
