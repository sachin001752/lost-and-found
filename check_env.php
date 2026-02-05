<?php
echo "OpenSSL Support: " . (extension_loaded('openssl') ? "Enabled" : "Disabled") . "\n";
echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
?>
