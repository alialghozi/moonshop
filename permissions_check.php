<?php
$upload_dir = __DIR__ . '/uploads/';
if (is_writable($upload_dir)) {
    echo "The uploads directory is writable.";
} else {
    echo "The uploads directory is NOT writable.";
}
?>
