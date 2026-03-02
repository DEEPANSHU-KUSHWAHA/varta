<?php
/** @var mysqli $conn */
$conn = new mysqli();
// phpstan-stubs.php
 if (!class_exists('mysqli')) { 
    class mysqli { 
        public function __construct(
             string $hostname = null,
              string $username = null,
               string $password = null,
                string $database = null,
                 int $port = null,
                  string $socket = null
                   ) {}
                    public function prepare(string $query) {} 
                    public function query(string $query) {}
                     public function close() {}
                      }
                       }