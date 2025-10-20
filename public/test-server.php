<?php
/**
 * Script de diagnóstico para verificar la configuración del servidor
 * Subir este archivo a: public/test-server.php
 * Acceder desde: https://monkits.com/inventario/test-server.php
 */

echo "<h1>Diagnóstico del Servidor - Laravel en Subdirectorio</h1>";
echo "<hr>";

// 1. Verificar PHP
echo "<h2>✓ PHP Version</h2>";
echo "<p>PHP: " . phpversion() . "</p>";

// 2. Verificar extensiones necesarias
echo "<h2>✓ Extensiones PHP</h2>";
$required = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>{$status} {$ext}</p>";
}

// 3. Verificar mod_rewrite (Apache)
echo "<h2>✓ Apache mod_rewrite</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $rewrite = in_array('mod_rewrite', $modules) ? '✅ HABILITADO' : '❌ DESHABILITADO';
    echo "<p>{$rewrite}</p>";
} else {
    echo "<p>⚠️ No se puede verificar (posible Nginx o CGI)</p>";
}

// 4. Verificar .htaccess
echo "<h2>✓ Archivo .htaccess</h2>";
$htaccess = __DIR__ . '/.htaccess';
if (file_exists($htaccess)) {
    echo "<p>✅ Existe</p>";
    echo "<details><summary>Ver contenido</summary>";
    echo "<pre>" . htmlspecialchars(file_get_contents($htaccess)) . "</pre>";
    echo "</details>";
} else {
    echo "<p>❌ NO EXISTE - ¡Esto es un problema!</p>";
}

// 5. Verificar permisos
echo "<h2>✓ Permisos de Directorios</h2>";
$dirs = [
    '../storage' => is_writable(__DIR__ . '/../storage'),
    '../bootstrap/cache' => is_writable(__DIR__ . '/../bootstrap/cache'),
];
foreach ($dirs as $dir => $writable) {
    $status = $writable ? '✅ Escribible' : '❌ NO escribible';
    echo "<p>{$status} {$dir}</p>";
}

// 6. Verificar variables de entorno
echo "<h2>✓ Variables de Servidor</h2>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>SCRIPT_FILENAME:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

// 7. Verificar .env
echo "<h2>✓ Archivo .env</h2>";
$env = __DIR__ . '/../.env';
if (file_exists($env)) {
    echo "<p>✅ Existe</p>";
    $content = file_get_contents($env);
    preg_match('/APP_ENV=(.+)/', $content, $app_env);
    preg_match('/APP_URL=(.+)/', $content, $app_url);
    preg_match('/APP_DEBUG=(.+)/', $content, $app_debug);

    echo "<p><strong>APP_ENV:</strong> " . ($app_env[1] ?? 'no encontrado') . "</p>";
    echo "<p><strong>APP_URL:</strong> " . ($app_url[1] ?? 'no encontrado') . "</p>";
    echo "<p><strong>APP_DEBUG:</strong> " . ($app_debug[1] ?? 'no encontrado') . "</p>";
} else {
    echo "<p>❌ NO EXISTE</p>";
}

// 8. Test de rewrite
echo "<h2>✓ Test de URL Rewriting</h2>";
echo "<p>Si puedes ver este archivo, significa que:</p>";
echo "<ul>";
echo "<li>✅ PHP está funcionando</li>";
echo "<li>✅ El servidor puede ejecutar archivos .php</li>";
echo "</ul>";
echo "<p><strong>Prueba la ruta de Laravel:</strong></p>";
echo "<p>Accede a: <a href='/inventario/dashboard'>/inventario/dashboard</a></p>";
echo "<p>Si da 404 o error de 'archivo no encontrado', el .htaccess NO está funcionando.</p>";

// 9. Verificar index.php
echo "<h2>✓ Archivo index.php</h2>";
$index = __DIR__ . '/index.php';
if (file_exists($index)) {
    echo "<p>✅ Existe en: {$index}</p>";
} else {
    echo "<p>❌ NO EXISTE</p>";
}

echo "<hr>";
echo "<h2>📋 Resumen de Problemas</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<p><strong>Si ves estos problemas:</strong></p>";
echo "<ul>";
echo "<li>❌ mod_rewrite deshabilitado → Habilitar con: <code>sudo a2enmod rewrite</code></li>";
echo "<li>❌ .htaccess no existe → Subir el archivo desde tu repositorio</li>";
echo "<li>❌ Directorios no escribibles → Ejecutar: <code>sudo chmod -R 775 storage bootstrap/cache</code></li>";
echo "<li>❌ APP_ENV no es 'production' → Editar .env y cambiar a production</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p style='color: red;'><strong>⚠️ IMPORTANTE: Elimina este archivo después del diagnóstico por seguridad.</strong></p>";
?>
