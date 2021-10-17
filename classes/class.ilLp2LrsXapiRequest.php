<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\RequestOptions as HttpRequestOptions;
use GuzzleHttp\Psr7\ServerRequest as HttpRequest;
use GuzzleHttp\Psr7\Uri as HttpRequestUri;
use GuzzleHttp\Client as HttpClient;

/**
 * Class ilLp2LrsXapiRequest
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilLp2LrsXapiRequest
{
	/**
	 * @var ilLogger
	 */
	protected $log;
	/**
	 * string
	 */
	protected $url;
	/**
	 * @var string
	 */
	protected $authKey;
	/**
	 * @var string
	 */
	protected $authSecret;
	
	/**
	 * ilLp2LrsXapiRequest constructor.
	 * @param ilLogger $log
	 * @param $url
	 * @param string $authKey
	 * @param string $authSecret
	 */
	public function __construct(ilLogger $log, string $url, string $authKey, string $authSecret)
	{
		$this->log = $log;
		$this->url = $url;
		$this->authKey = $authKey;
		$this->authSecret = $authSecret;
	}
	
	/**
	 * @return string
	 */
	protected function buildBasicAuth()
	{
		return 'Basic ' . base64_encode($this->authKey . ':' . $this->authSecret);
	}
	
	/**
	 * @return HttpRequestUri
	 */
	protected function buildUri()
	{
		$uri = new HttpRequestUri($this->url);
		return $uri;
	}
	
	/**
	 * @return array
	 */
	protected function buildHeaders($contentLength)
	{
		return [
			'Authorization' => [$this->buildBasicAuth()],
			'Content-Length' => [strlen($contentLength)],
			'Content-Type' => ['application/json'],
			'Accept' => ['*/*'],
			'X-Experience-API-Version' => ['1.0.3']
		];
	}
	
	/**
	 * @param ilLp2LrsXapiStatementList $statementList
	 * @return HttpRequest
	 */
	protected function buildRequest(ilLp2LrsXapiStatementList $statementList)
	{
		$postBody = $statementList->getPostBody();
		
		$this->log->debug(
			"LP xAPI Statement: ".json_encode(json_decode($postBody), JSON_PRETTY_PRINT)
		);
		
		$serverRequest = new HttpRequest(
			'POST',
			$this->buildUri(),
			$this->buildHeaders($postBody),
			$postBody,
			'1.1',
			$_SERVER
		);
		
		return $serverRequest;
	}
	
	/**
	 * @return array
	 */
	protected function buildRequestOptions()
	{
		return [
			HttpRequestOptions::VERIFY => DEVMODE ? false : true,
			HttpRequestOptions::CONNECT_TIMEOUT => 5
		];
	}
	
	/**
	 * @param ilLp2LrsXapiStatementList $statementList
	 */
	public function send(ilLp2LrsXapiStatementList $statementList)
	{
		$this->log->debug("URL: " . $this->url);
		
		$request = $this->buildRequest($statementList);
		
		try
		{
			$httpClient = new HttpClient();
			
			$asyncPromise = $httpClient->sendAsync($request, $this->buildRequestOptions());
			$response = $asyncPromise->wait();
			
			if( $response->getStatusCode() != 200 )
			{
				$this->log->error('Status : '.$response->getStatusCode().' | Response: ' . $response->getBody());
			}
			else
			{
				$this->log->debug('Response: ' . $response->getBody());
			}
		}
		catch (Exception $e)
		{
			$this->log->error($e);
			return false;
		}
		
		return true;
	}
}
