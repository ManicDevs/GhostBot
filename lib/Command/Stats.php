<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Stats extends \Classes\Command\Base
{
	protected $help = 'Display statistical information, for total words, lines and most activeness.';
	
	protected $usage = 'Stats [mostactive|count *|[nick]]';
	
	protected $numberOfArguments = array(0, 1, 2);
	
	public function command()
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
		
		switch(strtoupper($this->arguments[0]))
		{
			default:
				$this->say($this->usage);
			break;
			
			case 'MOSTACTIVE':
				$this->say(sprintf('Stats: Most words submitted by %s totalling at %s words.', 
						array_search(max($words[$this->source]), $words[$this->source]), max($words[$this->source])));
				
				$this->say(sprintf('Stats: Most lines submitted by %s totalling at %s lines.', 
						array_search(max($lines[$this->source]), $lines[$this->source]), max($lines[$this->source])));
			break;
			
			case 'COUNT':
				if(!isset($this->arguments[1]) || @$this->arguments[1] === '*')
				{
					$this->connection->sendData(sprintf('NOTICE %s :All Statistics', $this->usersource));
					$this->connection->sendData(sprintf('NOTICE %s : -> Most words submitted by %s totalling at %s words.', 
							$this->usersource, array_search(max($words[$this->source]), $words[$this->source]), max($words[$this->source])));
					$this->connection->sendData(sprintf('NOTICE %s : -> Most lines submitted by %s totalling at %s lines.', 
							$this->usersource, array_search(max($lines[$this->source]), $lines[$this->source]), max($lines[$this->source])));
					foreach($words[$this->source] as $nick => $count)
					{
						$this->connection->sendData(sprintf('NOTICE %s : -> %s has %s words and %s lines.', 
								$this->usersource,
								$nick,
								$count,
								$lines[$this->source][strtolower($nick)]));
					}
				}
				else
				{
					if(isset($words[$this->source][strtolower($this->arguments[1])]) &&
						isset($lines[$this->source][strtolower($this->arguments[1])]))
					{
						$this->say(sprintf('Stats: %s has submitted a total of %s words and %s lines.', 
								$this->arguments[1],
								$words[$this->source][strtolower($this->arguments[1])],
								$lines[$this->source][strtolower($this->arguments[1])]));
					}
					else
					{
						$this->say('Stats: That nick hasn\'t been found!');
					}
				}
			break;
		}
		return;
	}
}
