# Proyecto de vacaciones

Aplicación web en PHP para XAMPP con front amigable, JavaScript y MySQL.

## Archivos principales
- `index.php`: portada con calendario.
- `day.php`: formulario diario para foto, título y descripción.
- `api.php`: backend JSON para leer, guardar y borrar registros.
- `schema.sql`: tabla MySQL para importar en phpMyAdmin.

## Rango del calendario
- Inicio: 27 de marzo de 2026
- Fin: 10 de abril de 2026

## Configuración en XAMPP
1. Copia la carpeta en `htdocs`.
2. Crea la base de datos importando `schema.sql` en phpMyAdmin.
3. Verifica los datos de conexión en `config.php`.
4. Abre `http://localhost/tarea_uziel/index.php`.

## Notas
- La foto se guarda en MySQL como imagen en base de datos.
- La página usa `fetch` para comunicarse con PHP sin recargar.
- GitHub Pages no ejecuta PHP, así que este proyecto está pensado para XAMPP o cualquier hosting con PHP.
