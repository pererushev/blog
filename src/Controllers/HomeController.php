<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;

class HomeController
{
    private \Smarty $smarty;
    private Category $categoryModel;

    public function __construct(\Smarty $smarty)
    {
        $this->smarty = $smarty;
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        // Получаем все категории, у которых есть статьи
        $categories = $this->categoryModel->getAllWithPosts();
        
        // Собираем ID категорий для оптимизированного запроса
        $categoryIds = array_column($categories, 'id');
        
        // Получаем последние 3 поста для каждой категории (один запрос вместо N+1)
        $latestPostsByCategory = $this->categoryModel->getLatestPostsForCategories($categoryIds, 3);
        
        // Присваиваем данные в шаблон
        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('latestPostsByCategory', $latestPostsByCategory);
        $this->smarty->assign('pageTitle', 'Главная');
        $this->smarty->assign('currentUri', '/');
        
        // Отображаем шаблон
        $this->smarty->display('pages/home.tpl');
    }
}