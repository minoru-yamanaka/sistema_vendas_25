<?php
require_once __DIR__ . '/../../services/authService.php';
requireAdmin();

// Inclui todos os arquivos necessários
require_once __DIR__ . '/../../model/Entidade.php';
require_once __DIR__ . '/../../model/Produto.php';
require_once __DIR__ . '/../../dao/ProdutoDAO.php';
require_once __DIR__ . '/../../model/Categoria.php';
require_once __DIR__ . '/../../dao/CategoriaDAO.php';
require_once __DIR__ . '/../../model/Usuario.php';
require_once __DIR__ . '/../../dao/UsuarioDAO.php';

// Simulação de ID de usuário logado (admin)
$usuario_logado_id = 1;

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$produtoDAO = new ProdutoDAO();
$categoriaDAO = new CategoriaDAO();

switch ($acao) {
    case 'criar':
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $preco = (float)($_POST['preco'] ?? 0);
        $imagemUrl = $_POST['imagemUrl'] ?? null;
        $categoria_id = $_POST['categoria_id'] ?? null;

        $categoria = $categoria_id ? $categoriaDAO->getById($categoria_id) : null;

        $novoProduto = new Produto(
            null, $nome, $descricao, $preco, $categoria, true,
            null, null, null, $imagemUrl
        );

        $produtoDAO->create($novoProduto, $usuario_logado_id);
        header('Location: index.php');
        exit;

    case 'editar':
        $id = $_POST['id'] ?? 0;
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $preco = (float)($_POST['preco'] ?? 0);
        $categoria_id = $_POST['categoria_id'] ?? null;
        $ativo = isset($_POST['ativo']) ? (bool)$_POST['ativo'] : false;
        $imagemUrl = $_POST['imagemUrl'] ?? null;

        $categoria = $categoria_id ? $categoriaDAO->getById($categoria_id) : null;
        $produtoExistente = $produtoDAO->getById($id);

        if ($produtoExistente) {
            $produtoAtualizado = new Produto(
                $id, $nome, $descricao, $preco, $categoria, $ativo,
                $produtoExistente->getDataCriacao(),
                null,
                null,
                $imagemUrl
            );

            $produtoDAO->update($produtoAtualizado, $usuario_logado_id);
        }

        header('Location: index.php');
        exit;

    case 'excluir':
        $id = $_POST['id'] ?? 0;
        $produtoDAO->softDelete($id, $usuario_logado_id);
        header('Location: index.php');
        exit;
}
?>
