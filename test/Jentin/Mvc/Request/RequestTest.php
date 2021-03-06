<?php
/*
 * This file is part of the Jentin framework.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Jentin\Mvc;

use Jentin\Mvc\Request\Request;
use PHPUnit\Framework\TestCase;

/**
 * RequestTest
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class RequestTest extends TestCase
{

    /**
     * @dataProvider provideGetBaseUrl
     *
     * @param   array   $server
     * @param   string  $expectedBaseUrl
     */
    public function testGetBaseUrl(array $server, $expectedBaseUrl)
    {
        $request = new Request([], [], $server);
        $this->assertEquals($expectedBaseUrl, $request->getBaseUrl());
    }


    /**
     * @return array[]
     */
    public function provideGetBaseUrl()
    {
        return [
            'request uri contains script file' => [
                ['REQUEST_URI' => '/test/abc/123/test.php'],
                '/test/abc/123/'
            ],
            'request uri contains only path with slash at the end' => [
                ['REQUEST_URI' => '/test/abc/123/'],
                '/test/abc/123/'
            ],
            'request uri contains only path without slash at the end' => [
                ['REQUEST_URI' => '/test/abc/123'],
                '/test/abc/'
            ],
            'request uri contains script name and query string' => [
                ['REQUEST_URI' => '/test/abc/123/info.php?test=123&foo=on'],
                '/test/abc/123/'
            ],
            'request uri contains query string' => [
                ['REQUEST_URI' => '/test/abc/123?test=123&foo=on'],
                '/test/abc/'
            ],
            'request uri contains query string and anchor' => [
                ['REQUEST_URI' => '/test/abc/123?test=123&foo=on#test'],
                '/test/abc/'
            ],
            'request uri contains anchor' => [
                ['REQUEST_URI' => '/test/abc/123#test'],
                '/test/abc/'
            ],
            'request uri contains base path' => [
                [
                    'REQUEST_URI' => '/test/abc/123/',
                    'SCRIPT_NAME' => '/test/abc/test.php'
                ],
                '/test/abc/'
            ],
            'script with routing within document root' => [
                [
                    'REQUEST_URI' => '/module/controller/action?param=1',
                    'SCRIPT_NAME' => '/test.php'
                ],
                '/'
            ]
        ];
    }


    /**
     * @dataProvider provideGetBasePath
     * @param string $serverScriptName
     * @param string $expectedBasePath
     */
    public function testGetBasePath($serverScriptName, $expectedBasePath)
    {
        $request = new Request([], [], ['SCRIPT_NAME' => $serverScriptName]);
        $this->assertEquals($expectedBasePath, $request->getBasePath());
    }


    /**
     * @return array[]
     */
    public function provideGetBasePath()
    {
        $testCases = [
            'root-path' => ['/', '/'],
            'path' => ['/path/to/script.php', '/path/to'],
        ];
        if (DIRECTORY_SEPARATOR === '\\') {
            $testCases['root-path-with-windows-directory-separator'] = ['\\', '/'];
            $testCases['path-with-windows-directory-separator'] = ['\path\to\script.php', '/path/to'];
        }
        return $testCases;
    }


    /**
     * @dataProvider provideGetHost
     *
     * @param   array   $server
     * @param   string  $expectedHost
     */
    public function testGetHost(array $server, $expectedHost)
    {
        $request = new Request([], [], $server);
        $this->assertEquals($expectedHost, $request->getHost());
    }


    /**
     * @return array[]
     */
    public function provideGetHost()
    {
        $testData = [];

        // test case 1: host by HTTP_HOST
        $testData[] = [
            ['HTTP_HOST' => 'localhost'],
            'localhost'
        ];
        // test case 2: host by SERVER_NAME
        $testData[] = [
            ['SERVER_NAME' => 'localhost'],
            'localhost'
        ];

        return $testData;
    }


    public function testGetScheme()
    {
        $server = ['HTTPS' => 'on'];
        $request = new Request([], [], $server);
        $this->assertEquals('https', $request->getScheme());
    }


    public function testPostParamsPreferredOverGetParams()
    {
        $post = ['test' => 'post'];
        $get = ['test' => 'get'];
        $request = new Request($post, $get);
        $this->assertEquals('post', $request->getParam('test'));
        $this->assertTrue($request->isGet('test'));
        $this->assertTrue($request->isPost('test'));
    }


    public function testSetPostParam()
    {
        $post = [];
        $get = ['test' => 'abc'];
        $request = new Request($post, $get);
        $request->setPostParam('test', '123');

        $this->assertEquals('123', $request->getParam('test'));
        $this->assertTrue($request->isPost('test'));
    }


    public function testSetGetParam()
    {
        $post = ['test' => 'abc'];
        $request = new Request($post);
        $request->setGetParam('test', '123');
        $request->setGetParam('abc', 'xyz');

        $this->assertEquals('abc', $request->getParam('test'));
        $this->assertTrue($request->isPost('test'));
        $this->assertEquals('xyz', $request->getParam('abc'));
        $this->assertTrue($request->isGet('abc'));
    }


    /**
     * @dataProvider provideRequestUrls
     *
     * @param string $url
     * @param array  $expected
     */
    public function testGetQuery($url, $expected)
    {
        $server = array('REQUEST_URI' => $url);
        $request = new Request([], [], $server);
        $this->assertEquals($expected['query'], $request->getQuery());
    }


    /**
     * @dataProvider provideRequestUrls
     *
     * @param string $url
     * @param array  $expected
     */
    public function testGetFragment($url, $expected)
    {
        $server = array('REQUEST_URI' => $url);
        $request = new Request([], [], $server);
        $this->assertEquals($expected['fragment'], $request->getFragment());
    }


    /**
     * @return array[]
     */
    public function provideRequestUrls()
    {
        $testCases = [];

        $testCases[] = [
            '/test/abc/action?hello=world#?_de=123',
            [
                'query' => 'hello=world',
                'fragment' => '?_de=123'
            ]
        ];

        $testCases[] = [
            '/test/abc/action',
            [
                'query' => '',
                'fragment' => ''
            ]
        ];

        $testCases[] = [
            '/test/abc/action#_dc=123',
            [
                'query' => '',
                'fragment' => '_dc=123'
            ]
        ];

        $testCases[] = [
            '/test/abc/action#_dc=123?hello=world',
            [
                'query' => '',
                'fragment' => '_dc=123?hello=world'
            ]
        ];

        $testCases[] = [
            '/test/abc/action?hello=world',
            [
                'query' => 'hello=world',
                'fragment' => ''
            ]
        ];

        $testCases[] = [
            '/test/abc/action?##hello=world',
            [
                'query' => '',
                'fragment' => '#hello=world'
            ]
        ];

        $testCases[] = [
            '/test/abc/action??##hello=world',
            [
                'query' => '?',
                'fragment' => '#hello=world'
            ]
        ];

        return $testCases;
    }


    public function testGetServer()
    {
        $serverVars = [
            'REQUEST_URI' => '/test/abc/123/test.php',
            'HTTPS' => 'on'
        ];
        $request = new Request([], [], $serverVars);

        $this->assertEquals($serverVars['REQUEST_URI'], $request->getServer('REQUEST_URI'));
        $this->assertNull($request->getServer('TEST_SOMETHING'));
        $this->assertEquals('Something', $request->getServer('TEST_SOMETHING', 'Something'));
        $this->assertInternalType('array', $request->getServer());
    }

}
