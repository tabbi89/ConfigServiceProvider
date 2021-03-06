<?php

/**
 * This file is part of ConfigServiceProvider.
 *
 * (c) Tomasz Lopusiewicz <tomasz.pobieralnia@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author (c) Igor Wiedler <igor@wiedler.ch>
 * @see https://github.com/igorw/ConfigServiceProvider
 */

use Silex\Application;
use Tabbi\Silex\ConfigServiceProvider;
use Tabbi\Silex\ChainConfigDriver;
use Tabbi\Silex\PhpConfigDriver;
use Tabbi\Silex\YamlConfigDriver;
use Tabbi\Silex\JsonConfigDriver;
use Tabbi\Silex\TomlConfigDriver;
use Tabbi\Silex\Config;

/**
 * Test Config access
 * 
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Jérôme Macias <jerome.macias@gmail.com>
 * @author Tomasz Łopusiewicz <tomasz.pobieralnia@gmail.com>
 */
class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideEmptyFilenames
     */
    public function testEmptyConfigs($filename)
    {
        $driver = new Config(new ChainConfigDriver(array(
            new PhpConfigDriver(),
            new YamlConfigDriver(),
            new JsonConfigDriver(),
            new TomlConfigDriver(),
        )));

        $readConfigMethod = new \ReflectionMethod($driver, 'readConfig');
        $readConfigMethod->setAccessible(true);

        $this->assertEquals(array(), $readConfigMethod->invokeArgs($driver, array($filename)));
    }

    /**
     * @dataProvider provideFilenames
     */
    public function testConfigs($filename)
    {
        $app = new Application();
        $app->register(new ConfigServiceProvider());
        $app['config']->add($filename);

        $this->assertEquals("pdo_mysql", $app['config']->get('config_base.db.driver'));
    }

    /**
     * @dataProvider provideFilenames
     */
    public function testNoExsistingKey($filename)
    {
        $app = new Application();
        $app->register(new ConfigServiceProvider());
        $app['config']->add($filename);

        $this->assertNull($app['config']->get('config_base.db.driver.ble.ble.ble'));
    }

    /**
     * @group autoload
     */
    public function testAutoload()
    {
        $app = new Application();
        $app->register(new ConfigServiceProvider(__DIR__."/Fixtures/Autoload/"));
        $this->assertEquals("test", $app['config']->get('db.test'));
        $this->assertEquals("test2", $app['config']->get('db2.test'));
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Invalid JSON provided "Syntax error" in
     */
    public function invalidJsonShouldThrowException()
    {
        $app = new Application();
        $app->register(new ConfigServiceProvider());
        $app['config']->add(__DIR__."/Fixtures/broken.json");
    }

    /**
     * @test
     * @expectedException Symfony\Component\Yaml\Exception\ParseException
     */
    public function invalidYamlShouldThrowException()
    {
        $app = new Application();
        $app->register(new ConfigServiceProvider());
        $app['config']->add(__DIR__."/Fixtures/broken.yml");
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function invalidTomlShouldThrowException()
    {
        $app = new Application();
        $app->register(new ConfigServiceProvider());
        $app['config']->add(__DIR__."/Fixtures/broken.toml");
    }

    public function provideFilenames()
    {
        return array(
            array(__DIR__."/Fixtures/config_base.php"),
            array(__DIR__."/Fixtures/config_base.json"),
            array(__DIR__."/Fixtures/config_base.yml"),
            array(__DIR__."/Fixtures/config_base.toml"),
        );
    }

    public function provideEmptyFilenames()
    {
        return array(
            array(__DIR__."/Fixtures/config_empty.php"),
            array(__DIR__."/Fixtures/config_empty.json"),
            array(__DIR__."/Fixtures/config_empty.yml"),
            array(__DIR__."/Fixtures/config_empty.toml"),
        );
    }

    public function provideMergeFilenames()
    {
        return array(
            array(__DIR__."/Fixtures/config_base.php", __DIR__."/Fixtures/config_extend.php"),
            array(__DIR__."/Fixtures/config_base.json", __DIR__."/Fixtures/config_extend.json"),
            array(__DIR__."/Fixtures/config_base.yml", __DIR__."/Fixtures/config_extend.yml"),
        );
    }
}