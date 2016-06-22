<?php

namespace Classes\Listener;

abstract class Base
{
	protected $connection = null;

	protected $bot = null;
	
	abstract function execute($data);
	
	abstract function getKeywords();
	
	protected function say($msg, $source)
	{
		$this->connection->sendData('PRIVMSG ' . $source . ' :' . $msg);
	}
	
	public function setIRCConnection( \Classes\IRC\SocketConnection $ircConnection )
	{
		$this->connection = $ircConnection;
	}
	
	public function setIRCBot(\Classes\IRC\BotCore $ircBot)
	{
		$this->bot = $ircBot;
	}
	
	protected function getArguments($data)
	{
		$args = explode( ' ', $data );
		$func = function($value)
		{
			return trim(\Classes\Functions::removeLineBreaks($value));
		};

		return array_map($func, $args);
	}
	
	/*
	protected function fetch($uri)
	{

		$this->bot->log("Fetching from URI: " . $uri);
		
		$options = array(
  			'http'=>array(
   			'method'=>"GET",
    			'header'=>"Accept-language: en\r\n" .
					"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
			)
		);
		$context = stream_context_create($options);
		$output = @file_get_contents($uri, false, $context);
		
		$this->bot->log("Data fetched!");

		return $output;
	}
	*/
	
	protected function fetch($url, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT)
	{
		$this->bot->log("Fetching from URL: " . $url);
		
		// We DO force the tags to be terminated.
		$dom = new \Classes\Htmldom(null, $lowercase, $forceTagsClosed, $target_charset, $defaultBRText);
		
		// For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
		$contents = @file_get_contents($url, $use_include_path, $context, $offset);
		
		$this->bot->log("Data fetched!");
		
		// Paperg - use our own mechanism for getting the contents as we want to control the timeout.
		//$contents = retrieve_url_contents($url);
		if (empty($contents))
		{
			return false;
		}
		
		// The second parameter can force the selectors to all be lowercase.
		$dom->load($contents, $lowercase, $stripRN);
		return $dom;
	}

	protected function strfetch($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT)
	{
		$dom = new \Classes\Htmldom(null, $lowercase, $forceTagsClosed, $target_charset, $defaultBRText);
		if (empty($str))
		{
			$dom->clear();
      	return false;
   	}
    	$dom->load($str, $lowercase, $stripRN);
		return $dom;
	}
}
