<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Roulette extends \Classes\Command\Base
{
	protected $help = 'Russian Roulette, a game for the brave using a 6 chamber gun!';
	
	protected $usage = 'Roulette [spin|reload|stats [nick]]';

	protected $numberOfArguments = array(0, 1, 2);

	protected $chamber = 0;
	
	public function command()
	{
		if(!file_exists('lib/Command/Roulettedata/'))
		{
			mkdir('lib/Command/Roulettedata/');
			touch('lib/Command/Roulettedata/stats.db');
			file_put_contents('lib/Command/Roulettedata/stats.db', serialize(array()));
		}
		elseif(!file_exists('lib/Command/Roulettedata/stats.db'))
		{
			touch('lib/Command/Roulettedata/stats.db');
			file_put_contents('lib/Command/Roulettedata/stats.db', serialize(array()));
		}
		else
		{
			$stats = unserialize(file_get_contents('lib/Command/Roulettedata/stats.db'));
		}
		
		$action = strtoupper($this->arguments[0]);
		switch(strtoupper($action))
		{
			default:
				$this->say($this->usage);
			break;
			
			case 'SPIN':
				$spins = array('got lucky!', 'lived to see another day!', 'is safe... for now!', 'BOOM!', 'got a blank!', 'is lucky... for now!');
				$spin = $spins[array_rand($spins)];
				$this->chamber++;
				
				if($this->chamber <= 6)
				{
					if($spin !== 'BOOM!' && $this->chamber !== 6)
					{
						$this->say(sprintf('%s %s', $this->usersource, $spin));
						if(isset($stats[strtolower($this->usersource)]))
						{
							$stats[strtolower($this->usersource)]['survived']++;
						}
						else
						{
							$stats[strtolower($this->usersource)] = array('nick' => $this->usersource, 'survived' => 1, 'killed' => 0);
						}
						file_put_contents('lib/Command/Roulettedata/stats.db', serialize($stats));
						return;
					}
					
					global $config;
					if(isset($config['operline']) && count($config['operline']) == 2)
					{
						$this->connection->sendData(sprintf('KILL %s BOOM... Headshot!', $this->usersource));
					}
					else
					{
						$this->say(sprintf('%s was unlucky, BOOM... Headshot!'));
					}
					$this->chamber = 7;
					if(isset($stats[strtolower($this->usersource)]))
					{
						$stats[strtolower($this->usersource)]['killed']++;
					}
					else
					{
						$stats[strtolower($this->usersource)] = array('nick' => $this->usersource, 'survived' => 0, 'killed' => 1);
					}
					file_put_contents('lib/Command/Roulettedata/stats.db', serialize($stats));
				}
				
				$this->say('Please reload a chamber, and spin!');
			break;
			
			case 'RELOAD':
				if($this->chamber >= 6)
				{
					$this->chamber = 0;
					$this->say('The chamber has been reloaded, now SPIN!');
					return;
				}
				
				$this->say('The chamber is already loaded!');
			break;
			
			case 'STATS':
				if(count($stats) < 1)
				{
					$this->say('There are currently no roulette statistics at the moment!');
					return;
				}			
			
				if(!strlen(@$this->arguments[1]))
				{
					foreach($stats as $stat)
					{
						$this->connection->sendData(sprintf('NOTICE %s :%s has survived %s time(s) and has been killed %s time(s)!', $this->usersource, $stat['nick'], $stat['survived'], $stat['killed']));
					}
					return;
				}
				
				if(!isset($stats[strtolower($this->arguments[1])]))
				{
					$this->say('There are currently no roulette statistics for that nick at the moment!');
					return;
				}
				
				$this->say(sprintf('%s has survived %s time(s) and has been killed %s time(s)!', $this->arguments[1], $stats[strtolower($this->arguments[1])]['survived'], $stats[strtolower($this->arguments[1])]['killed']));
			break;
		}
		return;
	}
}
