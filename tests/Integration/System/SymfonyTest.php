<?php

namespace adamcameron\php8\tests\Integration\System;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Response;

#[TestDox("Tests of Symfony installation")]
#[Group("slow")]
class SymfonyTest extends TestCase
{
    #[TestDox("It serves the default welcome page after installation")]
    public function testSymfonyWelcomeScreenDisplays()
    {
        $client = new Client([
            'base_uri' => 'http://nginx/'
        ]);

        $response = $client->get(
            "/",
            ['http_errors' => false]
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $html = $response->getBody();
        $document = new DOMDocument();

        // not ideal, but libxml can't handle the SVG in the Symfony logo
        $document->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);

        $xpathDocument = new DOMXPath($document);

        $hasTitle = $xpathDocument->query('/html/head/title[text() = "Welcome to Symfony!"]');
        $this->assertCount(1, $hasTitle);
    }

    /** @SuppressWarnings(PHPMD.UnusedLocalVariable) - $output */
    #[TestDox("It can run the console in a shell")]
    public function testSymfonyConsoleRuns()
    {
        $appRootDir = dirname(__DIR__, 3);

        exec("$appRootDir/bin/console --help", $output, $returnCode);

        $this->assertEquals(Command::SUCCESS, $returnCode);
    }
}
