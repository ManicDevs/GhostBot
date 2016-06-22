<?php

namespace Listener;

class Youtube extends \Classes\Listener\Base
{
	private $apiUri = "https://%s";

	public function execute($data)
	{
		//preg_match('#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#', $data, $matches);
		preg_match('/(youtube.com|youtu.be)\/watch\?v=(.*)/', $data, $matches);
		if(isset($matches[0]))
		{
			$ytApi = sprintf($this->apiUri, $matches[0]);
			$html	= $this->fetch($ytApi);			
			
			$ytTitle = @$this->decodeChars(str_replace(' - YouTube', '', @$html->find('title', 0)->plaintext));
			$ytViews = @$html->find('div.watch-view-count', 0)->plaintext);			
			
			if($ytViews !== '')
			{
				$args = $this->getArguments($data);
				$this->say(sprintf("1,0You0,20Tube %s [Views: %s]", $ytTitle, $ytViews), $args[2]);
			}
		}
	}

	
	private function decodeChars($input)
	{
		return htmlspecialchars_decode(preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input));
	}
	
	public function getKeywords()
	{
		return array("PRIVMSG");
	}
}
