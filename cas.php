<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Gérer les requêtes OPTIONS (pré-vol) pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'vendor/autoload.php';
require_once "vendor/apereo/phpcas/CAS.php";

// Détecter si l'URL contient "preprod"
$isPreprod = strpos($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'preprod') !== false;
$baseUrl = $isPreprod ? 'https://ilc.iut-acy.univ-smb.fr/preprod/' : 'https://ilc.iut-acy.univ-smb.fr/';
$redirectUrl = $isPreprod ? 'https://ilc.iut-acy.univ-smb.fr/preprod/#/login' : 'https://ilc.iut-acy.univ-smb.fr/#/login';
$homeUrl = $isPreprod ? 'https://ilc.iut-acy.univ-smb.fr/preprod/#/' : 'https://ilc.iut-acy.univ-smb.fr/#/';

phpCAS::setVerbose(true);
phpCAS::client(CAS_VERSION_2_0, "cas-uds.grenet.fr", 443, '', $baseUrl);
phpCAS::setNoCasServerValidation();

// Vérification de l'état de connexion
if (isset($_REQUEST['check_login'])) {
    $isLoggedIn = phpCAS::isAuthenticated();
    $user = $isLoggedIn ? phpCAS::getUser() : null;

    echo json_encode(['logged_in' => $isLoggedIn, 'login' => $user]);
    exit();
}

if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
    echo "<script>
            window.location.href = '" . $homeUrl . "';
          </script>";
    exit();
} else {
    phpCAS::forceAuthentication();
    $user = phpCAS::getUser();

    // Redirection vers la page de login avec stockage dans localStorage
    echo "<script>
            localStorage.setItem('login', '" . addslashes($user) . "');
            localStorage.setItem('auth', 'success');
            window.location.href = '" . $redirectUrl . "';
          </script>";
    exit();
}
?>