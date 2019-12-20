<?php

declare(strict_types=1);

namespace Akdr\Selma;

use Akdr\Selma\Traits\Browser;
use Akdr\Selma\Traits\DOM;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use League\CLImate\CLImate;

class Navigation
{
    use DOM;
    use Browser;

    /**
     * @var RemoteWebDriver
     */
    private $webDriver;

    /**
     * @var CLImate 
     */
    private $CLImate;

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
        #    array(
        #    '--window-size=500,10000',
        #));

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        $this->webDriver = RemoteWebDriver::create($webdriverHostURL, $capabilities);

        $this->CLImate = new CLImate;
    }
    
    /**
     * 
     * @param string $message 
     * @param string $color 
     * @return Navigation 
     */
    public function cli(string $message, string $color = 'green') : Navigation
    {
        $this->CLImate->$color($message);
        return $this;
    }

    public function __destruct()
    {
        $this->cli('Killing Browser on exit', 'cyan');
        $this->webDriver->quit();
    }
}
