<?php

namespace Command;
/**
 * @package IRCBot
 * @subpackage Command
 */
class Relay extends \Classes\Command\Base {
	/**
	 * The command's help text.
	 *
	 * @var string
	 */
	protected $help = 'Relay a remote IRC Channel to a Local one.';
	
	/**
	 * How to use the command.
	 *
	 * @var string
	 */
	protected $usage = 'relay <ssl true/false> <host> <port> <channel> <relayhere true/false> <nickname> <ident> <showsenderhost true/false>';
	
	/**
	 * Verify the user before executing this command.
	 *
	 * @var bool
	 */
	protected $verify = true;
	
	/**
	 * The number of arguments the command needs.
	 *
	 * @var integer
	 */
	protected $numberOfArguments = array(1, 2, 8);

	private $pids = array();

	public function command()
	{
		if($this->arguments[0] == 'list')
		{
			if(count($this->pids) > 0)
			{
				$this->say('Relay: Listing relay threads!');
				$cnt=1;
				foreach($this->pids as $key => $pid)
				{
					$this->say("$cnt) $pid = $key");
					++$cnt;
				}
				return;
			}
			$this->say('Relay: No relays currently running!');
			return;
		}
		
		if($this->arguments[0] == 'kill')
		{
			if(in_array($this->arguments[1], $this->pids))
			{
				$this->say('Relay: Killing relay thread with PID: ' . $this->arguments[1] . '!');
				exec('kill -9 ' . $this->arguments[1]);
				$key = array_search($this->arguments[1], $this->pids);
				unset($this->pids[$key]);
				return;
			}
			$this->say('Relay: No such relay with PID: ' . $this->arguments[1]);
			return;
		}

		if($this->arguments[0] == 'killall')
		{
			if(count($this->pids) > 0)
			{
				$this->say('Relay: Killing relay threads with PIDs: ' . implode(',', $this->pids) . '!');
				$cnt=1;
				foreach($this->pids as $key => $pid)
				{
					exec('kill -9 ' . $pid);
					unset($this->pids[$key]);
					$this->say("$cnt) Killed $pid = $key");
				}
				return;
			}
			$this->say('Relay: No relays currently running!');
			return;
		}

		$pid = pcntl_fork();
		$this->pids[$this->arguments[3].'@'.$this->arguments[1]] = $pid;
		if(!$pid)
		{
			$ssl = false;
			if(strcasecmp($this->arguments[0], 'true') == 0)
			{
				$ssl = true;
			}
			
			$relayhere = false;
			if(strcasecmp($this->arguments[4], 'true') == 0)
			{
				$relayhere = true;
			}

			$showuserhost = false;
			if(strcasecmp($this->arguments[7], 'true') == 0)
			{
				$showuserhost = true;
			}
			
			$this->IRCConnect($ssl, $this->arguments[1], intval($this->arguments[2]), $this->arguments[3], 
									true, $this->arguments[5], $this->arguments[6], $showuserhost);
		}
	}
	
	public function __destruct()
	{
		foreach($this->pids as $pid)
		{
			exec("kill -9 $pid");
		}
	}	

	private function IRCConnect($ssl, $host, $port, $channel, $relayhere, $nickname, $ident, $showsenderhost)
	{
		if($ssl)
		{
			$context = stream_context_create(['ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed'=> true
			]]);
			$socket = stream_socket_client("ssl://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
		}
		else
		{
			$socket = stream_socket_client("tcp://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT);
		}
		
		if($socket == FALSE)
		{
			$this->say(sprintf("Failed to connect to IRC [%s@%s:%s]\r\n", $channel, $host, $port));
			return;
		}
		
		$this->say(sprintf("Connecting to IRC [%s@%s:%s]\r\n", $channel, $host, $port));
		
		if($relayhere)
		{
			$this->say(sprintf("Relaying messages from [%s@%s:%s]\r\n", $channel, $host, $port));	
		}
		else
		{
			$this->connection->sendData("JOIN $channel\r\n");
			$this->say(sprintf("Join channel %s on this IRC to see the relayed messages from [%s@%s:%s]\r\n", $channel, $channel, $host, $port));	
		}
		
		fputs($socket,"USER $ident 8 * :$ident\r\n");
		fputs($socket,"NICK $nickname\r\n");
		
		while(true)
		{
			while($data = substr_replace(fgets($socket), '', -2))
			{	
				$splitdata = explode(' ', $data);
				
				if(strcasecmp($splitdata[0], 'PING') == 0)
					fputs($socket, "PONG " . $splitdata[1] . "\r\n");
					
				if(count($splitdata) < 4)
					continue;
				
				$sender = $splitdata[0];
				$action = $splitdata[1];
				$recipient = $splitdata[2];
				
				if(strcasecmp($action, '422') == 0 || strcasecmp($action, '376') == 0)
				{
					fputs($socket, "JOIN $channel\r\n");
				}
				elseif(strcasecmp($action, 'PRIVMSG') == 0)
				{
					$message = substr($data, strpos($data, ' :') + 2);
					
					if(isset($message) && strlen($message) > 0)
					{
						if(!$showsenderhost)
						{
							$sender = explode('!', $sender)[0];
						}

						if($relayhere)
						{
							$this->say("[$recipient@$host]<" . substr($sender, 1) . "> $message");
						}
						else
						{
							$this->connection->sendData(
								"PRIVMSG $channel :[$recipient@$host]<" . substr($sender, 1) . "> $message\r\n"
							);
						}
					}
				}
			}
		}
		fclose($socket);
	}
	
}
