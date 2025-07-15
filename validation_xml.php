<?php
/**
 * Script de validation du fichier XML contre le DTD et le Schema XSD
 */

function validerAvecDTD($xmlFile, $dtdFile) {
    $dom = new DOMDocument();
    $dom->load($xmlFile);
    
    if ($dom->validate()) {
        return ['success' => true, 'message' => 'Validation DTD réussie'];
    } else {
        $errors = libxml_get_errors();
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->message;
        }
        return ['success' => false, 'errors' => $errorMessages];
    }
}

function validerAvecXSD($xmlFile, $xsdFile) {
    $dom = new DOMDocument();
    $dom->load($xmlFile);
    
    if ($dom->schemaValidate($xsdFile)) {
        return ['success' => true, 'message' => 'Validation XSD réussie'];
    } else {
        $errors = libxml_get_errors();
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->message;
        }
        return ['success' => false, 'errors' => $errorMessages];
    }
}

// Test de validation
$xmlFile = 'data/plateforme.xml';
$dtdFile = 'data/plateforme.dtd';
$xsdFile = 'data/plateforme.xsd';

echo "<h2>Validation du fichier XML</h2>";

// Validation DTD
echo "<h3>Validation avec DTD :</h3>";
$resultDTD = validerAvecDTD($xmlFile, $dtdFile);
if ($resultDTD['success']) {
    echo "<p style='color: green;'>" . $resultDTD['message'] . "</p>";
} else {
    echo "<p style='color: red;'>Erreurs DTD :</p>";
    foreach ($resultDTD['errors'] as $error) {
        echo "<p style='color: red;'>- " . htmlspecialchars($error) . "</p>";
    }
}

// Validation XSD
echo "<h3>Validation avec Schema XSD :</h3>";
$resultXSD = validerAvecXSD($xmlFile, $xsdFile);
if ($resultXSD['success']) {
    echo "<p style='color: green;'>" . $resultXSD['message'] . "</p>";
} else {
    echo "<p style='color: red;'>Erreurs XSD :</p>";
    foreach ($resultXSD['errors'] as $error) {
        echo "<p style='color: red;'>- " . htmlspecialchars($error) . "</p>";
    }
}
?>
