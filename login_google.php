<?php
session_start();
require_once 'vendor/autoload.php';

// Reemplaza con tus credenciales de Google Cloud
$clientID = '912634052705-lumsb838md99m7nucvlbqjrm5bs4js6v.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/sistemas-servidores-Rob3-2026/login_google.php';

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

$client->setPrompt('select_account');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;
        $name = $google_account_info->name;

        // Validación de dominio institucional
        if (str_ends_with($email, '@ies21.edu.ar')) {
            $_SESSION['user'] = $email;
            $_SESSION['nombre'] = $name;
            header('Location: dashboard.php');
            exit();
        } else {
            echo "<h3 style='color:red;'>Acceso denegado. Solo se permiten correos de IES.</h3>";
            echo "<a href='index.php'>Volver al inicio</a>";
            exit();
        }
    }
}

// Generar URL y redirigir a Google
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
?>