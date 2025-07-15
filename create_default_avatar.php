<?php
/**
 * Script pour créer une image d'avatar par défaut
 */

// Créer le dossier s'il n'existe pas
$dir = 'assets/img';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Créer une image simple pour l'avatar par défaut
$width = 100;
$height = 100;

$image = imagecreate($width, $height);

// Couleurs
$bg_color = imagecolorallocate($image, 0, 105, 194); // Bleu
$text_color = imagecolorallocate($image, 255, 255, 255); // Blanc

// Remplir le fond
imagefill($image, 0, 0, $bg_color);

// Ajouter du texte
$text = '👤';
$font_size = 5;

// Centrer le texte
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// Sauvegarder l'image
imagepng($image, $dir . '/default-avatar.png');
imagedestroy($image);

echo "Avatar par défaut créé avec succès dans " . $dir . "/default-avatar.png";
?>
