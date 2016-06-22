<?php

namespace Listener;

class Shortenurl extends \Classes\Listener\Base
{
	private $apiUri = "";

	public function execute($data)
	{
		#/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i
		preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $data, $url);
		
		if(filter_var(@$url[0], FILTER_VALIDATE_URL))
		{
			$shortData = $this->fetch('http://is.gd/create.php?url='.urlencode($url[0]).'&shorturl=&opt=0');
			
			preg_match('/<\/p>.*value="(.*)"/U', $shortData, $shorturl);
			
			if(isset($shorturl[1]))
			{
				$args = $this->getArguments($data);
				$this->say(sprintf('Shorten: %s', $shorturl[1]), $args[2]);
			}
		}
	}
	
	public function getKeywords()
	{
		return array("PRIVMSG");
	}
}
