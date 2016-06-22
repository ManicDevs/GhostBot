<?php

namespace Classes\IRC;

class BotSocket implements \Classes\IRC\SocketConnection
{
	private $socket = null;
	
	private $serverAddr = '';
	
	private $serverPort = 0;	
	
	public function setServerAddr($serverAddr)
	{
		$this->serverAddr = $serverAddr;
	}

	public function setServerPort($serverPort)
	{
		$this->serverPort = $serverPort;
	}

	public function doConnect()
	{
		global $config;
		if(!$config['socks5']['enabled'])
		{
			if($config['serverssl'])
			{
				$context = stream_context_create(['ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed'=> true
				]]);
				$this->socket = stream_socket_client('ssl://' . $this->serverAddr . ':' . $this->serverPort, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
			}
			else
			{
				$this->socket = stream_socket_client('tcp://' . $this->serverAddr . ':' . $this->serverPort, $errno, $errstr, 30, STREAM_CLIENT_CONNECT);			
			}
		}
		else
		{
			$this->socks5 = new Socks5($this->serverAddr, $this->serverPort);
			$this->socket = $this->socks5->socket;
		}
		return true;
	}
	
	public function doDisconnect()
	{
		if(is_resource($this->socket))
		{
			fclose($this->socket);
			return true;
		}
		return false;
	}
		
	public function isConnected()
	{
		if(is_resource($this->socket))
		{
			return true;
		}
		return false;
	}
	
	public function sendData($buffer = '')
	{
		@fwrite($this->socket, "$buffer\r\n", strlen("$buffer\r\n"));
		return true;
	}
	
	public function recvData($buflen = 1024)
	{
		$buffer = @fgets($this->socket, $buflen);
		return trim(\Classes\Functions::removeLineBreaks($buffer));
	}
}

interface SocketConnection
{
	public function doConnect();

	public function doDisconnect();

	public function isConnected();

	public function sendData($buffer);

	public function recvData($buflen);
}