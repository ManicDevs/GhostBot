<?php

namespace Command;

class Imdb extends \Classes\Command\Base
{
	protected $help = 'Get information about IMDB movies.';
	
	protected $usage = 'imdb [movie title]';

	private $apiUri = 'http://omdbapi.com/?t=%s&r=json&plot=short';

	protected $numberOfArguments = -1;

	public function command()
	{
		$imdbTitle = implode(' ', $this->arguments);
		$imdbTitle = preg_replace('/\s\s+/', ' ', $imdbTitle);
		$imdbTitle = trim($imdbTitle);
		$imdbTitle = urlencode($imdbTitle);

		if (!strlen($imdbTitle))
		{
			$this->say(sprintf('Enter movie title. (Usage: !imdb movie title)'));
			return;
		}

		$apiUri  = sprintf($this->apiUri, $imdbTitle);
		$getJson = $this->fetch($apiUri);

		$json = json_decode($getJson, true);

		$title		= $json['Title'];
		$rating		= $json['imdbRating'];
		$shortPlot	= $json['Plot'];
		$link			= 'http://www.imdb.com/title/' . $json['imdbID'];

		if(!strlen($title))
		{
			$this->say('IMDB: Error fetching data');
			return;
		}

		$this->say(sprintf('Title: %s | Rating: %s | Link: %s | Plot: %s', $title, $rating, $link, $shortPlot));
	}
}
