<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Findtweets extends \Classes\Command\Base
{
	protected $help = 'Find Live Twitter Tweets';
	
	protected $usage = 'findtweets [query]';

	protected $numberOfArguments = -1;

	public function command()
	{
		$query = implode(' ', $this->arguments);
		$query = urlencode(trim($query));
		$html = $this->fetch('https://twitter.com/search?f=tweets&q=include:retweets%20' . $query);
		$tweets = $html->find('div.content');

		if(count($tweets) === 0)
		{
			$this->say(sprintf("FindTweets: %s, Unable to find any tweets with that query, try again later or use another query!", $this->usersource));
			
			return;
		}
		
		array_splice($tweets, 2);
		
		foreach($tweets as $tweet)
		{
			$tweetFromFullName	= $tweet->find('strong.fullname', 0)->plaintext;
			$tweetFromUserName	= $tweet->find('span.username', 0)->plaintext;
			$tweetTimeStamp		= $tweet->find('a.tweet-timestamp', 0)->title;
			$tweetTimePast			= $tweet->find('span.u-hiddenVisually', 0)->plaintext;
			$tweetMessage			= $tweet->find('p.tweet-text', 0)->plaintext;
			
			str_replace('pic.', ' pic.', $tweetMessage);			

			#$this->say(sprintf("FindTweets: -> Name: %s - %s | Time: %s - %s | 4,0Tweet: %s",
			#	$this->decodeChars($tweetFromFullName),
			#	$this->decodeChars($tweetFromUserName),
			#	$tweetTimeStamp, $tweetTimePast,
			#	$this->decodeChars($tweetMessage)));
				
			$this->say(sprintf('Tweet: -> %s - %s', $this->decodeChars($tweetFromFullName), $this->decodeChars($tweetFromUserName)));
			$this->say(sprintf(' -> Time: %s %s', $tweetTimeStamp, '- ' . $tweetTimePast));
			$this->say(sprintf(' -> Msg: %s', $this->decodeChars($tweetMessage)));
		}
		
		return;
	}

	private function decodeChars($input)
	{
		return html_entity_decode(htmlspecialchars_decode(preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input)));
	}
}
