<?php

namespace webignition\HttpRedirectLoopDetector;

use Guzzle\Http\Client;

class HttpRedirectLoopDetector {
    
    const DEFAULT_REDIRECT_LIMIT = 5;
    
    /**
     *
     * @var array
     */
    private $history = array();
    
    /**
     *
     * @var string
     */
    private $url;
    
    
    /**
     *
     * @var \Guzzle\Http\Client 
     */
    private $httpClient;
    
    
    /**
     *
     * @var string
     */
    private $currentUrl;
    
    
    /**
     * 
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
        $this->currentUrl = $url;
        $this->history = array();
    }
    
    
    /**
     * 
     * @param \Guzzle\Http\Client $client
     */
    public function setHttpClient(\Guzzle\Http\Client $client) {
        $this->httpClient = $client;
    }
    
    
    /**
     * 
     * @return \Guzzle\Http\Client
     */
    public function getHttpClient() {
        if (is_null($this->httpClient)) {
            $this->httpClient = new \Guzzle\Http\Client();
        }
        
        return $this->httpClient;
    }
    
    
    /**
     * 
     * @return boolean
     */
    public function test() {
        while (count($this->history) < self::DEFAULT_REDIRECT_LIMIT) {
            $request = $this->getHttpClient()->get($this->currentUrl, array(), array('allow_redirects' => false)); 
            $response = $request->send();
            
            if ($response->isRedirect()) {
                $this->history[] = $this->currentUrl;
                $this->currentUrl = $this->getResponseLocation($response);
            } else {
                return false;
            }
        }
        
        $sourceUrl = new \webignition\NormalisedUrl\NormalisedUrl($this->url);
        foreach ($this->history as $url) {
            $comparatorUrl = new \webignition\NormalisedUrl\NormalisedUrl($url);
            if ((string)$sourceUrl == (string)$comparatorUrl) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * 
     * @param \Guzzle\Http\Message\Response $response
     * @return string
     */
    private function getResponseLocation(\Guzzle\Http\Message\Response $response) {
        $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver($response->getLocation(), $this->currentUrl);
        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    }
    
}