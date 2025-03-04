<?php
include('../protect.php'); // Inclui a função de proteção ao acesso da página
require_once('../conexao.php');
$conexao = novaConexao();

unset($_SESSION['origem']);

$registros = [];
$erro = false;

try {
    $sql = "SELECT * FROM produtos";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos os registros

    $sql_cat = "SELECT * FROM produtos";
    $stmt = $conexao->prepare($sql_cat);
    $stmt->execute();
    $registroCat = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos os registros

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtraCat'])) {
        $id = $_POST['filtraCat']; // Captura o valor do botão de filtro

        // A consulta SQL agora utiliza um parâmetro para evitar injeção SQL
        $sql = "SELECT * FROM produtos WHERE nomeExib = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR); // Binding do parâmetro
        $stmt->execute(); // Executa a consulta

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos os registros
    }
} catch (PDOException $e) {
    $erro = true; // Configura erro se houver uma exceção
    echo "Erro: " . $e->getMessage();
}

if (isset($_POST['delete'])) {
    $id = $_POST['codPro'];

    try {
        $sqlSelect = "SELECT imagem FROM produtos WHERE codPro = :id";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtSelect->execute();
        $produto = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($produto && isset($produto['imagem'])) {
            $caminhoArquivo = $produto['imagem'];

            // Verifica e exclui o arquivo se ele existir
            if (file_exists($caminhoArquivo)) {
                if (unlink($caminhoArquivo)) {
                    echo "Arquivo excluído com sucesso!";
                } else {
                    echo "Erro ao excluir o arquivo.";
                }
            } else {
                echo "O arquivo não existe ou já foi excluído.";
            }
        }

        // SQL para excluir a linha com base no ID
        $sqlDelete = "DELETE FROM produtos WHERE codPro = :id";
        $stmtDelete = $conexao->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmtDelete->execute()) {
            echo "Linha excluída com sucesso!";
            // Redireciona para evitar reenviar o formulário
            header("Location: editProd.php");
            exit;
        } else {
            echo "Erro ao excluir a linha do banco de dados.";
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

if (isset($_POST['edit'])) {
    $_SESSION['codPro'] = [
        $_POST['codPro']
    ];
    header("Location: edicaoProd.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <div class="container-fluid cabecalho"> <!-- CABECALHO -->
        <nav class="navbar navbar-light navbar-expand-md" style="background-color: #FFFF;">
            <a class="nav justify-content-start m-2" href="../admInicial.php">
                <img src="../img/back.png">
            </a>

            <button class="navbar-toggler hamburguer" data-bs-toggle="collapse" data-bs-target="#navegacao">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navegacao">

                <ul class="nav nav-pills justify-content-center listas"> <!-- LISTAS DO MENU CABECALHO-->


                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Pedidos
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="../pedidos/cadPed.php">Cadastro</a>
                            <a class="dropdown-item" href="../pedidos/consPed.php">Consulta</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Agenda
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="../agenda/insAge.php">Inserir</a>
                            <a class="dropdown-item" href="../agenda/consAge.php">Consultar</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Produtos
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="./cadProd.php">Cadastro</a>
                            <a class="dropdown-item" href="./editProd.php">Edição</a>
                            <a class="dropdown-item" href="./categoria.php">Categoria</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Funcionários
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="../funcionarios/cadFunc.php">Cadastro</a>
                            <a class="dropdown-item" href="../funcionarios/listaFunc.php">Listar</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                </ul> <!-- FECHA LISTAS MENU CABECALHO -->
            </div>
            <a href="../logout.php" class="nav-link justify-content-end" style="color: red;">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                    class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                    <path fill-rule="evenodd"
                        d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                </svg>
            </a>
        </nav> <!-- FECHA CABECALHO -->
    </div> <!-- FECHA CONTAINER DO CABECALHO -->

    <h3 class="text-center mb-5">Lista de Produtos</h3>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center text-center">
            <h5 class="mb-3">FILTRAR POR:</h5>
            <div class="dropdown">
                <form method="POST">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Nome
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php
                        // Array para rastrear valores únicos
                        $filtrosExibidos = [];

                        foreach ($registroCat as $registro):
                            // Verifica se o valor já foi exibido
                            if (in_array($registro['nomeExib'], $filtrosExibidos)) {
                                continue; // Ignora valores duplicados
                            }

                            // Adiciona o valor ao array de rastreamento
                            $filtrosExibidos[] = $registro['nomeExib'];
                        ?>
                            <li>
                                <!-- Botão de envio que envia o nomeExib como valor -->
                                <button type="submit" class="dropdown-item btnFiltro" name="filtraCat"
                                    value="<?php echo htmlspecialchars($registro['nomeExib']); ?>">
                                    <?php echo htmlspecialchars($registro['nomeExib']); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <button type="submit" class="btn btn-outline-danger" name="limpar" value="pendente">limpar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($erro): ?> <!-- Se a variável $erro for true, exibe uma mensagem de erro. -->
        <div class="alert alert-danger" role="alert">
            Não foi possível carregar os dados.
        </div>
    <?php else: ?> <!-- Se não houve erro, exibe a tabela com os registros. -->
        <div class="container consContainer">
            <table class="table table-striped">
                <thead> <!-- define o cabeçalho da tabela -->
                    <tr>
                        <th>
                            <div class="row justify-content-center text-center titleCons">
                                ID
                            </div>
                        </th>
                        <th>
                            <div class="row justify-content-center titleCons">
                                Nome de exib.
                            </div>
                        </th>
                        <th>
                            <div class="row justify-content-center titleCons">
                                Produto
                            </div>
                        </th>
                        <th>
                            <div class="row justify-content-center text-center titleCons">
                                Medida
                            </div>
                        </th>
                        <th>
                            <div class="row justify-content-center text-center titleCons">
                                Valor
                            </div>
                        </th>
                        <th>
                            <div class="row justify-content-center text-center titleCons">
                                Operações
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody> <!-- define o corpo da tabela -->
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td>
                                <div class="row justify-content-center registro">
                                    <?php echo ($registro['codPro']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="row justify-content-center registro">
                                    <?php echo ($registro['nomeExib']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="row justify-content-center registro">
                                    <?php echo ($registro['nomeCat']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="row justify-content-center registro">
                                    <?php echo ($registro['medida']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="row justify-content-center registro">
                                    <?php echo ($registro['valor']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="row text-center justify-content-center operacoes">
                                    <div class="col-3 oprBtn">
                                        <form method="POST">
                                            <input type="hidden" name="codPro" value="<?php echo $registro['codPro']; ?>">
                                            <button type="submit" name="delete" class="btn btn-outline-danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                    <path
                                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-3 oprBtn">
                                        <form method="POST">
                                            <input type="hidden" name="codPro" value="<?php echo $registro['codPro']; ?>">
                                            <button type="submit" name="edit" class="btn btn-outline-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                    <path
                                                        d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                    <path fill-rule="evenodd"
                                                        d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>