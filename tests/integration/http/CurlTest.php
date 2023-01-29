<?php

namespace adamcameron\php8\tests\Integration\Http;

/** @testdox Tests of Curl functionality */
class CurlTest extends HttpTest
{
    /** @testdox It can make a GET request */
    public function testGet()
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

    /** @testdox It can make a POST request */
    public function testPost()
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
}
