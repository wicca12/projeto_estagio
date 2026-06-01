<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "mydb";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli($host, $usuario, $senha, $banco);

    $conn->set_charset("utf8mb4");

} catch (Exception $e) {

    die("Erro ao conectar ao banco de dados.");
}