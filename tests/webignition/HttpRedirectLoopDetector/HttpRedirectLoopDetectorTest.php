<?php

namespace webignition\HttpRedirectLoopDetector;

class HttpRedirectLoopDetectorTest extends \PHPUnit_Framework_TestCase {
    
    public function testHasDirectRedirectLoop() {
        $httpClient = new \Guzzle\Http\Client();
        $mockPlugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));        
        $httpClient->addSubscriber($mockPlugin);
        
        $detector = new \webignition\HttpRedirectLoopDetector\HttpRedirectLoopDetector();
        $detector->setHttpClient($httpClient);
        $detector->setUrl('http://example.com/');
        $this->assertTrue($detector->test());
    }
    
    public function testHasIndirectRedirectLoop() {
        $httpClient = new \Guzzle\Http\Client();
        $mockPlugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/2/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/2/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/2/'));        
        $httpClient->addSubscriber($mockPlugin);
        
        $detector = new \webignition\HttpRedirectLoopDetector\HttpRedirectLoopDetector();
        $detector->setHttpClient($httpClient);
        $detector->setUrl('http://example.com/');
        $this->assertTrue($detector->test());
    } 
    
    public function testHasNoDirectRedirectLoop() {
        $httpClient = new \Guzzle\Http\Client();
        $mockPlugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 200 Ok'));     
        $httpClient->addSubscriber($mockPlugin);
        
        $detector = new \webignition\HttpRedirectLoopDetector\HttpRedirectLoopDetector();
        $detector->setHttpClient($httpClient);
        $detector->setUrl('http://example.com/');
        $this->assertFalse($detector->test());        
    }
    
    public function testHasNoIndirectRedirectLoop() {
        $httpClient = new \Guzzle\Http\Client();
        $mockPlugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/2/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 200'));
        $httpClient->addSubscriber($mockPlugin);
        
        $detector = new \webignition\HttpRedirectLoopDetector\HttpRedirectLoopDetector();
        $detector->setHttpClient($httpClient);
        $detector->setUrl('http://example.com/');
        $this->assertFalse($detector->test());        
    }
    
    public function testExcludeSourceUrlFromHistoryComparison() {
        $httpClient = new \Guzzle\Http\Client();
        $mockPlugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/2/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/3/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/4/'));
        $mockPlugin->addResponse(\Guzzle\Http\Message\Response::fromMessage('HTTP/1.1 301'."\n".'Location: http://example.com/5/'));        
        $httpClient->addSubscriber($mockPlugin);
        
        $detector = new \webignition\HttpRedirectLoopDetector\HttpRedirectLoopDetector();
        $detector->setHttpClient($httpClient);
        $detector->setUrl('https://example.com/');
        $this->assertFalse($detector->test());          
    }
    
}