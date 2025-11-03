<?php
// db.php
$db = new SQLite3('database.db');

// Cria tabela se não existir
$db->exec('CREATE TABLE IF NOT EXISTS links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    url TEXT NOT NULL
)');
?>
