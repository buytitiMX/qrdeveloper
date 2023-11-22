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

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Añadir el menú del generador de códigos QR
function qrbuytiti_qr_menu()
{
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
function qrbuytiti_qr_display()
{
    global $wpdb;

    $url_error_message = '';

    // Comprueba si se envió el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Verifica si se hizo clic en el botón de eliminar
        if (isset($_POST['delete_qr']) && isset($_POST['qr_id'])) {
            $qr_id = absint($_POST['qr_id']);

            // Elimina el código QR de la base de datos
            $wpdb->delete(
                $wpdb->prefix . 'qrcodes',
                array('id' => $qr_id),
                array('%d')
            );
        } elseif (isset($_POST['url'])) {
            // Genera el código QR
            $url = esc_url($_POST['url']);

            // Verifica si el código QR ya existe en la base de datos
            $existing_qr = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}qrcodes WHERE url = %s", $url), ARRAY_A);

            if (!$existing_qr) {
                $qr_code = generate_qr_code($url);

                // Guarda el código QR en la base de datos
                $wpdb->insert(
                    $wpdb->prefix . 'qrcodes', // Asegúrate de que esta tabla exista en tu base de datos
                    array(
                        'url' => $url,
                        'qr_code' => $qr_code,
                    )
                );
            } else {
                // Muestra un mensaje de error si el enlace ya está en uso
                $url_error_message = 'Este link ya está en uso.';
            }
        }
    }

    // Muestra los códigos QR almacenados
    $qr_codes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qrcodes", ARRAY_A);

    echo '<div style="display: flex; flex-wrap: wrap; justify-content: center; margin-top: 10rem;">';

    foreach ($qr_codes as $code) {
        echo '<div style="margin: 10px; text-align: center;">';
        echo '<img src="' . $code['qr_code'] . '" style="height: 10rem; width: 10rem; margin:auto;">';
        echo '<p style="margin-top: 1rem;"><strong>Enlace:</strong> <a href="' . esc_url($code['url']) . '" target="_blank">' . esc_html($code['url']) . '</a></p>';
        // Añadir formulario para eliminar el código QR
        echo '<form method="POST">';
        echo '<input type="hidden" name="qr_id" value="' . esc_attr($code['id']) . '">';
        echo '<input type="submit" name="delete_qr" value="Eliminar">';
        echo '</form>';
        echo '</div>';
    }

    echo '</div>';

    // Muestra el formulario
    echo '<div style="text-align: center; margin-top: 2rem;">';
    echo '<form method="POST">';
    echo '<input type="submit" value="Generar Código QR">';
    echo '<input type="url" name="url" required style="width: 300px; height: 30px; margin-left: 10px; border: 1px solid #ccc; border-radius: 5px;">';

    // Estilos para el mensaje de error
    if (!empty($url_error_message)) {
        echo '<p style="color: white; background-color: red; width: 10rem; height: 1.5rem; margin: auto; border-radius: 5px; padding: 5px;">' . esc_html($url_error_message) . '</p>';
    }

    echo '</form>';
    echo '</div>';
}

function generate_qr_code($url)
{
    // Carga la biblioteca de Endroid QR Code
    require_once 'vendor/autoload.php';

    // Crea una nueva instancia de QR Code
    $qrCode = new \Endroid\QrCode\QrCode($url);

    // Configura las opciones del código QR
    $qrCode->setSize(300);
    $qrCode->setMargin(10);

    // Crea un constructor para establecer las dependencias
    $writer = new \Endroid\QrCode\Writer\PngWriter();

    // Obtiene un objeto de resultado que puede generar una cadena de datos URI
    $result = $writer->write($qrCode);

    // Devuelve la cadena de datos URI del código QR
    return $result->getDataUri();
}
