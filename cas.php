<?php

require 'vendor/autoload.php';

require_once "vendor/apereo/phpcas/CAS.php";

//phpCAS::setDebug();
phpCAS::setVerbose(true);

phpCAS::client(CAS_VERSION_2_0, "cas-uds.grenet.fr", 443, '', true);
phpCAS::setNoCasServerValidation();
//phpCAS::setNoCasServerValidation();

phpCAS::forceAuthentication();
echo phpCAS::getUser();

//<?php
// if (isset($_REQUEST['logout'])) {
//         phpCAS::logout();
// }
?>