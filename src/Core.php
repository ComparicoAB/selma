<?php
	
	namespace Akdr\Selma;
	
	use Exception;
	use Facebook\WebDriver\Remote\RemoteWebElement;
	use Facebook\WebDriver\WebDriverBy;
	use JBZoo\Utils\Cli;
	
	class Core {
		private Navigation $navigation;
		private ?RemoteWebElement $element = null;
		private bool $visibilityOfElement = false;
		private string $selector = '';
		private array $lookForAnyOfArray;
		
		/**
		 * Element constructor.
		 *
		 * @param Navigation $navigation
		 */
		public function __construct( Navigation $navigation ) {
			$this->navigation = $navigation;
			
			return $this;
		}
		
		public function amBrowsing( string $url ): static {
			$this->navigation->goTo( $url );
			
			return $this;
		}
		
		public function see( string $selector, $webDriverBy = 'cssSelector' ): static {
			$this->selector = $selector;
			if ( ! is_null( $this->element ) ) {
				try {
					$this->element             = $this->element->findElement( WebDriverBy::$webDriverBy( $selector ) );
					$this->visibilityOfElement = $this->element->isDisplayed();
				} catch ( Exception $e ) {
					$this->navigation->cli( 'Could not find child element ' . $this->selector . '. Setting Element selected to null' );
					$this->element             = null;
					$this->visibilityOfElement = false;
				}
				
				return $this;
			}
			
			try {
				$this->element             = $this->navigation->webDriver->findElement( WebDriverBy::$webDriverBy( $selector ) );
				$this->visibilityOfElement = $this->element->isDisplayed();
			} catch ( Exception $e ) {
				$this->navigation->cli( 'Could not find ' . $this->selector . '. Setting Element selected to null' );
				$this->element             = null;
				$this->visibilityOfElement = false;
			}
			
			return $this;
		}
		
		public function seeMultiple( string $selector, $webDriverBy = 'cssSelector' ): static {
			$this->selector = $selector;
			if ( ! is_null( $this->element ) ) {
				$this->elements = $this->element->findElements( WebDriverBy::$webDriverBy( $selector ) );
				
				return $this;
			}
			
			$this->elements = $this->navigation->webDriver->findElements( WebDriverBy::$webDriverBy( $selector ) );
			
			return $this;
		}
		
		public function am( ?RemoteWebElement $remote_web_element = null ): static {
			$this->reset();
			if ( $remote_web_element instanceof RemoteWebElement ) {
				$this->element = $remote_web_element;
			} else {
				$this->element = null;
			}
			
			return $this;
		}
		
		/**
		 * @throws Exception
		 */
		public function type( string $inputString ): static {
			if ( is_null( $this->element ) ) {
				throw new Exception( "No element selected, cannot type into nothing." );
			}
			
			$this->element->sendKeys( $inputString );
			
			return $this;
		}
		
		/**
		 * @throws Exception
		 */
		public function clickedIt( bool $optional = false ): static {
			if ( is_null( $this->element ) && ! $optional ) {
				throw new Exception( "No element selected, set \$optional to true." );
			}
			try {
				if ( ! is_null( $this->element ) ) {
					$this->element->click();
				} else {
					error_log( "Element with selector {$this->selector} was not clicked, continuing." );
				}
			} catch ( Exception $e ) {
				error_log( "Element with selector {$this->selector} was not clicked" );
			}
			
			
			return $this;
		}
		
		public function sleepFor( int $milliseconds ): static {
			usleep( $milliseconds );
			
			return $this;
		}
		
		public function reset(): static {
			$this->element             = null;
			$this->selector            = '';
			$this->visibilityOfElement = false;
			$this->lookForAnyOfArray   = [];
			
			return $this;
		}
		
		public function and(): static {
			return $this;
		}
		
		public function waitingFor( string $selector, int $milliseconds ): static {
			$startTime = microtime( true );
			$msInFloat = $milliseconds / 1_000_000;
			do {
				$timeSinceStart = microtime( true ) - $startTime;
				if ( $timeSinceStart > $msInFloat ) {
					break;
				}
				
				$this->sleepFor( 1000 );
				try {
					$this->element = $this->navigation->webDriver->findElement( WebDriverBy::cssSelector( $selector ) );
					if ( ! $this->element->isDisplayed() ) {
						$this->element = null;
					}
				} catch ( Exception $e ) {
					$this->element = null;
				}
				
				$waitingElement = $this->element;
			} while ( is_null( $waitingElement ) );
			
			$endTime = microtime( true );
			$status  = ( is_null( $this->element ) ) ? 'Not found' : 'Found';
			$this->navigation->cli( '##### WaitingForElement Info ######' );
			$this->navigation->cli( 'Waited for: ' . ( $endTime - $startTime ) . ' seconds.' );
			$this->navigation->cli( 'Total waiting time approved: ' . $msInFloat . ' seconds.' );
			$this->navigation->cli( 'cssSelector: ' . $selector );
			$this->navigation->cli( 'Status: ' . $status );
			$this->navigation->cli( '#########################' );
			
			return $this;
		}
		
		public function lookForAnyOf( array $selectors, int $milliseconds ): static {
			$this->lookForAnyOfArray = [];
			//TODO: Replace with a waitingFor concept instead
			$this->sleepFor( $milliseconds );
			foreach ( $selectors as $selector ) {
				try {
					if ( $this->navigation->webDriver
						->findElement( WebDriverBy::cssSelector( $selector ) ) ) {
						$this->lookForAnyOfArray[] = $selector;
					}
				} catch ( Exception $e ) {
				}
			}
			
			return $this;
		}
		
		public function returnWhich(): array {
			return array_filter( $this->lookForAnyOfArray );
		}
		
		public function returnElement(): ?RemoteWebElement {
			return $this->element;
		}
		
		public function returnElements(): array {
			return $this->elements;
		}
		
		public function elementExists(): bool {
			return ! is_null( $this->element );
		}
		
		public function returnValue( string $value ): ?string {
			if ( ! is_null( $this->element ) ) {
				return ( $value == 'text' ) ? $this->element->getText() : $this->element->getAttribute( $value );
			}
			$this->reset();
			
			return null;
		}
	}