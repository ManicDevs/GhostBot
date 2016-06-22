<?php

namespace Command;

class Nick extends \Classes\Command\Base
{
	protected $help = 'Change the bots nick';

	protected $usage = 'nick [nickname]';

	protected $verify = true;

	protected $numberOfArguments = 1;

	public function command()
	{
		$this->connection->sendData('NICK :' . $this->arguments[0]);
	}
}
