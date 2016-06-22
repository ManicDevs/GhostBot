<?php

namespace Command;

class Shorten extends \Classes\Command\Base
{
	protected $help = 'Shorten a URL to a smaller link.';

	protected $usage = 'urlshorten http://google.com';
	
	protected $verify = false;
	
	protected $numberOfArguments = 1;
	
	public function command()
	{
		$url = $this->arguments[0];
		if(filter_var($url, FILTER_VALIDATE_URL))
		{
			$shortData = $this->fetch('http://is.gd/create.php?url='.urlencode($url).'&shorturl=&opt=0');
			
			preg_match('/<\/p>.*value="(.*)"/U', $shortData, $shorturl);
			
			if(isset($shorturl[1]))
			{
				$this->say(sprintf('Shorten: %s | Long URL: %s', $shorturl[1], $url));
			}
			else
			{
				$this->say('Shorten: Error (unable to shorten [is.gd urls, or IPs])!');
			}
		}
	}
}
