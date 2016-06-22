<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Mball extends \Classes\Command\Base
{
	protected $help = '8Ball replies to your questions!';
	
	protected $usage = '8ball [message]';

	protected $numberOfArguments = -1;

	public function command()
	{
		$replies = array('It is certain', 'It is decidedly so', 'Without a doubt',
        'Yes - definitely', 'You may rely on it', 'As I see it, yes',
        'Most likely', 'Outlook good', 'Signs point to yes', 'Yes',
        'Reply hazy, try again', 'Ask again later', 'Better not tell you now',
        'Cannot predict now', 'Concentrate and ask again', 'Don\'t count on it',
        'My reply is no', 'My sources say no', 'Outlook not so good',
        'Very doubtful');   

		$this->say(sprintf("%s, %s", $this->usersource, $replies[array_rand($replies)]));
		return;
	}
}
