<?php include 'conexao.php'; ?>

<?php
// Adicionar link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url']) && isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $url = $_POST['url'];
    $stmt = $conn->prepare('INSERT INTO links (nome, url) VALUES (?, ?)');
    $stmt->bind_param("ss", $nome, $url);
    if ($stmt->execute()) {
        $mensagem_sucesso = "Link adicionado com sucesso!";
    } else {
        $mensagem_erro = "Erro ao adicionar link: " . $conn->error;
    }
    $stmt->close();
}

// Remover link
if (isset($_POST['remover_id'])) {
    $id = $_POST['remover_id'];
    $stmt = $conn->prepare('DELETE FROM links WHERE id = ?');
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem_sucesso = "Link removido com sucesso!";
    } else {
        $mensagem_erro = "Erro ao remover link: " . $conn->error;
    }
    $stmt->close();
}

// Buscar todos os links
$result = $conn->query('SELECT * FROM links ORDER BY id DESC');
if (!$result) {
    die("Erro na consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .mensagem {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<header>
    <h1>Painel Administrativo</h1>
    <button onclick="window.location.href='acoes-sociais.php'">Ir para página pública</button>
</header>

<main>
    <?php if (isset($mensagem_sucesso)): ?>
        <div class="mensagem sucesso"><?= $mensagem_sucesso ?></div>
    <?php endif; ?>
    
    <?php if (isset($mensagem_erro)): ?>
        <div class="mensagem erro"><?= $mensagem_erro ?></div>
    <?php endif; ?>

    <section>
        <h2>Adicionar novo link</h2>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome do projeto" required>
            <input type="url" name="url" placeholder="Link do Instagram" required>
            <button type="submit">Adicionar</button>
        </form>
    </section>

    <section>
        <h2>Links cadastrados</h2>
        <table>
            <tr><th>ID</th><th>Nome</th><th>URL</th><th>Ações</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><a href="<?= htmlspecialchars($row['url']) ?>" target="_blank">Ver Post</a></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="remover_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="remover">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
</main>
</body>
</html>