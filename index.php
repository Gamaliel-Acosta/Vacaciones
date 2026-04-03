<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars(app_title(), ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="home">
  <div class="shape shape-a"></div>
  <div class="shape shape-b"></div>

  <main class="page-shell">
    <section class="hero-card">
      <p class="eyebrow">Proyecto escolar de preparatoria</p>
      <h1><?php echo htmlspecialchars(student_name(), ENT_QUOTES, 'UTF-8'); ?></h1>
      <form id="homeDescriptionForm" class="home-description-form">
        <article id="homeDescriptionPreview" class="home-description-preview"></article>
        <textarea id="homeDescriptionInput" class="home-description-input" rows="4" maxlength="500" placeholder="Escribe aquí la descripción general de tu proyecto"></textarea>
        <button id="homeDescriptionEdit" type="button" class="home-edit-icon" aria-label="Editar o guardar descripción">✎</button>
        <span id="homeDescriptionStatus" class="inline-status"></span>
      </form>
    </section>

    <section class="calendar-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Calendario</p>
          <h2>Registro diario de vacaciones</h2>
        </div>
        <p id="calendarRange" class="section-note"></p>
      </div>
      <div id="calendarGrid" class="calendar-grid" aria-label="Calendario interactivo por tarjetas"></div>
      <p class="calendar-help">Cada tarjeta muestra el estado del día. Puedes abrir el registro al hacer clic.</p>
    </section>
  </main>

  <script>
    window.APP_CONFIG = {
      apiUrl: 'api.php',
      startDate: '<?php echo vacation_start_date(); ?>',
      endDate: '<?php echo vacation_end_date(); ?>',
      studentName: <?php echo json_encode(student_name(), JSON_UNESCAPED_UNICODE); ?>
    };
  </script>
  <script src="script.js"></script>
</body>
</html>