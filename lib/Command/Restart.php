<?php

namespace Command;

class Restart extends \Classes\Command\Base
{
	protected $help = 'Reconnect the bot to the network. Does not reload plugins.';
	
	protected $usage = 'restart';

	protected $verify = true;

	protected $numberOfArguments = array(0, -1);

	public function command()
	{
		if(count($this->arguments) == 0)
		{
			$message = 'Restarting... Beep.';
		}
		else
		{
			$message = trim(preg_replace('/\s\s+/', ' ',  implode(' ', $this->arguments)));
		}
	
		$this->connection->sendData('QUIT :' . $message);
		global $argv;
		$_ = $_SERVER['_'];
		pcntl_exec($_, $argv);
	}
}
