<?php

namespace adamcameron\php8\test\integration\http;

use PHPUnit\Framework\TestCase;

/** @testdox Tests of HTTP request functionality */
abstract class HttpTest extends TestCase
{

    protected function getUserAgentForCurl(): string
    {
        return sprintf("curl/%s", curl_version()['version']);
    }

    protected function assertGitInfoIsCorrect(string $response): void
    {
        $myGitMetadata = json_decode($response);
        $this->assertEquals('adamcameron', $myGitMetadata->login);
        $this->assertEquals('Adam Cameron', $myGitMetadata->name);
    }
}
