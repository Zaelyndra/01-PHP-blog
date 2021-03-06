<?php

namespace Tests\Framework;

use App\Blog\BlogModule;
use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Tests\Framework\Modules\ErroredModule;
use Tests\Framework\Modules\StringModule;
use Zend\Stdlib\ResponseInterface;

class AppTest extends TestCase
{

    public function testRedirectTrailingSlash()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/demoslash/');
        $response = $app->run($request);
        $this->assertContains('/demoslash', $response->getHeader('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testBlog()
    {
        $app = new App([
            BlogModule::class
        ]);
        $request = new ServerRequest('GET', '/blog');
        $response = $app->run($request);
        $this->assertStringContainsString('<h1>Bienvenue sur le blog</h1>', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());

        $requestSingle = new ServerRequest('GET', '/article-de-test');
        $responseSingle = $app->run($requestSingle);
        $this->assertStringContainsString('<h1>Bienvenue sur l\'article article-de-test', (string)$responseSingle->getBody());
    }

    public function testThrowExeptionIdNoResponseSend()
    {
        $app = new App([
                ErroredModule::class
            ]
        );
        $request = new ServerRequest('GET', '/demo');
        $this->expectException(\Exception::class);
        $app->run($request);
    }

    public function testConvertStringToResponse()
    {
        $app = new App([
                StringModule::class
            ]
        );
        $request = new ServerRequest('GET', '/demo');
        $response = $app->run($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertStringContainsString('DEMO', (string)$response->getBody());


    }


    public function testError404()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/azeaze');
        $response = $app->run($request);
        $this->assertStringContainsString('<h1>Erreur 404</h1>', (string)$response->getBody());
        $this->assertEquals(404, $response->getStatusCode());
    }
}