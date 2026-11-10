# Credenciales de demostración

Estas cuentas permiten probar localmente los distintos perfiles de la aplicación Patitas Felices.

| Perfil | Correo electrónico | Contraseña |
|---|---|---|
| Administrador | `admin@patitas.test` | `PatitasDemo2026!` |
| Recepción | `recepcion@patitas.test` | `PatitasDemo2026!` |
| Veterinaria | `veterinaria@patitas.test` | `PatitasDemo2026!` |
| Anestesia | `anestesia@patitas.test` | `PatitasDemo2026!` |

## Iniciar la aplicación

Desde la carpeta del proyecto, ejecuta:

```powershell
php artisan migrate --no-interaction
php artisan db:seed --no-interaction
php artisan serve
```

Después, abre [http://localhost:8000/login](http://localhost:8000/login) e inicia sesión con una de las cuentas anteriores.

## Importante

- Son cuentas y datos ficticios para pruebas locales; no representan usuarios reales.
- Todas comparten la misma contraseña y esta es conocida porque forma parte del seeder de demostración.
- No despliegues estas cuentas ni esta contraseña en producción. En producción deben usarse cuentas individuales y contraseñas seguras.
- El seeder de demostración se niega a ejecutarse cuando Laravel está en entorno de producción.
