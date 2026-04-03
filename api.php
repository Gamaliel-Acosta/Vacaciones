<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $ok, array $payload = [], int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode(array_merge(['ok' => $ok], $payload), JSON_UNESCAPED_UNICODE);
    exit;
}

function isValidDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function isWithinRange(string $value): bool
{
    return $value >= VACATION_START_DATE && $value <= VACATION_END_DATE;
}

function rowToEntry(array $row, bool $includeImage = false): array
{
    $entry = [
        'date' => $row['entry_date'],
        'title' => $row['title'] ?? '',
        'description' => $row['description'] ?? '',
        'updatedAt' => $row['updated_at'] ?? null,
        'hasPhoto' => !empty($row['image_data']),
    ];

    if ($includeImage) {
        $mime = $row['image_mime'] ?? '';
        $data = $row['image_data'] ?? null;
        $entry['photoUrl'] = $data ? 'data:' . $mime . ';base64,' . base64_encode($data) : null;
    }

    return $entry;
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($action === 'home') {
        $pdo = getDb();
        $statement = $pdo->prepare('SELECT home_description, updated_at FROM app_settings WHERE id = 1 LIMIT 1');
        $statement->execute();
        $row = $statement->fetch();

        respond(true, [
            'description' => $row['home_description'] ?? '',
            'updatedAt' => $row['updated_at'] ?? null,
        ]);
    }

    if ($action === 'save-home') {
        $description = trim((string)($_POST['description'] ?? ''));

        $pdo = getDb();
        $statement = $pdo->prepare('INSERT INTO app_settings (id, home_description, updated_at) VALUES (1, :description, NOW()) ON DUPLICATE KEY UPDATE home_description = VALUES(home_description), updated_at = NOW()');
        $statement->execute([':description' => $description]);

        respond(true, ['message' => 'Descripción de la portada guardada correctamente.']);
    }

    if ($action === 'list') {
        $pdo = getDb();
        $statement = $pdo->prepare('SELECT entry_date, title, description, image_data, image_mime, updated_at FROM day_entries WHERE entry_date BETWEEN :start AND :end ORDER BY entry_date ASC');
        $statement->execute([
            ':start' => VACATION_START_DATE,
            ':end' => VACATION_END_DATE,
        ]);

        $entries = [];
        foreach ($statement->fetchAll() as $row) {
            $entries[] = rowToEntry($row, true);
        }

        respond(true, ['entries' => $entries]);
    }

    if ($action === 'read') {
        $date = (string)($_GET['date'] ?? '');

        if (!isValidDate($date) || !isWithinRange($date)) {
            respond(false, ['message' => 'La fecha solicitada no es válida o está fuera del rango.'], 422);
        }

        $pdo = getDb();
        $statement = $pdo->prepare('SELECT entry_date, title, description, image_data, image_mime, updated_at FROM day_entries WHERE entry_date = :date LIMIT 1');
        $statement->execute([':date' => $date]);
        $row = $statement->fetch();

        respond(true, ['entry' => $row ? rowToEntry($row, true) : null]);
    }

    if ($action === 'save') {
        $date = (string)($_POST['date'] ?? '');
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if (!isValidDate($date) || !isWithinRange($date)) {
            respond(false, ['message' => 'La fecha no es válida o no pertenece al calendario.'], 422);
        }

        $pdo = getDb();
        $statement = $pdo->prepare('SELECT image_data, image_mime FROM day_entries WHERE entry_date = :date LIMIT 1');
        $statement->execute([':date' => $date]);
        $existing = $statement->fetch();

        $imageData = $existing['image_data'] ?? null;
        $imageMime = $existing['image_mime'] ?? null;

        if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
            $detectedMime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']) ?: '';
            if ($detectedMime !== '' && str_starts_with($detectedMime, 'image/')) {
                $imageData = file_get_contents($_FILES['photo']['tmp_name']);
                $imageMime = $detectedMime;
            }
        }

        $upsert = $pdo->prepare(
            'INSERT INTO day_entries (entry_date, title, description, image_data, image_mime, created_at, updated_at)
             VALUES (:date, :title, :description, :image_data, :image_mime, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                description = VALUES(description),
                image_data = VALUES(image_data),
                image_mime = VALUES(image_mime),
                updated_at = NOW()'
        );

        $upsert->bindValue(':date', $date);
        $upsert->bindValue(':title', $title);
        $upsert->bindValue(':description', $description);
        $upsert->bindValue(':image_data', $imageData, $imageData === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
        $upsert->bindValue(':image_mime', $imageMime, $imageMime === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upsert->execute();

        $fetch = $pdo->prepare('SELECT entry_date, title, description, image_data, image_mime, updated_at FROM day_entries WHERE entry_date = :date LIMIT 1');
        $fetch->execute([':date' => $date]);
        $savedRow = $fetch->fetch();

        respond(true, ['entry' => $savedRow ? rowToEntry($savedRow, true) : null, 'message' => 'Información guardada correctamente.']);
    }

    if ($action === 'delete') {
        $date = (string)($_POST['date'] ?? '');

        if (!isValidDate($date) || !isWithinRange($date)) {
            respond(false, ['message' => 'La fecha no es válida o no pertenece al calendario.'], 422);
        }

        $pdo = getDb();
        $statement = $pdo->prepare('DELETE FROM day_entries WHERE entry_date = :date');
        $statement->execute([':date' => $date]);

        respond(true, ['message' => 'Registro eliminado.']);
    }

    respond(false, ['message' => 'Acción no reconocida.'], 400);
} catch (Throwable $exception) {
    respond(false, ['message' => 'No se pudo completar la operación.', 'detail' => $exception->getMessage()], 500);
}