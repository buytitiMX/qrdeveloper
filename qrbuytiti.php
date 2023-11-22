<?php
/**
 * Plugin Name:       Buytiti - QR
 * Plugin URI:        https://buytiti.com
 * Description:       Este plugin añade la capacidad de generar códigos QR de forma dinámica.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Jesus Jimenez
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qrbuytiti
 * Update URI:        https://buytiti.com
 *
 * @package           buytiti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Añadir el menú del generador de códigos QR
function qrbuytiti_qr_menu() {
    add_menu_page(
        'Generador de Códigos QR', 
        'Generador de Códigos QR', 
        'manage_options', 
        'qrbuytiti-qr', 
        'qrbuytiti_qr_display'
    );
}
add_action('admin_menu', 'qrbuytiti_qr_menu');

// Mostrar el generador de códigos QR
function qrbuytiti_qr_display() {
    // Comprueba si se envió el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url'])) {
        // Genera el código QR
        $url = esc_url($_POST['url']);
        $qr_code = generate_qr_code($url);  // Esta es la función que necesitas implementar

        // Muestra el código QR
        echo '<div style="display: flex; margin-top: 10rem;">';  // Añade un div con estilo para personalizar el código QR
echo '<img src="' . $qr_code . '" style="display: flex; height: 10rem; width: 10rem; margin:auto;">';
echo '</div>';
    }

    // Muestra el formulario
    echo '<div style="text-align: center;">';  // Añade un div con estilo para centrar el formulario
    echo '<form method="POST" style="margin-top: 5rem;">';
    echo '<input type="submit" value="Generar Código QR">';  // Cambia el orden del botón y el input
    echo '<input type="url" name="url" required style="width: 300px; height: 30px; margin-left: 10px; border: 1px solid #ccc; border-radius: 5px;">';  // Añade estilos al input
    echo '</form>';
    echo '</div>';
}

function generate_qr_code($url) {
    // Carga la biblioteca de Endroid QR Code
    require_once 'vendor/autoload.php';

    // Crea una nueva instancia de QR Code
    $qrCode = new \Endroid\QrCode\QrCode($url);

    // Configura las opciones del código QR
    $qrCode->setSize(300);  // Tamaño en píxeles
    $qrCode->setMargin(10);  // Margen alrededor del código QR

    // Crea un constructor para establecer las dependencias
    $writer = new \Endroid\QrCode\Writer\PngWriter();

    // Obtiene un objeto de resultado que puede generar una cadena de datos URI
    $result = $writer->write($qrCode);

    // Devuelve la cadena de datos URI del código QR
    return $result->getDataUri();  // Este es el método correcto
}
