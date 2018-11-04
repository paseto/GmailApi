<?php
/**
 * Created by PhpStorm.
 * User: giovani
 * Date: 11/4/18
 * Time: 5:02 PM
 */
require 'vendor/autoload.php';
ini_set('display_errors', 1);

$gmail = new \GmailApi\Gmail('token.json');


//$service = new Google_Service_Gmail($client);
//
//$s = $service->users->getProfile("me");

//$s = $gmail->getUser();
try {
    $message = $gmail->send('giovaniw2@gmail.com', 'Teste 2', 'subject 2');
    echo $message->getId();
} catch (Exception $e) {
    print_r($e->getMessage());
}
echo '<pre>';
print_r($message);
