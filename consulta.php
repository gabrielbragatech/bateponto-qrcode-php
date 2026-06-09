<?php
// Pagina de consulta do bate-ponto.
// Mostra os funcionarios cadastrados e o historico de pontos batidos.
// (a tela do index.php so registra, aqui eu leio os dados pra ver)

$host  = "localhost";
$banco = "bate_ponto";
$usuario = "root";
$senha   = "";

$erro = null;
$funcionarios = [];
$registros = [];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // lista de funcionarios
    $funcionarios = $pdo
        ->query("SELECT id, nome, matricula FROM funcionarios ORDER BY nome")
        ->fetchAll();

    // historico de pontos. junto as duas tabelas com JOIN pra trazer o
    // nome do funcionario junto da data do ponto, do mais novo pro mais velho.
    $sql = "SELECT f.nome, f.matricula,
                   DATE_FORMAT(r.data_hora, '%d/%m/%Y %H:%i:%s') AS quando
              FROM registros_ponto r
              JOIN funcionarios f ON r.funcionario_id = f.id
          ORDER BY r.data_hora DESC";
    $registros = $pdo->query($sql)->fetchAll();

} catch (PDOException $e) {
    $erro = "Não foi possível conectar no banco. Veja se o MySQL está ligado.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta - Bate-Ponto</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; padding-bottom: 40px; }
        .container { max-width: 800px; }
    </style>
</head>
<body>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center my-4">
            <h3 class="mb-0">Consulta de Pontos</h3>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar</a>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php else: ?>

            <!-- funcionarios cadastrados -->
            <h5>Funcionários</h5>
            <table class="table table-striped table-bordered bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Matrícula</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funcionarios as $f): ?>
                        <tr>
                            <td><?php echo $f["id"]; ?></td>
                            <td><?php echo htmlspecialchars($f["nome"]); ?></td>
                            <td><?php echo htmlspecialchars($f["matricula"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- historico de pontos -->
            <h5 class="mt-4">Histórico de pontos</h5>
            <?php if (count($registros) === 0): ?>
                <div class="alert alert-info">
                    Nenhum ponto registrado ainda. Bata um ponto na tela principal e volte aqui.
                </div>
            <?php else: ?>
                <table class="table table-striped table-bordered bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>Funcionário</th>
                            <th>Matrícula</th>
                            <th>Data / Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r["nome"]); ?></td>
                                <td><?php echo htmlspecialchars($r["matricula"]); ?></td>
                                <td><?php echo $r["quando"]; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</body>
</html>

