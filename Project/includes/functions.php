<?php

const MAX_IMAGE_BYTES = 2097152;

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function valid_csrf_token(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function validate_username(string $username): ?string
{
    if (strlen($username) < 3 || strlen($username) > 25) {
        return 'Username must be between 3 and 25 characters.';
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        return 'Username may contain only letters, numbers, and underscores.';
    }
    return null;
}

/** @return array{0: ?string, 1: ?string} relative path and validation error */
function save_image_upload(?array $file, bool $required = false): array
{
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, $required ? 'An image is required.' : null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'The image upload failed. Please try again.'];
    }
    if ($file['size'] <= 0 || $file['size'] > MAX_IMAGE_BYTES) {
        return [null, 'Images must be no larger than 2 MB.'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => ['image/jpeg', 'jpg'], IMAGETYPE_PNG => ['image/png', 'png'], IMAGETYPE_GIF => ['image/gif', 'gif']];
    if (!$imageInfo || !isset($allowed[$imageInfo[2]]) || $mime !== $allowed[$imageInfo[2]][0]) {
        return [null, 'Upload a valid JPEG, PNG, or GIF image.'];
    }

    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return [null, 'The upload directory is unavailable.'];
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$imageInfo[2]][1];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        return [null, 'The image could not be saved. Please try again.'];
    }
    return ['uploads/' . $filename, null];
}

function set_flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function get_flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}
