<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;

class CategoryController
{
    private \Smarty $smarty;
    private Category $categoryModel;

    public function __construct(\Smarty $smarty)
    {
        $this->smarty = $smarty;
        $this->categoryModel = new Category();
    }

    public function show(string $slug): void
    {
        // Получаем категорию по slug
        $category = $this->categoryModel->getBySlug($slug);
        
        if (!$category) {
            throw new \Exception('Category not found');
        }
        
        // Валидация и получение параметров сортировки
        $sortBy = $_GET['sort'] ?? 'date';
        $sortBy = in_array($sortBy, ['date', 'views']) ? $sortBy : 'date';
        
        // Пагинация
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        
        // Получаем посты с сортировкой и пагинацией
        $posts = $this->categoryModel->getPosts($category['id'], $sortBy, $currentPage, $perPage);
        
        // Общее количество постов для расчёта пагинации
        $totalPosts = $this->categoryModel->getPostsCount($category['id']);
        $totalPages = (int)ceil($totalPosts / $perPage);
        
        // Присваиваем данные в шаблон
        $this->smarty->assign('category', $category);
        $this->smarty->assign('posts', $posts);
        $this->smarty->assign('currentPage', $currentPage);
        $this->smarty->assign('totalPages', $totalPages);
        $this->smarty->assign('currentSort', $sortBy);
        $this->smarty->assign('pageTitle', $category['name']);
        $this->smarty->assign('currentUri', "/category/{$slug}");
        
        // Отображаем шаблон
        $this->smarty->display('pages/category.tpl');
    }
}