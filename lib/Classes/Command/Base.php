<?php

namespace Classes\Command;

/**
 * @package IRCBot
 * @subpackage Classes
 */
abstract class Base
{

	/**
	 * Reference to the IRC Connection.
	 * @var \Classes\IRC\Connection
	 */
	protected $connection = null;

	/**
	 * Reference to the IRC Bot
	 * @var \Classes\IRC\Bot
	 */
	protected $bot = null;

	/**
	 * Contains all given arguments.
	 * @var array
	 */
	protected $arguments = array ( );

	/**
	 * Contains channel or user name
	 *
	 * @var string
	 */
	protected $source = null;

	/**
	 * Contains user name
	 *
	 * @var string
	 */
	protected $usersource = null;
	
	/**
	 * Original request from server
	 *
	 * @var string
	 */
	protected $data;

	/**
	 * The number of arguments the command needs.
	 *
	 * You have to define this in the command.
	 *
	 * @var integer
	 */
	protected $numberOfArguments = 0;
	
	/**
	 * The help string, shown to the user when using the help command.
	 *
	 * This is optional to define in the command, but it is recommended you do.
	 *
	 * @var string
	 */
	protected $help = '';

	/**
	 * The usage string, shown to the user if the user calls the command with wrong parameters.
	 *
	 * You have to define this in the command.
	 *
	 * @var string
	 */
	protected $usage = '';
	
	/**
	 * Verify the user before executing a command.
	 *
	 * Defaults to false to allow everyone to execute commands
	 * which do not have this flag set.
	 *
	 * This is optional to define in the command.
	 *
	 * @var bool
	 */
	protected $verify = false;

	/**
	 * Executes the command.
	 *
	 * @param array           $arguments The assigned arguments.
	 * @param string          $source    Originating request
	 * @param string          $data      Original data from server
	 */
	public function executeCommand( array $arguments, $source, $data )
	{
		// Set source
		$this->source = $source;

		// Set usersource
		$this->usersource = substr(explode('!', $data)[0], 1);
		
		// Set data
		$this->data = $data;
		
		// Do we verify the legitimacy of the user executing?
		if($this->needsVerification() && !$this->verifyUser())
		{
			$this->bot->log('Failed to request permission; aborting command.');
			return;
		}
		elseif($this->needsVerification())
		{
			$this->bot->log('Success; proceeding with command.');
		}
		
		// If a number of arguments is incorrect then run the command, if
		// not then show the relevant help text.
		// This is fugly, but it works.
		
		// If it's an int...
		if (is_numeric($this->numberOfArguments))
		{
			if (($this->numberOfArguments === -1 && count($arguments) == 0) || ($this->numberOfArguments !== -1 && count($arguments) != $this->numberOfArguments))
			{
				$this->say('Error: illegal amount of arguments. For help, use ' . $this->bot->commandPrefix . 'help ' . str_replace('Command\\', '', get_class($this)));
				return;
			}
		}
		// But if it's an array... An array means this command can take multiple counts of arguments, and react accordingly.
		elseif (is_array($this->numberOfArguments))
		{
			if (!((in_array(count($arguments), $this->numberOfArguments)) || (in_array(-1, $this->numberOfArguments) && count($arguments) >= 1)))
			{
				$this->say('Error: illegal amount of arguments. For help, use' . $this->bot->commandPrefix . 'help ' . str_replace('Command\\', '', get_class($this)));
				return;
			}
		}
		// Some safeguarding here.
		else
		{
			$this->bot->log(get_class($this) . ': No number of arguments variable set. Please add the $numberOfArguments variable to your command file.');
			$this->bot->log('This command will not work until fixed.');
			return;
		}
		
		// Set Arguments
		$this->arguments = $arguments;

		// Execute the command.
		$this->command();
	}
	
	/**
	 * Checks the legitimacy of the user running a command.
	 *
	 */
	protected function verifyUser()
	{
		global $config;
		// Get the host.
		preg_match("/([^\s]+)/", $this->data, $hosts);
		$hosts[0] = substr($hosts[0], 1);
		
		// Check if the user has privileges.
		$this->bot->log('Requesting privileges for host ' . $hosts[0] . '!');
		if(!in_array($hosts[0], $config['hosts']))
		{
			// Nope. No access for you.
			if($this->verify)
			{
				$args = explode(' ', $this->data);
				$usersource = explode('!', $args[0]);
				$usersource = substr($usersource[0], 1);
				$this->say('Unable to process: ' . $usersource . ' is not in the hosts list.', substr($args[2], 1));
				$this->bot->log('Failed; this host is not trusted.');
			}
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Sends PRIVMSG to source with $msg
	 *
	 * @param string $msg
	 */
   protected function say($msg)
   {
		$this->connection->sendData('PRIVMSG ' . $this->source . ' :' . $msg);
	}

/**
 * Get help for the command being run.
 */
	public function getHelp()
	{
		if (!empty($this->help))
		{
			return array($this->help, $this->usage);
		}
	}
	
	/**
	 * Check if the current commands requires verification.
	 */
	public function needsVerification()
	{
		//return !empty($this->verify);
		return $this->verify;
	}

	/**
	 * Overwrite this method for your needs.
	 * This method is called if the command get's executed.
	 */
	public function command()
	{
		echo 'fail';
		flush();
		throw new Exception( 'You have to overwrite the "command" method and the "executeCommand". Call the parent "executeCommand" and execute your custom "command".' );
	}

	/**
	 * Set's the IRC Connection, so we can use it to send data to the server.
	 * @param \Classes\IRC\Connection $ircConnection
	 */
	public function setIRCConnection( \Classes\IRC\SocketConnection $ircConnection )
	{
		$this->connection = $ircConnection;
	}

	/**
	 * Set's the IRC Bot, so we can use it to send data to the server.
	 *
	 * @param \Classes\IRCBot $ircBot
	 */
	public function setIRCBot( \Classes\IRC\BotCore $ircBot )
	{
		$this->bot = $ircBot;
	}

	/**
	 * Returns requesting user IP
	 *
	 * @return string
	 */
	protected function getUserIp() {
		// catches from @ to first space
		if (preg_match('/@([a-z0-9.-]*) /i', $this->data, $match) === 1)
		{
			$hostname = $match[1];

			$ip = gethostbyname($hostname);

			// did we really get an IP
			if (preg_match( '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $ip ) === 1)
			{
				return $ip;
			}
		}

		return null;
	}

	/**
	 * Fetches data from $uri
	 *
	 * @param string $uri
	 * @return string
	 */
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
		$output = file_get_contents($uri, false, $context);
		
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
		if(empty($contents))
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
