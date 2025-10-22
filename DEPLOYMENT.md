# Guía de Configuración: Local vs Producción

Esta guía te ayudará a cambiar la configuración entre ambiente local (XAMPP) y producción (servidor web).

---

## 📋 Checklist de Cambios

### Para trabajar en **LOCAL** (XAMPP):

#### 1. Archivo `.env`
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/inventario-8

# NO incluir línea ASSET_URL
```

#### 2. Archivo `public/.htaccess`
```apache
RewriteBase /inventario-8/
```

#### 3. Base de datos
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monkits_inventario
DB_USERNAME=root
DB_PASSWORD=
```

#### 4. Comandos después de cambiar
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### 5. URL de acceso
```
http://localhost/inventario-8
```

---

### Para subir a **PRODUCCIÓN**:

#### 1. Archivo `.env`
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://monkits.com/inventario
ASSET_URL=https://monkits.com/inventario
```

#### 2. Archivo `public/.htaccess`
```apache
RewriteBase /inventario/
```

#### 3. Base de datos
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monkits_inventario
DB_USERNAME=root
DB_PASSWORD=tu_password_seguro_aqui
```

#### 4. Comandos después de cambiar
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```

#### 5. URL de acceso
```
https://monkits.com/inventario
```

---

## ⚠️ Advertencias Importantes

### Nunca en producción:
- ❌ `APP_DEBUG=true` (expone información sensible)
- ❌ `APP_ENV=local` (deshabilita optimizaciones)
- ❌ Contraseña de base de datos vacía

### Siempre verificar:
- ✅ Caché limpiado después de cambios
- ✅ Permisos correctos en carpetas `storage/` y `bootstrap/cache/`
- ✅ SSL/HTTPS habilitado en producción
- ✅ Backup de base de datos antes de migrar

---

## 🔄 Comando Rápido de Cambio

### Script para LOCAL:
```bash
# Copiar .env.local a .env (si existe)
cp .env.local .env
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

### Script para PRODUCCIÓN:
```bash
# Copiar .env.production a .env (si existe)
cp .env.production .env
php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan optimize
```

---

## 💡 Recomendaciones

1. **Mantén dos archivos de configuración separados:**
   - `.env.local` - Para desarrollo local
   - `.env.production` - Para servidor de producción
   - Copia el que necesites a `.env` según el ambiente

2. **NO subas el archivo `.env` a GitHub**
   - Ya está en `.gitignore`
   - Cada ambiente debe tener su propio `.env`

3. **Haz respaldo antes de cambiar**
   - Guarda una copia del `.env` actual antes de modificarlo

4. **Verifica el archivo `public/.htaccess`**
   - Es fácil olvidar cambiar el `RewriteBase`
   - Esto puede causar errores 404 o rutas rotas

---

## 🐛 Problemas Comunes

### Problema: "404 Not Found" o rutas no funcionan
**Solución:** Verifica que `RewriteBase` en `public/.htaccess` coincida con tu estructura de carpetas

### Problema: CSS/JS no cargan
**Solución:** Verifica `APP_URL` en `.env` y ejecuta `php artisan config:clear`

### Problema: "Base table or view not found"
**Solución:** Ejecuta las migraciones: `php artisan migrate`

### Problema: Errores de permisos
**Solución:**
```bash
# En Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# En Windows/XAMPP normalmente no es necesario
```

---

## 📞 Soporte

Si encuentras problemas, verifica:
1. Apache y MySQL están corriendo en XAMPP
2. El archivo `.env` está correctamente configurado
3. Has ejecutado los comandos de limpieza de caché
4. La base de datos existe y tiene datos

---

**Última actualización:** 2025-10-22
