<?php
session_start();  // Inicia la sesión para poder almacenar variables de sesión

// Conexión a la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'formulario_contacto';

$conn = new mysqli($host, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para sanear los datos de entrada
function sanitize_input($data) {
    $data = trim($data); // Eliminar espacios en blanco al principio y al final
    $data = stripslashes($data); // Eliminar barras invertidas
    $data = htmlspecialchars($data); // Convertir caracteres especiales en entidades HTML
    return $data;
}

// Validación del teléfono (simple, puede mejorarse dependiendo de las necesidades)
function validate_phone($phone) {
    // Validar si el teléfono es un número válido (esto puede mejorarse dependiendo del país)
    return preg_match("/^[0-9]{10}$/", $phone);
}

// Validación y saneamiento de los datos del formulario
$nombre = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
$correo = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$telefono = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
$mensaje = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

// Validación de que los campos no estén vacíos y tengan el formato correcto
if (empty($nombre) || empty($correo) || empty($telefono) || empty($mensaje)) {
    $_SESSION['error_message'] = 'Por favor, completa todos los campos.';
    header('Location: index.html'); // Redirige al formulario en index.html
    exit();
}

// Validación del correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = 'El correo electrónico no es válido.';
    header('Location: index.html'); // Redirige al formulario en index.html
    exit();
}

// Validación del teléfono (asegurándose de que sea un número de 10 dígitos, por ejemplo)
if (!validate_phone($telefono)) {
    $_SESSION['error_message'] = 'El número de teléfono no es válido. Debe tener 10 dígitos.';
    header('Location: index.html');
    exit();
}

// Preparar la consulta (ya usando declaraciones preparadas)
$stmt = $conn->prepare("INSERT INTO envios (nombre, correo, telefono, mensaje) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nombre, $correo, $telefono, $mensaje);

// Ejecutar la consulta y redirigir al index
if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Tu mensaje ha sido enviado exitosamente.';
    header('Location: index.html');  // Redirige al formulario en index.html
    exit();
} else {
    $_SESSION['error_message'] = 'Hubo un problema al enviar tu mensaje. Por favor, intenta nuevamente.';
    header('Location: index.html');  // Redirige al formulario en index.html en caso de error
    exit();
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
