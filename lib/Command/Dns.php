<?php

namespace Command;

class Dns extends \Classes\Command\Base
{
	protected $help = 'Get a DNS return from an IP Address.';

	protected $usage = 'dns [ip address]';

	protected $numberOfArguments = 1;

	public function command()
	{
		$ipaddr = trim($this->arguments[0]);

		if(!filter_var($ipaddr, FILTER_VALIDATE_IP))
		{
			$this->say("Enter an IP address. (Usage: dns 8.8.8.8)");
			return;
		}
		
		$ipaddr = str_replace('http://', '', $ipaddr);
		$ipaddr = str_replace('https://', '', $ipaddr);
		
		$dnsname = gethostbyaddr($ipaddr);
		$dnsname = ($dnsname!=$ipaddr)||($dnsname!='')?$dnsname:'dns-not-set.';
		
		$this->say(sprintf('DNS: %s | IP: %s', $dnsname, $ipaddr));
	}
}
