<?php

namespace Command;

class Join extends \Classes\Command\Base
{
	protected $help = 'Make the bot join channels.';

	protected $usage = 'join [#channel]|[#channel,#otherchannel:Password]';

	protected $numberOfArguments = 1;

	protected $verify = true;
	
	public function command()
	{
		foreach(explode(',', $this->arguments[0]) as $channelArgs)
		{
			@list($channel, $password) = explode(':', $channelArgs);
			if(strlen($password) > 0)
			{
				$this->connection->sendData("JOIN $channel $password");
			}
			else
			{
				$channels[] = $channel;
			}
		}
		
		if(count($channels) > 0)
		{
			$this->connection->sendData('JOIN ' . implode(',', $channels));
		}
	}
}
