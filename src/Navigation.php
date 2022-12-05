<?php

declare( strict_types=1 );

namespace ComparicoAB\Selma;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;

class Navigation {
	/**
	 * @var RemoteWebDriver
	 */
	public $webDriver;

	/**
	 * @var string
	 * @var array;
	 */
	public function __construct( string $webdriverHostURL, array $chromeOptionsArguments ) {
		$options = new ChromeOptions();
		$options->addArguments( $chromeOptionsArguments );

		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability( ChromeOptions::CAPABILITY_W3C, $options );
		$capabilities->setPlatform( "Linux" );
		$this->webDriver = RemoteWebDriver::create( $webdriverHostURL, $capabilities );
	}

	/**
	 * Make the driver browse a url
	 *
	 * @param string $url
	 *
	 * @return Navigation
	 */
	public function goTo( string $url ): Navigation {
		$this->webDriver->get( $url );

		return $this;
	}

	/**
	 * Return the URL which the browser is currently showing
	 *
	 * @return string
	 */
	public function currentUrl(): string {
		return $this->webDriver->getCurrentURL();
	}

	/**
	 * Executes javascript in the browser and returns the answer if able to.
	 *
	 * @param string $script
	 *
	 * @return null|string
	 */
	public function javascript( string $script ) {
		return $this->webDriver->executeScript( $script );
	}

	/**
	 * sleep
	 *
	 * @param int $ms
	 *
	 * @return Navigation
	 */
	public function sleep( int $ms = 300000 ): Navigation {
		usleep( $ms );

		return $this;
	}

	/**
	 * Make the Selenium browser scroll to a specific height. If no param is set, it will scroll all the way down.
	 *
	 * @param int|null $scrollHeight
	 *
	 * @return Navigation
	 */
	public function scrollTo( ?int $scrollHeight = null ): Navigation {
		if ( $scrollHeight == null ) {
			$this->webDriver->executeScript( 'window.scrollTo(0,document.body.scrollHeight);' );
		} else {
			$this->webDriver->executeScript( "window.scrollTo(0,$scrollHeight);" );
		}

		return $this;
	}

	/**
	 * screenshot
	 *
	 * @param string $absolutPath
	 *
	 * @return Navigation
	 */
	public function screenshot( string $absolutPath ): Navigation {
		$this->webDriver->takeScreenshot( $absolutPath );
		$this->cli( 'Screenshot taken on ' . $this->currentUrl() );

		return $this;
	}

	public function getSource(): string {
		return $this->webDriver->getPageSource();
	}

	/**
	 *
	 * @param string $message
	 *
	 * @return Navigation
	 */
	public function cli( string $message ): Navigation {
		error_log( $message );

		return $this;
	}

	public function __destruct() {
		error_log( 'Killing Browser on exit' );
		$this->webDriver->quit();
	}
}
