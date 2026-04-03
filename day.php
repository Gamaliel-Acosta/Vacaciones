<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$dateValue = isset($_GET['date']) ? (string)$_GET['date'] : '';
$dateValue = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue) ? $dateValue : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro del día</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="day" data-selected-date="<?php echo htmlspecialchars($dateValue, ENT_QUOTES, 'UTF-8'); ?>">
  <div class="shape shape-a"></div>
  <div class="shape shape-b"></div>

  <main class="page-shell day-shell">
    <section class="day-card">
      <a class="back-link" href="index.php">← Volver al calendario</a>
      <h1 id="dayTitle">Día</h1>
      <p id="daySubtitle" class="section-note"></p>

      <div id="dayStatus" class="status-banner"></div>

      <section id="savedView" class="saved-view panel">
        <div class="saved-image-wrap">
          <img id="savedImage" alt="Imagen del día" />
          <div id="savedPlaceholder" class="preview-placeholder">No hay imagen para este día.</div>
        </div>
        <h2 id="savedTitle" class="saved-title">Sin título</h2>
        <p id="savedDescription" class="saved-description">Todavía no hay una descripción guardada para este día.</p>
        <div class="saved-actions">
          <button id="editButton" type="button" class="primary-button">Editar información</button>
        </div>
      </section>

      <form id="entryForm" class="entry-form panel is-collapsed">
        <label class="field-group">
          <span>Foto</span>
          <input id="photoInput" type="file" accept="image/*" />
        </label>

        <label class="field-group">
          <span>Título</span>
          <input id="titleInput" type="text" maxlength="80" placeholder="Escribe un título" />
        </label>

        <label class="field-group">
          <span>Descripción</span>
          <textarea id="descriptionInput" rows="6" maxlength="800" placeholder="Describe lo que hiciste ese día"></textarea>
        </label>

        <div class="form-actions">
          <button type="submit" class="primary-button">Guardar información</button>
          <button id="cancelEditButton" type="button" class="ghost-button">Cancelar</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    window.APP_CONFIG = {
      apiUrl: 'api.php',
      startDate: '<?php echo VACATION_START_DATE; ?>',
      endDate: '<?php echo VACATION_END_DATE; ?>',
      studentName: <?php echo json_encode(STUDENT_NAME, JSON_UNESCAPED_UNICODE); ?>
    };
  </script>
  <script src="script.js"></script>
</body>
</html>