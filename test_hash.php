<?php
$mot = "passer123";
$hash = '$2y$10$rJyz2zOCnuBYTyG7RVOxleJSh0XdQ12V8KQpPIrOpxjZgMSNkDOzu';

if (password_verify($mot, $hash)) {
    echo "OK : le mot de passe correspond.";
} else {
    echo "NOPE : le mot de passe ne correspond pas.";
}
