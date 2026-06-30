<?php
// mongo.php — MongoDB connection helper (user side)
// Requires the MongoDB PHP driver extension to be enabled (extension=mongodb)
// and Composer package mongodb/mongodb installed in this folder.

require_once __DIR__ . '/vendor/autoload.php';

// Database: tickets,  Collection: entries
// (Matches the structure shown in the Phase 4 specification)
$MONGO_URI        = "mongodb://localhost:27017";
$MONGO_NAMESPACE  = "tickets.entries";

try {
    $manager = new MongoDB\Driver\Manager($MONGO_URI);
} catch (Exception $e) {
    die("MongoDB connection failed: " . $e->getMessage());
}
?>
