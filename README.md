# Selma
A PHP-Webdriver wrapper trying to simplify the usage of web-scraping. 

## Usage

To use the wrapper you need to have a Selenium Hub up and running. 

To set it up, google or use the [docker-compose.yml](docker/docker-compose.yml) in the docker directory.
Instructions how to start it once you have docker installed is in the file.

### Navigation

Navigation handles the browser navigation and manipulation. Its used by the Element class and needs to be started before you try to scrape.

```php
// Example of starting a navigation. The first argument is the location of Selenium Hub and 
// the second is Chrome-options.
use Akdr\Selma\Navigation;
$nav = new Navigation('http://localhost:4444/wd/hub', ["window-size=1920,4000", "--headless", "--disable-gpu", "--no-sandbox"]);
````

##### Available methods:

| Method | Arguments | Comment | Return type |
| ---- | ---- | ------ | ----- |
| goTo | String | Make the browser go to the page in first argument. | Navigation |
| currentUrl | none | Returns the current URL as a string. | String |
| javascript | String | Executes javascript in the browser and returns it output. | ?String |
| scrollTo | ?Int | Scrolls the page to the first arguments X-value. If omitted is scrolls to the bottom. | Navigation |
| screenshot | String | Takes a screenshot of the page and saves it to the absolute path from the first argument. | Navigation |
| getSource | none | Returns the source from the current URL as a string. | String |
| cli | String | Prints a message in the error-log | Navigation |

### Element

The Element class handles everything DOM-related. It searches for DOM-elements, extracts text, filling in inputs and clicking elements.

```php
// Example of using the Element to fill out a form and then clicking the submit button.
use Akdr\Selma\Element;
use Akdr\Selma\Navigation;

$nav = new Navigation('http://localhost:4444/wd/hub', ["window-size=1920,4000", "--headless", "--disable-gpu", "--no-sandbox"]);

// First time we need to initiate the Element to use our browser, 
// later we can keep using it with the method Set.

// Enter the text "Selma is being used" into the input.
$element = new Element($nav, [
    'selector' => '#form-input',
    'input' => "Selma is being used"
]);

// Click the submit button
$element->set([
    'selector' => '#submit-button',
    'click' => true,
    'delay' => 400000
]);

//Select the response, which is a span without a class or id inside a container.

$container = $element->set([
    'selector' => '.container'
]);

$response = $element->set([
    'element' => $container->element,
    'selector' => 'span',
    'attribute' => 'text'
]);

// Finally, read the response and get the integer while removing the rest of the text.
$response->getValue('int');
```


##### Available methods
To get an Element and manipulate it, you can either use the construct or the set() method. They both takes an array as argument and is executed in the order being presented. The selector key has to be set and always (with the exception of 'element') be first.

| Key | Value-type | Comment |
| ---- | ---- | ------ |
| element | Facebook\WebDriver\ Remote\RemoteWebElement | If you need to find a multiple elements and their offspring, you can inject a RemoteWebElement and find its children through 'selector' |
| selector | String | Gets the first matching selector with the matching CSS-selector. If the key 'element' is set, it will search inside that Element. |
| attribute | String | Gets the attribute of the 'selector'. 'text' for the text, 'href' for the link and so on. |
| click | Bool | If true, the browser will try and click the 'selector' element. |
| class | String | Sets the property $hasClass to true if the 'selector' element has the CSS-class, else false. |
| input | String | Prints the argument into the 'selector' element. |
| pressKey | String | Presses the button passed in the argument, uses constants found [here](http://apigen.juzna.cz/doc/facebook/php-webdriver/class-WebDriverKeys.html) |
| delay | Int | Makes the browser sleep for the amount of milliseconds in the argument |
| waitForElement | Int | Waits for an element to appear in the browser. The argument sets amount of 0.01 seconds delays it is going to wait. |

To get the value from attribute, chain ->getValue('int'|'float'|null) on to the construct or set().

##### Public properties

If you need to retrieve the RemoteWebElement, call for the property inside the class named 'element'.

If you need to retrieve the class-bool, call for the property 'hasClass'.

Example:
```php
use Akdr\Selma\Navigation;
use Akdr\Selma\Element;

// Setup the browser and initiate the element class.
$nav = new Navigation('http://localhost:4444/wd/hub', ["window-size=1920,4000", "--headless", "--disable-gpu", "--no-sandbox"]);
$element = new Element($nav, []);

// <a href="https://comparico.se class="title">Title Number 3.14</a>
$title = $element->set([
    'selector' => 'a',
    'hasClass' => 'title',
    'attribute' => 'href'
]);

// Returns the RemoteWebElement
$title->element; // Facebook\WebDriver\Remote\RemoteWebElement

// Returns the class-bool
$title->hasClass; // true

// Fetch the attribute
$title->getValue(); // Title Number 3.14
$title->getValue('int'); // 314
$title->getValue('float'); // 3.14
```