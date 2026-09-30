<?php

function resort_local_image_url(string $path): ?string {
    $path = trim(str_replace('\\', '/', $path));
    if ($path === '' || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path) && !preg_match('~^https?://~i', $path)) {
        return null;
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }

    $path = ltrim($path, '/');
    if (preg_match('~(^|/)\.\.(/|$)~', $path)) {
        return null;
    }
    if (str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
        $candidates = [$path];
    } elseif (str_starts_with($path, 'images/')) {
        $candidates = ['assets/' . $path];
    } else {
        $candidates = ['assets/images/' . $path, 'assets/' . $path];
    }

    foreach ($candidates as $candidate) {
        $fullPath = realpath(APP_ROOT . '/' . $candidate);
        if ($fullPath && is_file($fullPath) && str_starts_with($fullPath, realpath(APP_ROOT) . DIRECTORY_SEPARATOR)) {
            return base_url($candidate);
        }
    }
    return null;
}

function resort_accommodation_images(array $room): array {
    $stored = json_decode((string)($room['images'] ?? ''), true);
    if (!is_array($stored)) {
        $stored = [];
    }

    $fallbacks = match ($room['room_type'] ?? '') {
        'Chalet' => ['images/shade.webp', 'images/cabin.webp', 'images/view.webp'],
        'Glamping Tent' => ['images/tent.webp', 'images/camp_van.webp'],
        'Suite' => ['images/rooms.webp', 'images/cabin.webp'],
        'Deluxe Room' => ['images/cabin.webp', 'images/rooms.webp', 'images/shade.webp'],
        default => ['images/rooms.webp', 'images/cabin.webp', 'images/view.webp'],
    };

    $images = [];
    $seen = [];
    foreach ($stored as $index => $storedPath) {
        if (!is_string($storedPath)) {
            continue;
        }
        $url = resort_local_image_url($storedPath);
        if (!$url) {
            $fallback = $fallbacks[$index % count($fallbacks)];
            $url = resort_local_image_url($fallback);
        }
        if ($url && !isset($seen[$url])) {
            $seen[$url] = true;
            $images[] = ['url' => $url, 'alt' => (string)($room['name'] ?? 'Resort accommodation')];
        }
    }

    if (!$images) {
        foreach ($fallbacks as $fallback) {
            $url = resort_local_image_url($fallback);
            if ($url) {
                $images[] = ['url' => $url, 'alt' => (string)($room['name'] ?? 'Resort accommodation')];
                break;
            }
        }
    }

    $count = count($images);
    foreach ($images as $index => &$image) {
        if ($count > 1) {
            $image['alt'] .= ' photo ' . ($index + 1) . ' of ' . $count;
        }
    }
    unset($image);
    return $images;
}