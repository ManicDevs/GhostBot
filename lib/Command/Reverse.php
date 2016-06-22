<?php

namespace Command;

class Reverse extends \Classes\Command\Base
{
	protected $help = 'Reverse a message';

	protected $usage = 'reverse [message]';

	protected $verify = false;

	protected $numberOfArguments = -1;

	public function command()
	{
		$this->say(strrev(implode(' ', $this->arguments)));
	}
}
