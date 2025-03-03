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

function getBaseUrl() {
    // Récupérer le paramètre redirect s'il existe
    if (isset($_REQUEST['redirect']) && !empty($_REQUEST['redirect'])) {
        $redirectUrl = urldecode($_REQUEST['redirect']);
        
        // Extraire tout ce qui est avant le contenu du fragment (après #)
        $hashPosition = strpos($redirectUrl, '#');
        
        if ($hashPosition !== false) {
            // +1 pour inclure le # lui-même
            $baseUrl = substr($redirectUrl, 0, $hashPosition + 1);
            return $baseUrl;
        }
        
        // Si pas de #, retourner l'URL complète avec # ajouté
        return $redirectUrl . '#';
    }
    
    // URL par défaut si pas de paramètre redirect
    return 'https://ilc.iut-acy.univ-smb.fr/#';
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
    $baseUrl = getBaseUrl();
        echo "<script>
        window.location.href = '" . $baseUrl . "';
        </script>";
    exit();
} else {
    phpCAS::forceAuthentication();
    $user = phpCAS::getUser();
    $baseUrl = getBaseUrl();
    $loginUrl = $baseUrl . "login";

    // Redirection vers la page de login avec stockage dans localStorage
    echo "<script>
    // Try both localStorage and sessionStorage
    try {
      localStorage.setItem('login', '" . addslashes($user) . "');
      localStorage.setItem('auth', 'success');
    } catch (e) {
      console.error('localStorage error:', e);
    }
    
    try {
      sessionStorage.setItem('login', '" . addslashes($user) . "');
      sessionStorage.setItem('auth', 'success');
    } catch (e) {
      console.error('sessionStorage error:', e);
    }
    
    window.location.href = '" . $loginUrl . "';
  </script>";

    exit();
}
?>
