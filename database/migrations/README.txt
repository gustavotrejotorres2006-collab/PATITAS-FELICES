PatitasFelices — paquete inicial de migraciones Laravel 13

IMPORTANTE:
1. Estas migraciones están diseñadas para ejecutarse después de las migraciones Laravel predeterminadas que ya aparecen como Ran en tu proyecto.
2. Copia todos los archivos PHP de este paquete a database/migrations.
3. Revisa que no existan archivos con los mismos timestamps/nombres.
4. Antes de ejecutar, respalda la base de datos.
5. Ejecuta php artisan migrate:status y luego php artisan migrate.
6. Estados, reglas FEFO, límites de descuentos, exactamente un tipo por línea de venta, no inventario negativo y un único propietario principal activo requieren validación transaccional en aplicación. Las migraciones no reemplazan esa lógica.
7. Revisa configuración DB y .env antes de ejecutar. No se incluye migración create_users porque ya está ejecutada.
8. En el paquete inicial, campos estado/tipo son strings para compatibilidad con motores SQL; restringe valores en Form Requests/Enums.
