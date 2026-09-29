<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class Post
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Статья по slug.
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM posts WHERE slug = :slug LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Категории, к которым привязана статья.
     */
    public function getCategories(int $postId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*
            FROM categories c
            INNER JOIN post_categories pc ON c.id = pc.category_id
            WHERE pc.post_id = :post_id
            ORDER BY c.name ASC
        ");
        $stmt->execute(['post_id' => $postId]);

        return $stmt->fetchAll();
    }

    /**
     * Инкремент просмотров.
     */
    public function incrementViews(int $postId): void
    {
        $stmt = $this->db->prepare("
            UPDATE posts SET views = views + 1 WHERE id = :id
        ");
        $stmt->execute(['id' => $postId]);
    }

    /**
     * Похожие статьи.
     * 
     * Логика: выбираем статьи, которые имеют общие категории 
     * с текущей статьёй. Сортируем по количеству общих категорий 
     * (чем больше — тем «похожее»), затем по дате.
     */
    public function getSimilar(int $postId, int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, COUNT(pc2.category_id) AS common_categories_count
            FROM posts p
            INNER JOIN post_categories pc2 ON p.id = pc2.post_id
            WHERE pc2.category_id IN (
                SELECT category_id 
                FROM post_categories 
                WHERE post_id = :post_id_1
            )
            AND p.id != :post_id_2
            GROUP BY p.id
            ORDER BY common_categories_count DESC, p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':post_id_1', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':post_id_2', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Привязать категории к статье (Many-to-Many).
     * Использует транзакцию для целостности данных.
     *
     * @param int[] $categoryIds
     */
    public function attachCategories(int $postId, array $categoryIds): void
    {
        $this->db->beginTransaction();

        try {
            // Сначала удаляем старые связи
            $stmt = $this->db->prepare("
                DELETE FROM post_categories WHERE post_id = :post_id
            ");
            $stmt->execute(['post_id' => $postId]);

            // Вставляем новые
            $stmt = $this->db->prepare("
                INSERT INTO post_categories (post_id, category_id) 
                VALUES (:post_id, :category_id)
            ");

            foreach ($categoryIds as $categoryId) {
                $stmt->execute([
                    'post_id'     => $postId,
                    'category_id' => $categoryId,
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}