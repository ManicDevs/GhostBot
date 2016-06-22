<?php

namespace Classes\IRC;

/**
 * @package IRCBot
 * @subpackage Classes
 */
class Socks5
{
	public function __construct($ph_desthost, $ph_destport, $ph_proxyhost="localhost", $ph_proxyport="9050")
	{
		//Set some default attributes.
		$this->ready = false;
		
		//Establish connection to SOCKS proxy server and perform handshake.
		//Initiate class variables for error handling using $this->errno and $this->errbuf
		$this->socket = fsockopen($ph_proxyhost, $ph_proxyport, $this->errno, $this->errbuf);
		if(!is_resource($this->socket))
		{
			//Can't connect to the local proxy.
			$this->ph_error("Connection to proxy server could not be established on $ph_proxyhost:$ph_proxyport (Connection refused)");
			$this->socket_status = -1;
			return false;
		}
		
		//Connection has been established.
		$this->socket_status = 1;
		
		//Initiate the SOCKS handshake sequence.
		//Write our version an method to the server.
		//Version 5, 1 authentication method, no authentication. (For now)
		fputs($this->socket, pack("C3", 0x05, 0x01, 0x00) );
		
		//Wait for a reply from the SOCKS server.
		$this->server_status = fread($this->socket, 8192);
		
		//Check if server status is okay.
		//Throw error if required.
		if($this->server_status == pack("C2", 0x05, 0x00))
		{
			$this->socket_status = 2;
		}
		else
		{
			$this->ph_error("SOCKS Server does not support this version and/or authentication method of SOCKS.");
			$this->socket_status = -2;
			return false;
		}
		
		//At this stage, our SOCKS socket should be open and ready to make its remote connection.
		//Send the connection request.
		fputs($this->socket, pack("C5", 0x05 , 0x01 , 0x00 , 0x03, strlen($ph_desthost)) . $ph_desthost . pack("n", $ph_destport));
		
		//Wait for a reply from the SOCKS server.
		$this->server_buffer = fread($this->socket, 8192);
		
		//Check is the connection succeeded.
		if($this->server_buffer == pack("C10", 0x05, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00))
		{
			//Connection succeeded.
			$this->socket_status = 3;
		}
		else
		{
			//Connection failed.
			$this->ph_error("The SOCKS server failed to connect to the specificed host and port. ($ph_desthost:$ph_destport)");
			$this->socket_status = -3;
			return false;
		}
		
		//Our socket is now ready to write data.
		if($this->socket_status == 3)
		{
			$this->ready = true;
		}
	}
	
	public function __destruct()
	{
		//Close the socket connection if it's open
		if(is_resource($this->socket)) 
		{
			fclose($this->socket);
		}
	}
	
	public function ph_error($ph_errormsg)
	{
		//global $shutdown;
		//$shutdown = true;
		echo "[Error] $ph_errormsg\n";
	}
	
	public function ph_debug_dump()
	{
		print_r($this);
	}
}
