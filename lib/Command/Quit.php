<?php

namespace Command;

class Quit extends \Classes\Command\Base
{
	protected $help = 'Shut the bot down.';

	protected $usage = 'quit';

	protected $verify = true;

	protected $numberOfArguments = array(0, -1);

	public function command()
	{
		if(strlen($this->arguments[0]))
		{
			$message = 'Insert Quit Message Here... Beep.';
		}
		else
		{
			$message = trim(preg_replace('/\s\s+/', ' ',  implode(' ', $this->arguments)));
		}
		
		$this->connection->sendData('QUIT :' . $message);
		exit;
	}
}
