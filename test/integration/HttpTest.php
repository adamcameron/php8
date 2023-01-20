<?php

namespace edd\api\test\integration;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/** @testdox Tests of HTTP functionality */
class HttpTest extends TestCase
{
    /** @testdox it can make a GET request using curl */
    public function testGetWithCurl()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.github.com/users/adamcameron',
            CURLOPT_USERAGENT => $this->getUserAgentForCurl(),
            CURLOPT_RETURNTRANSFER => 1
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->assertEquals(200, curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $this->assertJson($response);
        $this->assertGitInfoIsCorrect($response);
    }

    /** @testdox it can make a POST request using curl */
    public function testPostWithCurl()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://httpbin.org/post',
            CURLOPT_USERAGENT => $this->getUserAgentForCurl(),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => ['foo' => 'bar']
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->assertEquals(200, curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $this->assertJson($response);
        $httpBinResponse = json_decode($response);
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }

    /** @testdox it can make a GET request using Symfony's HTTP client */
    public function testGetWithSymfonyHttpClient()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api.github.com/users/adamcameron');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertGitInfoIsCorrect($response->getContent());
    }

    /** @testdox it can make a POST request using Symfony's HTTP client */
    public function testPostWithSymfonyHttpClient()
    {
        $client = HttpClient::create();
        $response = $client->request(
            'POST',
            'https://httpbin.org/post',
            ['body' => ['foo' => 'bar']
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $httpBinResponse = json_decode($response->getContent());
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }

    /** it can make a GET request using Guzzle */
    public function testGetWithGuzzleHttpClient()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.github.com/users/adamcameron');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $this->assertGitInfoIsCorrect($response->getBody());
    }

    /** @testdox it can make a POST request using Guzzle */
    public function testPostWithGuzzleHttpClient()
    {
        $client = new Client();
        $response = $client->request(
            'POST',
            'https://httpbin.org/post',
            ['form_params' => ['foo' => 'bar']]
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $httpBinResponse = json_decode($response->getBody());
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }

    /** it can make an asynchronous GET request using Guzzle */
    public function testGetWithGuzzleHttpClientAsync()
    {
        $client = new Client();
        $promise = $client->requestAsync('GET', 'https://api.github.com/users/adamcameron');
        $response = $promise->wait();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $this->assertGitInfoIsCorrect($response->getBody());
    }

    /** it can make multiple asynchronous GET requests with Guzzle */
    public function testMultipleGetWithGuzzleHttpClientAsync()
    {
        $client = new Client();
        $requestsToMakeConcurrently = [
            $client->getAsync('http://nginx/test-fixtures/slow.php?timeToWait=1'),
            $client->getAsync('http://nginx/test-fixtures/slow.php?timeToWait=2'),
            $client->getAsync('http://nginx/test-fixtures/slow.php?timeToWait=3')
        ];
        $startTime = microtime(true);
        $responses = Promise\Utils::unwrap($requestsToMakeConcurrently);
        $endTime = microtime(true);

        $totalTime = $endTime - $startTime;
        $this->assertGreaterThan(3, $totalTime);
        $this->assertLessThan(4, $totalTime);

        array_walk($responses, function ($response, $i) {
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals(sprintf("waited %d seconds", $i+1), $response->getBody());
        });
    }

    /** @testdox it can make an asynchronous POST request using Guzzle */
    public function testPostWithGuzzleHttpClientAsync()
    {
        $client = new Client();
        $promise = $client->requestAsync(
            'POST',
            'https://httpbin.org/post',
            ['form_params' => ['foo' => 'bar']]
        );
        $response = $promise->wait();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $httpBinResponse = json_decode($response->getBody());
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }

    /** @testdox it can make a GET request with PHP streams */
    public function testGetWithStreams()
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => ['User-Agent: ' . $this->getUserAgentForCurl()]
            ]
        ]);
        $response = file_get_contents('https://api.github.com/users/adamcameron', false, $context);
        $this->assertJson($response);
        $this->assertGitInfoIsCorrect($response);
    }

    /** @testdox it can make a POST request with PHP streams */
    public function testPostWithStreams()
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'User-Agent: ' . $this->getUserAgentForCurl(),
                    'Content-Type: application/x-www-form-urlencoded'
                ],
                'content' => http_build_query(['foo' => 'bar'])
            ]
        ]);
        $response = file_get_contents('https://httpbin.org/post', false, $context);
        $this->assertJson($response);
        $httpBinResponse = json_decode($response);
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }

    private function getUserAgentForCurl(): string
    {
        return sprintf("curl/%s", curl_version()['version']);
    }

    /**
     * @param bool|string $response
     * @return void
     */
    public function assertGitInfoIsCorrect(bool|string $response): void
    {
        $myGitMetadata = json_decode($response);
        $this->assertEquals('adamcameron', $myGitMetadata->login);
        $this->assertEquals('Adam Cameron', $myGitMetadata->name);
    }
}
