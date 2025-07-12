<?php
// Arquivo debug para verificar o que está acontecendo com as imagens
require_once __DIR__ . '/model/Entidade.php';
require_once __DIR__ . '/model/Produto.php';
require_once __DIR__ . '/dao/ProdutoDAO.php';
require_once __DIR__ . '/model/Categoria.php';
require_once __DIR__ . '/dao/CategoriaDAO.php';
require_once __DIR__ . '/model/Usuario.php';
require_once __DIR__ . '/dao/UsuarioDAO.php';

echo "<h1>Debug - Produtos e Imagens</h1>";

try {
    $produtoDAO = new ProdutoDAO();
    $produtos = $produtoDAO->getAll(true);
    
    echo "<p>Total de produtos encontrados: " . count($produtos) . "</p>";
    
    foreach ($produtos as $produto) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<h3>Produto ID: " . $produto->getId() . "</h3>";
        echo "<p><strong>Nome:</strong> " . htmlspecialchars($produto->getNome()) . "</p>";
        echo "<p><strong>Preço:</strong> R$ " . number_format($produto->getPreco(), 2, ',', '.') . "</p>";
        
        // Debug da imagem
        $imagemUrl = $produto->getImagemUrl();
        echo "<p><strong>URL da Imagem:</strong> " . htmlspecialchars($imagemUrl) . "</p>";
        
        // Testa se a imagem carrega
        echo "<p><strong>Imagem:</strong></p>";
        echo "<img src='" . htmlspecialchars($imagemUrl) . "' alt='Produto' style='max-width: 200px; max-height: 200px;' 
              onerror=\"this.style.border='2px solid red'; this.alt='ERRO: Imagem não carregou'\">";
        
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// Teste direto do banco
echo "<hr><h2>Debug direto do banco de dados</h2>";
try {
    require_once __DIR__ . '/database/Database.php';
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id, nome, preco, imagemUrl FROM produto WHERE ativo = 1 LIMIT 5");
    $rows = $stmt->fetchAll();
    
    echo "<p>Dados diretos do banco:</p>";
    foreach ($rows as $row) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao acessar banco: " . $e->getMessage() . "</p>";
}
?>