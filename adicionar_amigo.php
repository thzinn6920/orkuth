<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['amigo_id'])) {
    header("Location: logado.php?pagina=adicionar");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$amigo_id = $_POST['amigo_id'];

// Verifica se já existe amizade (pendente ou aceita)
$stmt = $conn->prepare("SELECT * FROM amigos WHERE usuario_id = ? AND amigo_id = ?");
$stmt->bind_param("ii", $usuario_id, $amigo_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $_SESSION['mensagem_amigo'] = "❌ Você já enviou uma solicitação ou é amigo dessa pessoa.";
} else {
    // Inserir nova solicitação de amizade
    $stmt = $conn->prepare("INSERT INTO amigos (usuario_id, amigo_id, status) VALUES (?, ?, 'pendente')");
    $stmt->bind_param("ii", $usuario_id, $amigo_id);
    if ($stmt->execute()) {
        $_SESSION['mensagem_amigo'] = "✅ Solicitação de amizade enviada com sucesso!";
    } else {
        $_SESSION['mensagem_amigo'] = "⚠️ Erro ao enviar solicitação.";
    }
}

// Redireciona de volta com a mesma busca (se houver)
$busca = $_GET['busca'] ?? '';
header("Location: logado.php?pagina=adicionar&busca=" . urlencode($busca));
exit;
