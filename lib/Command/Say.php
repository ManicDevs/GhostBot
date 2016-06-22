<?php

namespace Command;

class Say extends \Classes\Command\Base
{
	protected $help = 'Make the bot say something in a channel or to a user.';

	protected $usage = 'say [#channel|username] whatever you want to say';
	
	protected $verify = true;
	
	protected $numberOfArguments = -1;
	
	public function command()
	{
		if(!strlen($this->arguments[0]) || !strlen($this->arguments[1]))
		{
			$this->say($this->usage);
			return;
		}
		
		$this->connection->sendData(
			'PRIVMSG ' . $this->arguments[0] .
			' :'. trim(implode( ' ', array_slice( $this->arguments, 1 ) ))
		);
	}
}
