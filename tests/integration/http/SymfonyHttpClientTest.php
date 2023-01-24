<?php

namespace adamcameron\php8\tests\integration\http;

use Symfony\Component\HttpClient\HttpClient;

/** @testdox Tests of Symfony's HTTP client functionality */
class SymfonyHttpClientTest extends HttpTest
{

    /** @testdox It can make a GET request */
    public function testGet()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api.github.com/users/adamcameron');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertGitInfoIsCorrect($response->getContent());
    }

    /** @testdox It can make multiple asynchronous GET requests */
    public function testMultipleAsyncGet()
    {
        $client = HttpClient::create();
        $requestsToMakeConcurrently = [
            $client->request('GET', 'http://nginx/test-fixtures/slow.php?timeToWait=1'),
            $client->request('GET', 'http://nginx/test-fixtures/slow.php?timeToWait=2'),
            $client->request('GET', 'http://nginx/test-fixtures/slow.php?timeToWait=3')
        ];
        $stream = $client->stream($requestsToMakeConcurrently);

        $i = 1;
        $startTime = microtime(true);
        foreach ($stream as $response => $chunk) {
            if ($chunk->isLast()) {
                $this->assertEquals(200, $response->getStatusCode());
                $this->assertEquals("waited $i seconds", $response->getContent());
                $i++;
            }
        }
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $this->assertGreaterThan(3, $totalTime);
        $this->assertLessThan(4, $totalTime);
    }

    /** @testdox It can make a POST request */
    public function testPost()
    {
        $client = HttpClient::create();
        $response = $client->request(
            'POST',
            'https://httpbin.org/post',
            ['body' => ['foo' => 'bar']]
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $httpBinResponse = json_decode($response->getContent());
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }
}
