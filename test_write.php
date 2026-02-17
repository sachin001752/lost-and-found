<?php
$file = 'uploads/test_write_permission.txt';
if (file_put_contents($file, 'Test content')) {
    echo "SUCCESS: File written to uploads directory.";
    unlink($file);
} else {
    echo "FAILURE: Could not write to uploads directory.";
}
?>
