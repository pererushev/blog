<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Все категории, у которых есть хотя бы одна статья.
     */
    public function getAllWithPosts(): array
    {
        $sql = "
            SELECT c.*, COUNT(pc.post_id) AS posts_count
            FROM categories c
            INNER JOIN post_categories pc ON c.id = pc.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Категория по slug (для ЧПУ).
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM categories WHERE slug = :slug LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * 3 последних поста категории (для главной страницы).
     */
    public function getLatestPosts(int $categoryId, int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*
            FROM posts p
            INNER JOIN post_categories pc ON p.id = pc.post_id
            WHERE pc.category_id = :category_id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Посты категории с сортировкой и пагинацией.
     *
     * @param string $sortBy 'date' или 'views'
     */
    public function getPosts(
        int $categoryId,
        string $sortBy = 'date',
        int $page = 1,
        int $perPage = 9
    ): array {
        // Валидация сортировки — защита от инъекций в ORDER BY
        $orderColumn = match ($sortBy) {
            'views' => 'p.views',
            default => 'p.created_at',
        };

        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM posts p
            INNER JOIN post_categories pc ON p.id = pc.post_id
            WHERE pc.category_id = :category_id
            ORDER BY {$orderColumn} DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Общее количество постов в категории (для пагинации).
     */
    public function getPostsCount(int $categoryId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM post_categories
            WHERE category_id = :category_id
        ");
        $stmt->execute(['category_id' => $categoryId]);

        return (int) $stmt->fetch()['total'];
    }

    /**
     * Оптимизированный запрос: последние N постов для нескольких категорий.
     * Использует ROW_NUMBER() (MySQL 8.0+).
     * Решает проблему N+1 на главной странице.
     */
    public function getLatestPostsForCategories(array $categoryIds, int $limit = 3): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        // Динамические плейсхолдеры для IN(...)
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        $sql = "
            SELECT * FROM (
                SELECT 
                    p.*,
                    pc.category_id,
                    ROW_NUMBER() OVER (
                        PARTITION BY pc.category_id 
                        ORDER BY p.created_at DESC
                    ) AS rn
                FROM posts p
                INNER JOIN post_categories pc ON p.id = pc.post_id
                WHERE pc.category_id IN ({$placeholders})
            ) ranked
            WHERE rn <= :limit
            ORDER BY category_id, created_at DESC
        ";

        $stmt = $this->db->prepare($sql);

        // Биндим ID категорий
        foreach ($categoryIds as $i => $id) {
            $stmt->bindValue($i + 1, $id, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        // Группируем результат по category_id
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $catId = $row['category_id'];
            unset($row['category_id'], $row['rn']);
            $grouped[$catId][] = $row;
        }

        return $grouped;
    }
}