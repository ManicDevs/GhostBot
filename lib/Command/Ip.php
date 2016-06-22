<?php

namespace Command;

class Ip extends \Classes\Command\Base
{
	protected $help = 'Get an IP Address return from a DNS.';
	
	protected $usage = 'ip [domain name]';

	protected $numberOfArguments = 1;

	public function command()
	{
		$dnsname = trim($this->arguments[0]);

		$dnsname = str_replace('http://', '', $dnsname);
		$dnsname = str_replace('https://', '', $dnsname);

		if(!filter_var("http://$dnsname", FILTER_VALIDATE_URL))
		{
			$this->say("Enter a Domain Name. (Usage: ip example.com)");
			return;
		}
		
		$ipaddr = (array)gethostbyname($dnsname);
		$ipaddr = implode(',', $ipaddr);
		
		$this->say(sprintf('DNS: %s | IP(s): %s', $dnsname, $ipaddr));
	}
}
