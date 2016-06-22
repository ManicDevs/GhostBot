<?php

namespace Listener;

class Statspopulator extends \Classes\Listener\Base
{
	private $apiUri = "";

	public function execute($data)
	{
		if(!file_exists('lib/Command/Statsdata/'))
		{
			mkdir('lib/Command/Statsdata/');
		}
		
		if(!file_exists('lib/Command/Statsdata/words.db'))
		{
			touch('lib/Command/Statsdata/words.db');
			file_put_contents('lib/Command/Statsdata/words.db', serialize(array()));
		}
		
		if(!file_exists('lib/Command/Statsdata/lines.db'))
		{
			touch('lib/Command/Statsdata/lines.db');
			file_put_contents('lib/Command/Statsdata/lines.db', serialize(array()));
		}

		$words = unserialize(file_get_contents('lib/Command/Statsdata/words.db'));
		$lines = unserialize(file_get_contents('lib/Command/Statsdata/lines.db'));
		
		$args = $this->getArguments($data);
		$usersource = explode('!', $args[0]);
		$usersource = strtolower(substr($usersource[0], 1));
		$channel = $args[2];
		$message = $args;
		array_splice($message, 0, 3);
		$message = substr(implode(' ', $message), 1);
		
		if($usersource !== 'guardian' || $usersource !== 'hackbot')
		{
			if(!isset($words[$channel]) || !isset($words[$channel][$usersource]))
			{
				$words[$channel][$usersource] = count(explode(' ', $message));
				$lines[$channel][$usersource] = 1;
			}
			else
			{
				$words[$channel][$usersource] += count(explode(' ', $message));
				$lines[$channel][$usersource] ++;
			}
		}
		file_put_contents('lib/Command/Statsdata/words.db', serialize($words));
		file_put_contents('lib/Command/Statsdata/lines.db', serialize($lines));
	}
	
	public function getKeywords()
	{
		return array("PRIVMSG");
	}
}
