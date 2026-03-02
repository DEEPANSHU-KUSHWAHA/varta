<?php
/**
 * PHPStan Stubs for MySQLi and other native PHP extensions
 * This file helps PHPStan understand MySQLi and other classes
 */

if (!class_exists('mysqli')) {
    class mysqli {
        public int $insert_id;
        public int $errno;
        public string $error;
        public int $affected_rows;

        public function __construct(
            string $hostname = null,
            string $username = null,
            string $password = null,
            string $database = null,
            int $port = null,
            string $socket = null
        ) {}

        public function prepare(string $query) {
            return new mysqli_stmt($this, $query);
        }

        public function query(string $query) {
            return new mysqli_result($this, MYSQLI_USE_RESULT);
        }

        public function close() {}
        public function connect(string $host, string $user, string $password, string $database) {}
    }
}

if (!class_exists('mysqli_stmt')) {
    class mysqli_stmt {
        public function bind_param(string $types, ...$vars) {}
        public function execute() {}
        public function fetch_assoc() {}
        public function get_result() {
            return new mysqli_result(null, MYSQLI_USE_RESULT);
        }
        public function close() {}
    }
}

if (!class_exists('mysqli_result')) {
    class mysqli_result {
        public function fetch_assoc() {}
        public function fetch_all(int $mode = MYSQLI_NUM) {}
        public function free() {}
    }
}

// PHPGangsta GoogleAuthenticator stub
if (!class_exists('PHPGangsta_GoogleAuthenticator')) {
    class PHPGangsta_GoogleAuthenticator {
        public function createSecret() {}
        public function getQRCodeGoogleUrl(string $name, string $secret, string $title = null) {}
        public function verifyCode(string $secret, string $code, int $discrepancy = 0) {}
    }
}