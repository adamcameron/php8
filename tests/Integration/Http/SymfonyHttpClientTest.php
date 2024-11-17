<?php

namespace adamcameron\php8\tests\Integration\Http;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

#[TestDox("Tests of Symfony's HTTP client functionality")]
#[Group("slow")]
class SymfonyHttpClientTest extends HttpTestBase
{

    #[TestDox("It can make a GET request")]
    public function testGet()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api.github.com/users/adamcameron');
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertGitInfoIsCorrect($response->getContent());
    }

    #[TestDox("It can make multiple asynchronous GET requests")]
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

    #[TestDox("It can make a POST request")]
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
