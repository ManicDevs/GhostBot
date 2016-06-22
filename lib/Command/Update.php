<?php

namespace Command;

class Update extends \Classes\Command\Base
{
	protected $help = 'Updates the Bot to the Latest Version';
	
	protected $usage = 'update';
	
	protected $verify = true;
	
	protected $numberOfArguments = 0;
	
	public function command()
	{
		$this->bot->log('Checking for Bot Update');
		$this->say('Update: Checking for Bot Updates');
		$null = shell_exec('git stash 2>&1');
		$update = shell_exec('git pull --progress 2>&1');

		if(preg_match("/up-to-date/i", $update))
		{
			$this->bot->log('Bot is Already Up to Date');
			$this->say('Update: Bot is Already Up to Date');
		}
		elseif(preg_match("/error/i", $update))
		{
			$this->bot->log('Bot Updating Ran into an Error');
			$this->say('Update: There was an Error Updating the Bot');
			$this->say($update);
		}
		else
		{
			$this->bot->log('Bot Updated Successfully');
			$this->say('Update: Bot Was Updated Successfully');
			$this->connection->sendData('QUIT :Updating...');
			global $argv;
			$_ = $_SERVER['_'];
			pcntl_exec($_, $argv);
		}
	}
}
