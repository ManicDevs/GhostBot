<?php

namespace Command;

class Pingsite extends \Classes\Command\Base
{
	protected $help = 'Ping a website';
	
	protected $usage = 'pingsite [ip]|[domain]';
	
	protected $numberOfArguments = 1;

	public function command()
	{
		$starttime	= microtime(true);
		$file			= fsockopen(trim($this->arguments[0]), 80, $errno, $errstr, 10);
		$stoptime	= microtime(true);
		$status		= 0;

		if(!$file)
		{
			$status = -1;  // Site is down
		}
		else
		{
			fclose($file);
			$status = floor(($stoptime - $starttime) * 1000);
		}
		
		if(filter_var(gethostbyname(trim($this->arguments[0])), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))
		{
			$this->say(sprintf("Pingsite: %s responded to ping in %.2fms", trim($this->arguments[0]), $status));
			return;
		}
		
		$this->say(sprintf("Pingsite: %s didn't respond to ping.", trim($this->arguments[0])));
	}
}
