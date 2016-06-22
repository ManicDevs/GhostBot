<?php

namespace Command;

class Poke extends \Classes\Command\Base
{
	
	protected $help = 'Poke somebody.';
	
	protected $usage = 'poke [user]|[#channel] [user]';

	protected $numberOfArguments = array(1, 2);

	public function command()
	{
		if(!isset($this->arguments[1]))
		{
			$this->connection->sendData(
				'PRIVMSG ' . $this->arguments[0] .
				' :'. chr(1). 'ACTION pokes '. trim($this->arguments[0]). chr(1)
			);			
			return;
		}
		
		$this->connection->sendData(
			'PRIVMSG ' . $this->arguments[0] .
			' :'. chr(1). 'ACTION pokes '. trim($this->arguments[1]). chr(1)
		);
	}
}
