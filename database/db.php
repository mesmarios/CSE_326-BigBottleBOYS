<?php
// Shared PDO bootstrap (EL/EN): included by pages that need DB access.
// Αυτό το αρχείο γίνεται require_once σε κάθε σελίδα
try {
 $pdo = new PDO(
 'mysql:host=localhost;dbname=bigbrothers;charset=utf8mb4',
 'root', // username
 '', // password (κενό σε local LAMP)
 [
 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
 ]
 );
} catch (PDOException $e) {
 http_response_code(500);
 exit('Database connection failed.');
}
