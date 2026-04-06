<?php
// Helper functions

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_unique_filename($extension)
{
    return uniqid() . '_' . time() . '.' . $extension;
}

function ensure_directory_exists($path)
{
    if (!file_exists($path)) {
        return mkdir($path, 0777, true);
    }
    return true;
}
