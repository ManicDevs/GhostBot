<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Findtorrents extends \Classes\Command\Base
{
	protected $help = 'Find Torrents from ThePirateBay';
	
	protected $usage = 'findtorrents [query]';

	protected $numberOfArguments = -1;

	public function command()
	{
		$query = implode(' ', $this->arguments);
		$query = urlencode(trim($query));
		
		$options = array(
  			'http'=>array(
   			'method'=>"GET",
    			'header'=>"Accept-language: en\r\n" .
					"User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)\r\n"
			)
		);
		$context = stream_context_create($options);
		$html = $this->fetch('https://kuiken.co/search/' . $query . '/', false, $context);
		$torrents = $html->find('tr');
		
		if(count($torrents) === 0)
		{
			$this->say(sprintf("FindTorrents: %s, Unable to find any torrents with that query, try again later or use another query!", $this->usersource));
			return;
		}
		
		$torrents = array_slice($torrents, 1);
		array_splice($torrents, 3);
		
		foreach($torrents as $torrent)
		{	
			$torrentName = trim($torrent->find('div.detName', 0)->plaintext);	
			
			$torrentLink = trim($torrent->find('div.detName', 0)->innertext);
			preg_match('/href=\"(.*)\" class=/', $torrentLink, $torrentLink);
			
			#$torrentCategory = trim(urlencode(@$torrent->find('td.vertTh', 0)->plaintext));
			#$torrentCategory = str_replace('+', '', $torrentCategory);
			#$torrentCategory = str_replace('%0D', '', $torrentCategory);	
			#$torrentCategory = str_replace('%0A', '', $torrentCategory);
			#$torrentCategory = str_replace('%09', '', $torrentCategory);
			#$torrentCategory = urldecode($torrentCategory);
			#preg_match('/\(.*\)/', $torrentCategory, $torrentCatType);
			#preg_match('/.*\(/', $torrentCategory, $torrentCategory);			
			
			$shortLink = $this->fetch('http://is.gd/create.php?url='.urlencode('https://thepiratebay.org' . $torrentLink[1]).'&shorturl=&opt=0');	
			$this->say(sprintf('Torrent: -> %s', $torrentName));
			$this->say(sprintf(' -> Link: %s', $shortLink->find('input.tb', 0)->value));
			#$this->say(sprintf(' -> Category: %s', substr($torrentCategory[0], 0, strlen($torrentCategory[0])-1)));
			#$this->say(sprintf(' -> CatType: %s', substr($torrentCatType[0], 1, strlen($torrentCatType[0])-2)));
		}
		return;
	}

	private function decodeChars($input)
	{
		return html_entity_decode(htmlspecialchars_decode(preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input)));
	}
}
