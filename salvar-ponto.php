<?php
// Back-end do bate-ponto.
// Recebe a matricula por POST JSON, ve se o funcionario existe,
// grava o ponto com a hora do servidor e devolve a resposta em JSON.

header("Content-Type: application/json; charset=utf-8");

// dados de conexao - padrao do XAMPP (root e sem senha)
$host  = "localhost";
$banco = "bate_ponto";
$usuario = "root";
$senha   = "";

// atalho pra devolver o JSON e parar o script
function responder($dados) {
    echo json_encode($dados);
    exit;
}

// so aceito POST aqui
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder([
        "sucesso" => false,
        "erro"    => "Método inválido. Use POST."
    ]);
}

// o JS mandou JSON, entao leio o corpo cru e dou json_decode
$corpo = file_get_contents("php://input");
$json  = json_decode($corpo, true);

// trim pra tirar espaco que as vezes vem junto do QR
$matricula = isset($json["matricula"]) ? trim($json["matricula"]) : "";

if ($matricula === "") {
    responder([
        "sucesso" => false,
        "erro"    => "Matrícula não informada."
    ]);
}

// conecta no banco com PDO
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
} catch (PDOException $e) {
    responder([
        "sucesso" => false,
        "erro"    => "Falha ao conectar no banco de dados."
    ]);
}

try {
    // ve se a matricula existe (com ? pra evitar SQL Injection)
    $consulta = $pdo->prepare(
        "SELECT id, nome FROM funcionarios WHERE matricula = ? LIMIT 1"
    );
    $consulta->execute([$matricula]);
    $funcionario = $consulta->fetch();

    if (!$funcionario) {
        responder([
            "sucesso" => false,
            "erro"    => "Funcionário não encontrado para a matrícula " . $matricula
        ]);
    }

    // grava o ponto. o NOW() pega a hora do proprio MySQL
    $insercao = $pdo->prepare(
        "INSERT INTO registros_ponto (funcionario_id, data_hora) VALUES (?, NOW())"
    );
    $insercao->execute([$funcionario["id"]]);

    // busco a hora gravada so pra devolver formatada bonitinha
    $ultimoId = $pdo->lastInsertId();
    $busca = $pdo->prepare(
        "SELECT DATE_FORMAT(data_hora, '%d/%m/%Y %H:%i:%s') AS quando
           FROM registros_ponto WHERE id = ?"
    );
    $busca->execute([$ultimoId]);
    $registro = $busca->fetch();

    // deu certo
    responder([
        "sucesso"   => true,
        "nome"      => $funcionario["nome"],
        "data_hora" => $registro["quando"]
    ]);

} catch (PDOException $e) {
    // qualquer erro de SQL cai aqui
    responder([
        "sucesso" => false,
        "erro"    => "Erro ao registrar o ponto."
    ]);
}
