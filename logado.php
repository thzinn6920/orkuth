<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$pagina = $_GET['pagina'] ?? 'perfil';

// Buscar dados do usuário logado
$stmt = $conn->prepare("SELECT nome, usuario, foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Buscar amigos
$stmt = $conn->prepare("
    SELECT u.nome, u.usuario, u.foto
    FROM amigos a
    JOIN usuarios u ON u.id = a.amigo_id
    WHERE a.usuario_id = ? AND a.status = 'aceito'
    LIMIT 6
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$amigos_result = $stmt->get_result();

$amigos = [];
while ($row = $amigos_result->fetch_assoc()) {
    $amigos[] = $row;
}

// Resultado da busca de usuários
$busca_resultado = [];
if ($pagina === 'adicionar' && isset($_GET['busca'])) {
    $busca = '%' . $_GET['busca'] . '%';
    $stmt = $conn->prepare("SELECT id, nome, foto FROM usuarios WHERE nome LIKE ? AND id != ?");
    $stmt->bind_param("si", $busca, $usuario_id);
    $stmt->execute();
    $busca_resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logado - Orkuth</title>
    <link rel="stylesheet" href="logado.css">
    <style>
        .botao-adicionar {
            width: 35px;
            height: 35px;
            border: 2px solid #000;
            border-radius: 50%;
            background: #fff;
            color: #000;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .resultado {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .resultado img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        form textarea {
            width: 100%;
            height: 100px;
            resize: none;
        }
    </style>
</head>
<body>
<div class="container">

    <aside class="perfil">
        <img src="fotos/<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto do usuário">
        <h2><?= htmlspecialchars($usuario['nome']) ?></h2>
        <ul>
            <li>😄 <a href="?pagina=editar">Editar perfil</a></li>
            <li>👥 <a href="?pagina=adicionar">Adicionar amigo</a></li>
            <li>💬 <a href="?pagina=mensagem">Enviar mensagem</a></li>
        </ul>
    </aside>

    <main class="conteudo">
        <?php if ($pagina === 'perfil'): ?>
            <section class="info">
                <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
                <p><strong>Usuário:</strong> <?= htmlspecialchars($usuario['usuario']) ?></p>
            </section>
            <section class="mensagem">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/56/Mark_Zuckerberg_F8_2019_Keynote_%2847908857082%29_%28cropped%29.jpg" alt="Mark Zuckerberg">
                <div>
                    <p><strong>Mark Zuckerberg enviou uma mensagem para você</strong></p>
                    <p>Obrigado por aquecer o terreno.<br>Refinei o que você começou... e adicionei uma timeline.<br>Abraços!</p>
                    <a href="#">Enviar mensagem</a>
                </div>
            </section>

        <?php elseif ($pagina === 'editar'): ?>
            <section class="info">
                <h3>Editar Perfil</h3>
                <form method="POST" action="salvar_edicao.php" enctype="multipart/form-data">
                    <label>Nome:</label><br>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>"><br><br>
                    <label>Foto:</label><br>
                    <input type="file" name="foto"><br><br>
                    <button type="submit">Salvar</button>
                </form>
            </section>

        <?php elseif ($pagina === 'adicionar'): ?>
            <section class="info">
                <h3>Adicionar Amigo</h3>

                <?php if (isset($_SESSION['mensagem_amigo'])): ?>
                    <p style="color: green; font-weight: bold;">
                        <?= $_SESSION['mensagem_amigo'] ?>
                    </p>
                    <?php unset($_SESSION['mensagem_amigo']); ?>
                <?php endif; ?>

                <form method="GET">
                    <input type="hidden" name="pagina" value="adicionar">
                    <input type="text" name="busca" placeholder="Buscar por nome" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    <button type="submit">Buscar</button>
                </form>

                <?php foreach ($busca_resultado as $u): ?>
                    <div class="resultado">
                        <img src="fotos/<?= htmlspecialchars($u['foto']) ?>" alt="<?= htmlspecialchars($u['nome']) ?>">
                        <strong><?= htmlspecialchars($u['nome']) ?></strong>
                        <form method="POST" action="adicionar_amigo.php" style="margin-left: auto;">
                            <input type="hidden" name="amigo_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="botao-adicionar">+</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </section>

        <?php elseif ($pagina === 'mensagem'): ?>
            <section class="info">
                <h3>Enviar Mensagem</h3>
                <form method="POST" action="enviar_mensagem.php">
                    <label>Para:</label><br>
                    <input type="text" name="para"><br><br>
                    <label>Mensagem:</label><br>
                    <textarea name="mensagem"></textarea><br><br>
                    <button type="submit">Enviar</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <aside class="amigos">
        <h3>amigos (<?= count($amigos) ?>)</h3>
        <div class="lista-amigos">
            <?php foreach ($amigos as $amigo): ?>
                <div class="amigo">
                    <img src="fotos/<?= htmlspecialchars($amigo['foto']) ?>" alt="<?= htmlspecialchars($amigo['nome']) ?>">
                    <p><?= htmlspecialchars($amigo['nome']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="#" class="ver-todos">Ver todos</a>
    </aside>

</div>
</body>
</html>
