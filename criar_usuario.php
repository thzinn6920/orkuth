<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $nome = $_POST['nome'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $foto_nome = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nome = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], "fotos/" . $foto_nome);
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['admin_msg'] = "⚠️ Esse usuário já está cadastrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, nome, senha, foto) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $usuario, $nome, $senha, $foto_nome);

        if ($stmt->execute()) {
            $_SESSION['admin_msg'] = "✅ Usuário criado com sucesso!";
        } else {
            $_SESSION['admin_msg'] = "Erro ao criar usuário: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
    header("Location: cadastro.html");
    exit;
}
?>