<?php
session_start();
require_once 'conexao.php';

$nome = $_POST['nome'];
$id = $_SESSION['usuario_id'];

$foto = $_FILES['foto']['name'];
if ($foto) {
    $caminho = 'fotos/' . basename($foto);
    move_uploaded_file($_FILES['foto']['tmp_name'], $caminho);
    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, foto = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $foto, $id);
} else {
    $stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $nome, $id);
}

$stmt->execute();
header("Location: logado.php");
