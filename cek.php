<?php
echo "PHP version: " . phpversion() . "<br>";
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YA' : 'TIDAK') . "<br>";
echo "Driver PDO yang tersedia: ";
print_r(PDO::getAvailableDrivers());
