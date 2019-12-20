<?php

declare(strict_types=1);

namespace Akdr\Selma\Traits;

use Akdr\Selma\Navigation;

trait Browser
{

    /**
     * Make the driver browse a url
     *
     * @param string $url
     *
     * @return Navigation
     */
    public function goTo(string $url): Navigation
    {
        $this->webDriver->get($url);
        return $this;
    }

    /**
     * Return the URL which the browser is currently showing
     *
     * @return string
     */
    public function currentUrl(): string
    {
        return $this->webDriver->getCurrentURL();
    }

    /**
     * Executes javascript in the browser and returns the answer if able to.
     * 
     * @param string $script 
     * @return null|string 
     */
    public function javascript(string $script): ?string
    {
        return $this->webDriver->executeScript($script);
    }

    /**
     * sleep
     *
     * @return Navigation
     */
    public function sleep(int $ms = 300000): Navigation
    {
        usleep($ms);
        return $this;
    }

    /**
     * Make the Selenium browser scroll to a specific height. If no param is set, it will scroll all the way down.
     * @param int|null $scrollHeight
     * @return Navigation
     */
    public function scrollTo(?int $scrollHeight = null): Navigation
    {
        if ($scrollHeight == null) {
            $this->webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
        } else {
            $this->webDriver->executeScript("window.scrollTo(0,$scrollHeight);");
        }

        return $this;
    }

    /**
     * screenshot
     *
     * @return Navigation
     */
    public function screenshot(string $absolutPath): Navigation
    {
        $this->webDriver->takeScreenshot($absolutPath);
        $this->cli('Screenshot taken on ' . $this->currentUrl());
        return $this;
    }
}
