<?php
// Tela do bate-ponto.
// Abre a webcam, le o QR (que tem a matricula dentro) e manda pro
// salvar-ponto.php com fetch, sem recarregar a pagina.
// Deixei como .php so pra ficar junto dos outros arquivos no htdocs.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bate-Ponto por QR Code</title>

    <!-- Bootstrap pelo CDN -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; }
        /* aqui aparece o video da camera */
        #leitor-qr {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 2px dashed #adb5bd;
            border-radius: 12px;
            overflow: hidden;
        }
        .card-ponto { max-width: 520px; margin: 40px auto; }
    </style>
</head>
<body>

    <div class="card card-ponto shadow-sm">
        <div class="card-body text-center">
            <h3 class="card-title mb-1">Registro de Ponto</h3>
            <p class="text-muted">Aponte o QR Code do crachá para a câmera</p>

            <!-- a lib coloca o video aqui dentro -->
            <div id="leitor-qr" class="mb-3"></div>

            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mb-3">
                <button id="btnIniciar" class="btn btn-primary">Iniciar câmera</button>
                <button id="btnParar" class="btn btn-outline-secondary" disabled>Parar câmera</button>
            </div>

            <!-- mensagem de sucesso/erro -->
            <div id="status" class="alert d-none" role="alert"></div>
        </div>
    </div>

    <!-- lib que abre a camera e decodifica o QR pra mim -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        const divStatus = document.getElementById("status");
        const btnIniciar = document.getElementById("btnIniciar");
        const btnParar = document.getElementById("btnParar");

        // o leitor aponta pra div #leitor-qr
        const leitor = new Html5Qrcode("leitor-qr");

        // trava pra nao mandar o mesmo ponto varias vezes
        // (a camera le o mesmo QR umas 10x por segundo)
        let ultimaMatricula = null;
        let processando = false;

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        // mostra a mensagem colorida no rodape do card
        function mostrarStatus(mensagem, tipo) {
            divStatus.className = "alert alert-" + tipo;
            divStatus.textContent = mensagem;
            divStatus.classList.remove("d-none");
        }

        // chamada toda vez que a lib le um QR. o texto eh a matricula.
        async function aoLerQrCode(textoDecodificado) {
            // console.log("li:", textoDecodificado); // usei pra testar a leitura
            // se ja tem um envio rolando ou eh a mesma matricula, ignora
            if (processando || textoDecodificado === ultimaMatricula) {
                return;
            }

            processando = true;
            ultimaMatricula = textoDecodificado;
            mostrarStatus("Registrando matrícula " + textoDecodificado + "...", "info");

            try {
                // POST pro PHP mandando a matricula em JSON
                const resposta = await fetch("salvar-ponto.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ matricula: textoDecodificado })
                });

                const dados = await resposta.json();

                if (dados.sucesso) {
                    mostrarStatus(
                        "Ponto registrado: " + dados.nome + " às " + dados.data_hora,
                        "success"
                    );
                } else {
                    mostrarStatus("Erro: " + dados.erro, "danger");
                }
            } catch (e) {
                // cai aqui se o servidor estiver fora do ar
                mostrarStatus("Falha de comunicação com o servidor.", "danger");
            }

            // libera uma nova leitura depois de 3s
            setTimeout(function () {
                processando = false;
                ultimaMatricula = null;
            }, 3000);
        }

        btnIniciar.addEventListener("click", function () {
            // facingMode environment tenta a camera traseira no celular;
            // no notebook ele usa a webcam normal mesmo
            leitor.start(
                { facingMode: "environment" },
                config,
                aoLerQrCode
            ).then(function () {
                btnIniciar.disabled = true;
                btnParar.disabled = false;
                mostrarStatus("Câmera ligada. Aproxime o QR Code.", "info");
            }).catch(function (err) {
                mostrarStatus("Não foi possível acessar a câmera: " + err, "danger");
            });
        });

        btnParar.addEventListener("click", function () {
            leitor.stop().then(function () {
                btnIniciar.disabled = false;
                btnParar.disabled = true;
                mostrarStatus("Câmera desligada.", "info");
            });
        });
    </script>
</body>
</html>
