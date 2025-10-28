<?php
/**
 * Sanitizes data for HTML output to prevent XSS.
 * @param string $data The data to sanitize.
 * @return string The sanitized data.
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>