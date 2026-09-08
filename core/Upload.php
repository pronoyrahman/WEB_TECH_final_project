<?php
// TravelVista - image uploads. The MIME type is read from the file bytes with
// finfo (not trusted from the browser) and the stored name is generated here.

/**
 * Validate and store one uploaded image.
 *
 * @param  array  $file    One entry from $_FILES.
 * @param  string $folder  'avatars' or 'posts'.
 * @return array{ok: bool, filename: ?string, error: ?string}
 */
function save_image(array $file, string $folder): array
{
    $folder = $folder === 'avatars' ? 'avatars' : 'posts';

    // no file chosen - the caller decides whether that is an error
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'error' => null];
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return upload_fail('That image is larger than the ' . upload_limit_label() . ' limit.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return upload_fail('The image did not finish uploading. Try again.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return upload_fail('That file was not received as an upload.');
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return upload_fail('That image is larger than the ' . upload_limit_label() . ' limit.');
    }

    // read the real type from the bytes on disk
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!in_array($mime, ALLOWED_IMAGE_MIME, true)) {
        return upload_fail('Use a JPG, PNG, WEBP or GIF image.');
    }
    if (getimagesize($file['tmp_name']) === false) {
        return upload_fail('That file is not a readable image.');
    }

    $extension = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ][$mime];

    $directory = UPLOAD_DIR . '/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        return upload_fail('The uploads folder is not writable.');
    }

    $filename = $folder . '-' . bin2hex(random_bytes(8)) . '-' . time() . '.' . $extension;

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        return upload_fail('The image could not be saved.');
    }

    return ['ok' => true, 'filename' => $filename, 'error' => null];
}

// Delete a stored image. Ignores names that contain a path.
function delete_image(?string $filename, string $folder): void
{
    if (!$filename || basename($filename) !== $filename) {
        return;
    }
    $path = UPLOAD_DIR . '/' . ($folder === 'avatars' ? 'avatars' : 'posts') . '/' . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}

// The configured upload limit as a readable string, e.g. "2 MB".
function upload_limit_label(): string
{
    return round(MAX_UPLOAD_BYTES / 1048576, 1) . ' MB';
}

function upload_fail(string $message): array
{
    return ['ok' => false, 'filename' => null, 'error' => $message];
}
