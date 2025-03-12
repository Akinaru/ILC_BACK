<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Fonction de journalisation
function logToFile($message) {
    $logFile = 'logcas.txt';
    
    // Effacer le fichier à chaque nouvelle session
    if (!isset($_SESSION['log_initialized'])) {
        file_put_contents($logFile, '');
        $_SESSION['log_initialized'] = true;
    }
    
    // Ajouter un horodatage au message
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Écrire dans le fichier
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Démarrer la session pour suivre l'initialisation du log
session_start();
logToFile("Script démarré - " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Gérer les requêtes OPTIONS (pré-vol) pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    logToFile("Requête OPTIONS traitée");
    http_response_code(200);
    exit();
}

logToFile("Chargement des dépendances");
require 'vendor/autoload.php';
require_once "vendor/apereo/phpcas/CAS.php";

// Configuration de phpCAS
logToFile("Configuration de phpCAS");
phpCAS::setVerbose(true);
phpCAS::client(CAS_VERSION_2_0, "cas-uds.grenet.fr", 443, '', "https://ilc.iut-acy.univ-smb.fr/");
phpCAS::setNoCasServerValidation();
logToFile("Configuration phpCAS terminée");

function getBaseUrl() {
    logToFile("Calcul de l'URL de base");
    // Récupérer le paramètre redirect s'il existe
    if (isset($_REQUEST['redirect']) && !empty($_REQUEST['redirect'])) {
        $redirectUrl = urldecode($_REQUEST['redirect']);
        logToFile("Paramètre redirect trouvé: $redirectUrl");
        
        // Extraire tout ce qui est avant le contenu du fragment (après #)
        $hashPosition = strpos($redirectUrl, '#');
        
        if ($hashPosition !== false) {
            // +1 pour inclure le # lui-même
            $baseUrl = substr($redirectUrl, 0, $hashPosition + 1);
            logToFile("URL de base extraite avec #: $baseUrl");
            return $baseUrl;
        }
        
        // Si pas de #, retourner l'URL complète avec # ajouté
        $baseUrl = $redirectUrl . '#';
        logToFile("URL de base avec # ajouté: $baseUrl");
        return $baseUrl;
    }
    
    // URL par défaut si pas de paramètre redirect
    $baseUrl = 'https://ilc.iut-acy.univ-smb.fr/#';
    logToFile("URL de base par défaut utilisée: $baseUrl");
    return $baseUrl;
}

// Vérification de l'état de connexion
if (isset($_REQUEST['check_login'])) {
    logToFile("Vérification de l'état de connexion");
    $isLoggedIn = phpCAS::isAuthenticated();
    $user = $isLoggedIn ? phpCAS::getUser() : null;
    
    $response = ['logged_in' => $isLoggedIn, 'login' => $user];
    logToFile("Réponse check_login: " . json_encode($response));
    
    echo json_encode($response);
    exit();
}

if (isset($_REQUEST['logout'])) {
    logToFile("Déconnexion demandée");
    
    phpCAS::logout();
    $baseUrl = getBaseUrl();
    logToFile("Redirection après déconnexion vers: $baseUrl");
    
    echo "<script>
    window.location.href = '" . $baseUrl . "';
    </script>";
    exit();
} else {
    logToFile("Authentification forcée");
    
    phpCAS::forceAuthentication();
    $user = phpCAS::getUser();
    logToFile("Utilisateur authentifié: $user");
    
    $baseUrl = getBaseUrl();
    $loginUrl = $baseUrl . "login";
    logToFile("Redirection vers: $loginUrl");

    // Redirection vers la page de login avec stockage dans localStorage
    echo "<script>
    localStorage.setItem('login', '" . addslashes($user) . "');
    localStorage.setItem('auth', 'success');
    window.location.href = '" . $loginUrl . "';
    </script>"; 

    exit();
}
?>