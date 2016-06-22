<?php

namespace Command;

class Pingraw extends \Classes\Command\Base
{
	protected $help = 'Ping with raw sockets';
	
	protected $usage = 'pingraw [ip]|[domain]';
	
	protected $numberOfArguments = 1;

	public function command()
	{		
		$package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
		
		$sock = @\socket_create(AF_INET, SOCK_RAW, 1);
		if($sock === false)
		{
			$this->say("Pingraw: Unable to utilize Pingraw as I'm not root or equivalent.");
			return;
		}
		
		socket_bind($sock, '0.0.0.0');
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
		socket_connect($sock, gethostbyname(trim($this->arguments[0])), null);
		$start_time = microtime(true);
		socket_send($sock, $package, strlen($package), 0);
		
		if(@socket_read($sock, 255))
		{
			$end_time = microtime(true);
			$status = floor(($end_time - $start_time) * 1000);
		}
		else
		{
			$status = -1;
		}
		socket_close($sock);

		if(filter_var(gethostbyname(trim($this->arguments[0])), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))
		{
			$this->say(sprintf("Pingraw: %s responded to ping in %.2fms", trim($this->arguments[0]), $status));
			return;
		}
		
		$this->say(sprintf("Pingraw: %s didn't respond to ping.", trim($this->arguments[0])));
	}
}
