My Login Page.

<?php

require __DIR__.'/Client.php';

$loginURL = $client->getAuthorizationUrl();

?>

<a href="<?=$loginURL?>">Authrozie</a>
