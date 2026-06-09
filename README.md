# Bate-ponto com QR Code

Esse projeto eu fiz pra juntar umas coisas que eu tava estudando separado: front-end, câmera, PHP e banco de dados. O funcionamento é simples: a pessoa aponta o crachá (que tem um QR Code com a matrícula) pra webcam e o sistema já registra o horário, sem digitar nada e sem recarregar a página.

A real é que eu queria sair do básico de "formulário com botão enviar" e ver como funciona uma requisição assíncrona na prática. Esse projeto foi bom pra isso.

## O que ele faz

- Abre a webcam direto no navegador
- Lê o QR Code e pega a matrícula que tá dentro dele (tipo M001)
- Manda a matrícula pro servidor sem recarregar a tela (fetch)
- O PHP vê se o funcionário existe no banco e, se existir, grava o ponto com a hora do servidor
- A resposta aparece na hora, com o nome e o horário

## O que usei

- HTML + Bootstrap (via CDN, pra não precisar instalar nada)
- html5-qrcode, que é a lib que cuida da câmera e de ler o QR
- PHP sem framework (de propósito, pra entender o básico)
- MySQL do XAMPP

## Como rodar

1. Instalar o XAMPP e ligar o Apache e o MySQL.
2. Colocar a pasta dentro de `C:\xampp\htdocs`.
3. Abrir o phpMyAdmin, ir em Importar e mandar o `banco.sql`. Isso cria o banco `bate_ponto` já com 3 funcionários de teste.
4. Abrir `http://localhost/projeto-bate-ponto-qr/index.php`.
5. Pra ter os QR de teste, abrir o `gerar-qr.html` (em outra aba ou no celular). Ele já mostra os crachás do M001, M002 e M003 prontos. Aí é só apontar a câmera pra um deles.
6. Pra ver os funcionários e o histórico de pontos batidos, abrir `http://localhost/projeto-bate-ponto-qr/consulta.php` (ou clicar em "Ver registros" na página dos QR Codes).

Uma coisa que descobri quebrando a cabeça: a câmera só abre em `localhost` ou `https`. Se eu abrir o arquivo direto (`file:///`) o navegador bloqueia a webcam. Por isso tem que ser pelo Apache.

## Arquivos

- `index.php` - a tela, abre a câmera e manda o POST
- `salvar-ponto.php` - recebe o POST, valida e grava no banco
- `consulta.php` - mostra os funcionários e o histórico de pontos
- `gerar-qr.html` - gera os QR de teste aqui mesmo, sem site de fora
- `banco.sql` - cria o banco e as tabelas

## Desafios Técnicos e Soluções

### Processar a requisição no PHP

O que mais me travou foi entender como o PHP pega o que o JavaScript manda. Eu tentava usar `$_POST['matricula']` e vinha sempre vazio. Demorei pra sacar que o `$_POST` só preenche sozinho quando os dados vêm de um formulário normal. Como eu mandei JSON pelo fetch, eu tinha que ler o corpo da requisição com `file_get_contents("php://input")` e jogar no `json_decode` pra virar um array. Depois que entendi isso, funcionou.

No fim o back-end segue sempre a mesma ordem: recebe, valida, executa e responde. Primeiro confiro se é POST mesmo, depois se a matrícula veio, e só então mexo no banco.

### Prepared statement e SQL Injection

Esse eu fiz questão de fazer certo. Se eu montasse a query concatenando o texto (tipo `... WHERE matricula = '$matricula'`), daria pra alguém colocar um SQL malicioso dentro do QR e estragar o banco, que é o famoso SQL Injection. Usei prepared statement com PDO: coloco um `?` na query e passo o valor separado no `execute()`. Aí o banco trata aquilo como dado, não como comando. Foi quando eu entendi de verdade por que falam pra nunca concatenar variável no SQL.

### A parte assíncrona

A página nunca recarrega. Quando o QR é lido, o JS faz um `fetch`, espera o PHP responder (com `await`) e atualiza só a caixinha de status. Tive um problema meio chato: a câmera lê o mesmo QR várias vezes por segundo, então sem cuidado o ponto era registrado um monte de vezes seguidas. Resolvi com uma variável de trava (`processando`) e um `setTimeout` que só libera de novo depois de 3 segundos.

### Pegar a hora certa

Dava pra pegar a hora pelo JavaScript, mas o relógio de cada PC pode estar errado. Então deixei o MySQL gravar a hora com `NOW()` na hora do INSERT. Assim o horário sempre vem do servidor.
