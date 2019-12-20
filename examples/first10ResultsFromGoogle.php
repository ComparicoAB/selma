<?php

declare(strict_types=1);

use Akdr\Selma\Navigation;

require_once __DIR__ . '/../vendor/autoload.php';

//Replace with your own Selenium IP/URL
$browser = new Navigation('http://localhost:4444', ['--window-size=500,10000']);

$browser->goTo('http://google.com');

//Select the searchbox and fill it with the text "Selma"
$browser->selectElement('input[type="text"]')->click()->insertStringIntoElement('Selma')->pressEnter();

$results = $browser->selectElements('h3.LC20lb')->getText()->returnElementAndValue();

//Loop through the results and echo the value.
foreach($results as $result){
    echo $result['value'] . PHP_EOL;
}