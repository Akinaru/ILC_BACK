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
    // Test if localStorage is available and working
    try {
      localStorage.setItem('test', 'test');
      if (localStorage.getItem('test') === 'test') {
        console.log('localStorage is working properly');
        
        // Now try to set the actual values
        localStorage.setItem('login', '" . addslashes($user) . "');
        localStorage.setItem('auth', 'success');
        console.log('login value set:', localStorage.getItem('login'));
        console.log('auth value set:', localStorage.getItem('auth'));
      } else {
        console.error('localStorage test failed - can set but not retrieve');
      }
    } catch (e) {
      console.error('localStorage error:', e);
    }
    
    // Continue with redirection
    window.location.href = '" . $loginUrl . "';
  </script>";

    exit();
}
?>
