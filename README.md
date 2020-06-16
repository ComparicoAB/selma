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

Available methods:

| Method | Arguments | Comment | Return type |
| ---- | ---- | ------ | ----- |
| goTo | string | Make the browser go to the page in first argument. | Navigation |
| currentUrl | none | Returns the current URL as a string. | String |
| javascript | string | Executes javascript in the browser and returns it output. | ?String |
| scrollTo | ?int | Scrolls the webpage to the first arguments X-value. If omitted is scrolls to the bottom. | Navigation |
| screenshot | string | Takes a screenshot of the webpage and saves it to the absolute path from the first argument. | Navigation |
| getSource | none | Returns the source from the current URL as a string. | String |
| cli | string | Prints a message in the error-log | Navigation |

### Element

The Element class handles everything DOM-related. It searches for DOM-elements, extracts text, filling in inputs and clicking elements.

```php
// Example of using the Element to fill out a form and then clicking the submit button.
use Akdr\Selma\Element;

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

Methods and variables will be updated in the future for Element.