<?php
session_start();
session_unset();   // Libera todas las variables de sesión actuales
session_destroy(); // Destruye la sesión en el servidor
header("Location: index.php");
exit();
?>