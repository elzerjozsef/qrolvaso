<?php
// Camera listázó PHP fájl
header('Content-Type: application/json');

// A JavaScript használatával a HTML5 kamera API elérhető, és ez nem PHP által kerül kiolvasásra.
echo json_encode(['status' => 'ok']);
?>
