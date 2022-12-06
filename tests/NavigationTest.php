<?php declare(strict_types=1);

use ComparicoAB\Selma\Navigation;
use PHPUnit\Framework\TestCase;

final class NavigationTest extends TestCase
{
    protected static $navigation;

    public static function setUpBeforeClass(): void
    {
        self::$navigation = new Navigation('http://localhost:4444/wd/hub', ["window-size=1920,4000", "--headless", "--disable-gpu", "--no-sandbox"]);
    }

    public function testCanCreateABrowser(): void
    {
        $this->assertInstanceOf(
            Navigation::class,
            self::$navigation
        );
    }

    /*
     * @depends testCanCreateABrowser
     */
    public function testCanReachAWebsite(): void
    {
        self::$navigation->goTo('https://www.comparico.se/om-comparico/');

        $this->assertEquals(
            'https://www.comparico.se/om-comparico/',
            self::$navigation->currentUrl()
        );
    }

    /*
     * @depends testCanCreateABrowser
     * @depends testCanReachAWebsite
     */
    public function testCanUseJavascript(): void
    {
        $this->assertEquals(
            'Foo',
            self::$navigation->javascript('return "Foo"')
        );
    }

    /*
     * @depends testCanCreateABrowser
     */
    public function testCanUseSleep(): void
    {
        $startTime = microtime(true);
        self::$navigation->sleep(200000);
        $endTime = microtime(true);

        $this->assertGreaterThanOrEqual(
            0.2,
            $endTime-$startTime
        );
    }

    /*
     * @depends testCanCreateABrowser
     * @depends testCanReachAWebsite
     */
    public function testCanTakeScreenshot(): void
    {
        self::$navigation->screenshot(__DIR__ . '/test.png');

        $this->assertFileExists(__DIR__ . '/test.png');
        unlink(__DIR__ . '/test.png');
    }

    /*
     * @depends testCanCreateABrowser
     * @depends testCanReachAWebsite
     */
    public function testCanTakeGetSource(): void
    {
        $this->assertIsString(self::$navigation->getSource());
    }

}