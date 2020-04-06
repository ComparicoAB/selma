<?php

declare(strict_types=1);

namespace Akdr\Selma;

use Akdr\Selma\Traits\Browser;
use Akdr\Selma\Traits\DOM;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;

class Navigation
{
    use DOM;
    use Browser;

    /**
     * @var RemoteWebDriver
     */
    private $webDriver;

    /**
     * @var Element|null;
     */
    private $element;

    /**
     * @var Collection|null
     */
    private $collection;

    /**
     * @var string
     * @var array;
     */
    public function __construct(string $webdriverHostURL, array $chromeOptionsArguments)
    {
        $options = new ChromeOptions();
        $options->addArguments($chromeOptionsArguments);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $options);

        $this->webDriver = RemoteWebDriver::create($webdriverHostURL, $capabilities);
    }
    
    /**
     * 
     * @param string $message 
     * @param string $color 
     * @return Navigation 
     */
    public function cli(string $message, string $color = 'green') : Navigation
    {
        error_log($message);
        return $this;
    }

    public function __destruct()
    {
        error_log('Killing Browser on exit');
        $this->webDriver->quit();
    }
}
