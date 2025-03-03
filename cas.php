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

phpCAS::setVerbose(true);
phpCAS::client(CAS_VERSION_2_0, "cas-uds.grenet.fr", 443, '', "https://ilc.iut-acy.univ-smb.fr/");
//phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context, $client_service_name);
phpCAS::setNoCasServerValidation();

function getRedirectUrl() {
    // Récupérer le paramètre redirect s'il existe
    if (isset($_REQUEST['redirect']) && !empty($_REQUEST['redirect'])) {
        return urldecode($_REQUEST['redirect']);
    }
    
    // URL par défaut si pas de paramètre redirect
    return 'https://ilc.iut-acy.univ-smb.fr/#/';
}

// Vérification de l'état de connexion
if (isset($_REQUEST['check_login'])) {
    $isLoggedIn = phpCAS::isAuthenticated();
    $user = $isLoggedIn ? phpCAS::getUser() : null;

    echo json_encode(['logged_in' => $isLoggedIn, 'login' => $user]);
    exit();
}

if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
    $redirectUrl = getRedirectUrl();
        echo "<script>
        window.location.href = '" . $redirectUrl . "';
        </script>";
    exit();
} else {
    phpCAS::forceAuthentication();
    $user = phpCAS::getUser();
    $redirectUrl = getRedirectUrl();

    // Redirection vers la page de login avec stockage dans localStorage
        echo "<script>
        localStorage.setItem('login', '" . addslashes($user) . "');
        localStorage.setItem('auth', 'success');
        window.location.href = '" . $redirectUrl . "/login';
      </script>"; 

    exit();
}
?>
