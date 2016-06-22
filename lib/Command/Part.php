<?php

namespace Command;

class Part extends \Classes\Command\Base
{
	protected $help = 'Make the bot leave channels.';
	
	protected $usage = 'part [#channel]|[#channel,#otherchannel]';

	protected $numberOfArguments = 1;
	
	protected $verify = true;

	public function command()
	{
		$this->connection->sendData('JOIN ' . implode(',', $this->arguments[0]));
	}
}
