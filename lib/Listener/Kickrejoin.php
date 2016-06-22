<?php

namespace Listener;

class Kickrejoin extends \Classes\Listener\Base
{
	private $apiUri = "";
	
	public function execute($data)
	{
		$args = $this->getArguments($data);
		//$usersource = explode('!', $args[0]);
		$this->connection->sendData(sprintf('JOIN %s', $args[2]));
		//$this->say(sprintf('That wasn\'t nice... %s!', substr($usersource[0], 1)), $args[2]);
	}
	
	public function getKeywords()
	{
		return array("KICK");
	}
}
