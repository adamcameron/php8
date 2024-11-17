<?php

namespace adamcameron\php8\tests\Integration\Http;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[TestDox("Tests of Curl functionality")]
#[Group("slow")]
class CurlTest extends HttpTestBase
{
    #[TestDox("It can make a GET request")]
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

        $this->assertEquals(Response::HTTP_OK, curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $this->assertJson($response);
        $this->assertGitInfoIsCorrect($response);
    }

    #[TestDox("It can make a POST request")]
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

        $this->assertEquals(Response::HTTP_OK, curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $this->assertJson($response);
        $httpBinResponse = json_decode($response);

        $this->assertEquals('bar', $httpBinResponse->form->foo);
    }
}
