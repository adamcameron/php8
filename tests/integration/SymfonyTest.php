<?php

namespace adamcameron\php8\tests\integration;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/** @testdox Tests of Symfony installation */
class SymfonyTest extends TestCase
{
    /** @testdox It serves the default welcome page after installation */
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
        $document = new \DOMDocument();

        // not ideal, but libxml can't handle the SVG in the Symfony logo
        $document->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);

        $xpathDocument = new \DOMXPath($document);

        $hasTitle = $xpathDocument->query('/html/head/title[text() = "Welcome to Symfony!"]');
        $this->assertCount(1, $hasTitle);
    }

    /**
     * @testdox It can run the console in a shell
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) - $output
     */
    public function testSymfonyConsoleRuns()
    {
        $appRootDir = dirname(__DIR__, 2);

        exec("{$appRootDir}/bin/console --help", $output, $returnCode);

        $this->assertEquals(0, $returnCode);
    }
}
