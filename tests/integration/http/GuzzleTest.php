<?php

namespace adamcameron\php8\tests\integration\http;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

/** @testdox Tests of Guzzle functionality */
class GuzzleTest extends HttpTest
{
    /** @testdox It can make a GET request */
    public function testGet()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.github.com/users/adamcameron');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $this->assertGitInfoIsCorrect($response->getBody());
    }

    /** @testdox It can make a POST request */
    public function testPost()
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

    /** @testdox It can make an asynchronous GET request */
    public function testAsyncGet()
    {
        $client = new Client();
        $promise = $client->requestAsync('GET', 'https://api.github.com/users/adamcameron');
        $response = $promise->wait();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $this->assertGitInfoIsCorrect($response->getBody());
    }

    /** @testdox It can make multiple asynchronous GET requests */
    public function testMultipleAsyncGet()
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

    /** @testdox It can make an asynchronous POST request */
    public function testAsyncPost()
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
}
