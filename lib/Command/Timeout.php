<?php

namespace Command;

class Timeout extends \Classes\Command\Base
{
	protected $help = 'Disconnect the bot for the specified number of seconds.';
    
	protected $usage = 'timeout [seconds]';
	
	protected $numberOfArguments = 1;
	
	protected $verify = true;
	
	public function command()
	{
		$this->connection->sendData('QUIT :I\'ll be back!');
		sleep((int)($this->arguments[0]));
		global $argv;
		$_ = $_SERVER['_'];
		pcntl_exec($_, $argv);
	}
}
