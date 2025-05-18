<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Gérer les requêtes OPTIONS (pré-vol) pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

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
    return 'https://gallotta.fr/ilc/#';
}

// Vérification de l'état de connexion
if (isset($_REQUEST['check_login'])) {
    $isLoggedIn = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    $user = $isLoggedIn ? $_SESSION['user'] : null;

    echo json_encode(['logged_in' => $isLoggedIn, 'login' => $user]);
    exit();
}

if (isset($_REQUEST['logout'])) {
    // Déconnexion
    session_destroy();
    $baseUrl = getBaseUrl();
    echo "<script>
    window.location.href = '" . $baseUrl . "';
    </script>";
    exit();
} elseif (isset($_POST['password'])) {
    // Vérification du mot de passe
    $correctPassword = "Maxime123";
    
    if ($_POST['password'] === $correctPassword) {
        // Mot de passe correct, on simule l'authentification
        $_SESSION['authenticated'] = true;
        $_SESSION['user'] = 'gallottm';
        
        $baseUrl = getBaseUrl();
        $loginUrl = $baseUrl . "login";
        
        echo "<script>
        localStorage.setItem('login', 'gallottm');
        localStorage.setItem('auth', 'success');
        window.location.href = '" . $loginUrl . "';
        </script>";
    } else {
        // Mot de passe incorrect, afficher un message d'erreur
        $errorMessage = "Mot de passe incorrect. Veuillez réessayer.";
    }
} else {
    // Afficher le formulaire de connexion
    $errorMessage = "";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification CAS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            width: 320px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin: 0 0 25px 0;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #4285f4;
            outline: none;
        }
        .btn {
            background-color: #4285f4;
            color: white;
            padding: 12px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 15px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #3367d6;
        }
        .error {
            color: #d93025;
            font-size: 13px;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #fce8e6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo retiré -->
        <h2>Authentification</h2>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur:</label>
                <input type="text" id="username" name="username" value="gallottm" readonly style="background-color: #f9f9f9;">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
        
        <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #777; padding: 10px; border: 1px solid #eee; border-radius: 4px; background-color: #f9f9f9;">
            <p><strong>Note importante :</strong> Ceci est une version simulée du système d'authentification, créée uniquement pour les besoins de démonstration pédagogique. Elle n'est pas reliée au véritable service LCAS et respecte les réglementations administratives et juridiques en vigueur.</p>
        </div>
    </div>
</body>
</html>