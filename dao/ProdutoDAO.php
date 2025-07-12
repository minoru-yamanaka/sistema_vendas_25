<?php

require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../database/Database.php';

class ProdutoDAO
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function mapObject(array $row): Produto
    {
        $usuarioAtualizacao = null;
        if (!empty($row['usuario_atualizacao'])) {
            $usuarioDao = new UsuarioDAO();
            $usuarioAtualizacao = $usuarioDao->getById($row['usuario_atualizacao']);
        }

        $categoria = null;
        if (!empty($row['categoria_id'])) {
            $categoriaDao = new CategoriaDAO(); 
            $categoria = $categoriaDao->getById($row['categoria_id']);
        }

        // Debug: verificar se o campo imagemUrl existe na consulta
        $imagemUrl = null;
        if (array_key_exists('imagemUrl', $row)) {
            $imagemUrl = $row['imagemUrl'];
        }

        return new Produto(
            $row['id'],
            $row['nome'],
            $row['descricao'],
            (float)$row['preco'],
            $categoria,
            (bool)$row['ativo'],
            $row['data_criacao'],
            $row['data_atualizacao'],
            $usuarioAtualizacao,
            $imagemUrl // Usando a variável verificada
        );
    }

    public function create(Produto $produto, int $usuarioId): bool
    {
        $sql = "INSERT INTO produto (nome, descricao, preco, categoria_id, usuario_atualizacao, imagemUrl) 
                VALUES (:nome, :descricao, :preco, :categoria_id, :user_id, :imagemUrl)";
        $stmt = $this->db->prepare($sql);

        $categoriaId = $produto->getCategoria() ? $produto->getCategoria()->getId() : null;

        return $stmt->execute([
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':preco' => $produto->getPreco(),
            ':categoria_id' => $categoriaId,
            ':user_id' => $usuarioId,
            ':imagemUrl' => $produto->getImagemUrl()
        ]);
    }

    public function update(Produto $produto, int $usuarioId): bool
    {
        $sql = "UPDATE produto SET 
                    nome = :nome, 
                    descricao = :descricao, 
                    preco = :preco, 
                    categoria_id = :categoria_id, 
                    ativo = :ativo,
                    usuario_atualizacao = :user_id,
                    imagemUrl = :imagemUrl 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $categoriaId = $produto->getCategoria() ? $produto->getCategoria()->getId() : null;

        return $stmt->execute([
            ':id' => $produto->getId(),
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':preco' => $produto->getPreco(),
            ':categoria_id' => $categoriaId,
            ':ativo' => (int)$produto->isAtivo(),
            ':user_id' => $usuarioId,
            ':imagemUrl' => $produto->getImagemUrl()
        ]);
    }

    public function getAll(bool $somenteAtivos = true): array
    {
        // Certificar que o SELECT inclui o campo imagemUrl
        $sql = "SELECT id, nome, descricao, preco, categoria_id, ativo, data_criacao, data_atualizacao, usuario_atualizacao, imagemUrl FROM produto" . 
               ($somenteAtivos ? " WHERE ativo = 1" : "") . " ORDER BY nome";
        $stmt = $this->db->query($sql);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[] = $this->mapObject($row);
        }
        return $result;
    }

    public function getById(int $id): ?Produto
    {
        // Certificar que o SELECT inclui o campo imagemUrl
        $stmt = $this->db->prepare("SELECT id, nome, descricao, preco, categoria_id, ativo, data_criacao, data_atualizacao, usuario_atualizacao, imagemUrl FROM produto WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        return $data ? $this->mapObject($data) : null;
    }

    public function softDelete(int $id, int $usuarioId): bool
    {
        $sql = "UPDATE produto SET ativo = 0, usuario_atualizacao = :user_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $usuarioId]);
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM produto WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}