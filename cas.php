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

use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/'); // si le .env est à la racine Laravel
    $dotenv->load();
} catch (Exception $e) {
    http_response_code(500);
    echo "Erreur lors du chargement du fichier .env : " . $e->getMessage();
    exit();
}

session_start();

$pass = $_ENV['ADMIN_PASSWORD'] ?? null;

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
    $casLogged = phpCAS::isAuthenticated();
    $casUser = $casLogged ? phpCAS::getUser() : null;

    if (!$casLogged && isset($_SESSION['custom_user'])) {
        echo json_encode(['logged_in' => true, 'login' => $_SESSION['custom_user']]);
    } else {
        echo json_encode(['logged_in' => $casLogged, 'login' => $casUser]);
    }
    exit();
}

// --- MODE ADMIN (bypass CAS) ---
$redirectParam = isset($_REQUEST['redirect']) ? urldecode($_REQUEST['redirect']) : '';

$isAdmin = (isset($_GET['admin']) && $_GET['admin'] === 'true');
$containsIutAcy = strpos($redirectParam, 'iut-acy') !== false;

if (
    $isAdmin
    || ! $containsIutAcy
) {

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $pass = $_ENV['ADMIN_PASSWORD'] ?? null;
        if ($username === 'gallottm' && password_verify($password, $pass)) {
            $_SESSION['custom_user'] = 'gallottm';
            $baseUrl = getBaseUrl();
            $loginUrl = $baseUrl . "login";
        
            // Redirection vers la page de login avec stockage dans localStorage
            echo "<script>
                localStorage.setItem('login', '" . addslashes("gallottm") . "');
                localStorage.setItem('auth', 'success');
                window.location.href = '" . $loginUrl . "';
            </script>"; 
        
            exit();
        } else {
            $error = "Identifiants invalides.";
        }
    }

    // Affichage du formulaire
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Connexion Admin</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdn.jsdelivr.net/npm/daisyui@4.0.7/dist/full.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="flex items-center justify-center h-screen bg-base-200">
        <div class="card w-96 bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Connexion Admin</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-control">
                        <label class="label">Nom d'utilisateur</label>
                        <input type="text" name="username" class="input input-bordered" required />
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">Mot de passe</label>
                        <input type="password" name="password" class="input input-bordered" required />
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}




if (isset($_REQUEST['logout'])) {
    $baseUrl = getBaseUrl();

    // Si utilisateur connecté via fake admin
    if (isset($_SESSION['custom_user'])) {
        // Supprime la session
        unset($_SESSION['custom_user']);
        session_destroy();
        echo "<script>
            window.location.href = '" . $baseUrl . "';
        </script>";
        exit();
    }

    // Sinon CAS normal
    phpCAS::logout();
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
        localStorage.setItem('login', '" . addslashes($user) . "');
        localStorage.setItem('auth', 'success');
        window.location.href = '" . $loginUrl . "';
      </script>"; 

    exit();
}
?>
