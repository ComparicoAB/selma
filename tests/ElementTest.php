<?php declare(strict_types=1);

use ComparicoAB\Selma\Navigation;
use ComparicoAB\Selma\Element;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

final class ElementTest extends TestCase
{
    protected static $navigation;
    protected static $element;

    /*
     * @depends testCanReachAWebsite
     */
    public static function setUpBeforeClass(): void
    {
        self::$navigation = new Navigation('http://localhost:4444/wd/hub', ["window-size=1920,4000", "--headless", "--disable-gpu", "--no-sandbox"]);
        self::$navigation->goTo('https://www.comparico.se/om-comparico/');
        self::$element = new Element(self::$navigation, []);
    }

    public function testCanFindH1(): void
    {
        $h1 = self::$element->set([
            'selector'=> 'h1.text-center',
            'attribute' => 'text'
        ]);

        $this->assertIsString($h1->getValue());

        $this->assertEquals(
            'Om Comparico',
            $h1->getValue()
        );
    }

    public function testGetMultipleElements(): void
    {
        $groupOfElements = self::$navigation->webDriver->findElements(WebDriverBy::cssSelector('.col-md-4:not(.col-md-push-8)'));

        $this->assertCount(6, $groupOfElements);
    }

    /*
     * @depends testGetMultipleElements
     */
    public function testCanFindSubElements(): void
    {
        $groupOfElements = self::$navigation->webDriver->findElements(WebDriverBy::cssSelector('.col-md-4:not(.col-md-push-8)'));

        foreach($groupOfElements as $subElement){
            $h3 = self::$element->set([
                'element' => $subElement,
                'selector'=> 'h3',
                'attribute' => 'text'
            ]);

            $this->assertIsString($h3->getValue());
        }
    }

    public function testCanTransformTextToInt(): void
    {
            $element = self::$element->set([
                'selector'=> 'address',
                'attribute' => 'text'
            ]);

            $this->assertIsNotInt($element->getValue());
            $this->assertIsInt($element->getValue('int'));
            $this->assertEquals(1675330556851, $element->getValue('int'));
    }

    public function testCanTransformTextToFloat(): void
    {
        $element = self::$element->set([
            'selector'=> 'address',
            'attribute' => 'text'
        ]);

        $this->assertIsNotFloat($element->getValue());
        $this->assertIsFloat($element->getValue('float'));
        $this->assertEquals(1675330556851, $element->getValue('float'));
    }



}