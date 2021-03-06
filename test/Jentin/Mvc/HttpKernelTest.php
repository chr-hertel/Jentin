<?php
/*
 * This file is part of the Jentin framework.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Jentin\Mvc;

use Jentin\Mvc\HttpKernel;
use Jentin\Mvc\Router\Router;
use PHPUnit\Framework\TestCase;

/**
 * DispatcherTest
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class HttpKernelTest extends TestCase
{

    /**
     * controller dispatcher
     * @var HttpKernel
     */
    private $httpKernel;


    protected function setUp()
    {
        $pathArr = array(FIXTURE_DIR, 'modules', '%Module%', 'controllers');
        $controllerDirs = implode(DIRECTORY_SEPARATOR, $pathArr);
        $modules = array('Default', 'Test', 'Blog');
        $router = new Router();

        $this->httpKernel = new HttpKernel($controllerDirs, $modules, $router);
    }


    public function testGetControllerPath()
    {
        $pathArr = array(FIXTURE_DIR, 'modules', 'Test', 'controllers');
        $expectedControllerPath = implode(DIRECTORY_SEPARATOR, $pathArr);
        $controllerPath = $this->httpKernel->getControllerPath('Test', 'Default');
        $this->assertEquals($expectedControllerPath, $controllerPath);
    }


    /**
     * tests that a not existing module cannot be called
     * @expectedException \Jentin\Mvc\HttpKernelException
     */
    public function testNotExistingModuleThrowsException()
    {
        $this->httpKernel->getControllerPath('Blog', 'Index');
    }


    /**
     * tests that a not existing controller cannot be called
     * @expectedException \Jentin\Mvc\HttpKernelException
     */
    public function testNotExistingControllerThrowsException()
    {
        $this->httpKernel->getControllerPath('Default', 'Blog');
    }


    /**
     * tests that an existing module cannot be called, because it wasn't defined for the dispatcher
     * @expectedException \Jentin\Mvc\HttpKernelException
     */
    public function testNotDefinedModuleThrowsException()
    {
        $this->httpKernel->getControllerPath('News', 'Index');
    }


    /**
     * @dataProvider provideGetControllerClassName
     * @param $classNamePattern
     * @param $moduleName
     * @param $controllerName
     * @param $expected
     */
    public function testGetControllerClassName($classNamePattern, $moduleName, $controllerName, $expected)
    {
        $this->httpKernel->setControllerClassNamePattern($classNamePattern);
        $actual = $this->httpKernel->getControllerClassName($moduleName, $controllerName);
        $this->assertEquals($expected, $actual);
    }


    /**
     * @return array[]
     */
    public function provideGetControllerClassName()
    {
        $testData = array();

        $testData[] = array(
            'pattern'       => '%Module%\\%Controller%Controller',
            'module'        => 'test',
            'controller'    => 'controller-test',
            'expected'      => '\\Test\\ControllerTestController'
        );

        $testData[] = array(
            'pattern'       => 'MyModules\\%Module%\\%Controller%\\%Controller%Controller',
            'module'        => 'test',
            'controller'    => 'controller-test',
            'expected'      => '\\MyModules\\Test\\ControllerTest\\ControllerTestController'
        );

        $testData[] = array(
            'pattern'       => 'MyModules\\Controller',
            'module'        => 'test',
            'controller'    => 'controller-test',
            'expected'      => '\\MyModules\\Controller'
        );

        $testData[] = array(
            'pattern'       => 'My%Controller%s\\%Module%',
            'module'        => 'test',
            'controller'    => 'controller-test',
            'expected'      => '\\MyControllerTests\\Test'
        );

        // test upper and lower chracters
        $testData[] = array(
            'pattern'       => '%Module%\\%Controller%',
            'module'        => 'tESt',
            'controller'    => 'conTroller--Test',
            'expected'      => '\\Test\\ControllerTest'
        );

        return $testData;
    }

}
