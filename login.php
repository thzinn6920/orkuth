<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha, nome, foto FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario_data = $result->fetch_assoc();

        if (password_verify($senha, $usuario_data['senha'])) {
            $_SESSION['usuario_id'] = $usuario_data['id']; // ao invés de 'id'
            $_SESSION['nome'] = $usuario_data['nome'];
            $_SESSION['foto'] = $usuario_data['foto'];
            $_SESSION['usuario'] = $usuario;


            header("Location: logado.php");
            exit;
        } else {
            $_SESSION['login_erro'] = "❌ Senha incorreta.";
        }
    } else {
        $_SESSION['login_erro'] = "❌ Usuário não encontrado.";
    }

    $stmt->close();
    $conn->close();

    header("Location: index.html");
    exit;
}
?>
