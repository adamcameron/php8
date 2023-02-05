<?php

namespace adamcameron\php8\tests\Integration\Http;

/** @testdox Tests of PHP streams functionality relating to HTTP requests */
class PhpStreamsTest extends HttpTestBase
{
    /** @testdox It can make a GET request */
    public function testGet()
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

    /** @testdox It can make a POST request */
    public function testPost()
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => ['Content-Type: application/x-www-form-urlencoded'],
                'content' => http_build_query(['foo' => 'bar'])
            ]
        ]);
        $response = file_get_contents('https://httpbin.org/post', false, $context);
        $this->assertJson($response);
        $httpBinResponse = json_decode($response);
        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }
}
