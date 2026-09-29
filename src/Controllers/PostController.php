<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;

class PostController
{
    private \Smarty $smarty;
    private Post $postModel;

    public function __construct(\Smarty $smarty)
    {
        $this->smarty = $smarty;
        $this->postModel = new Post();
    }

    public function show(string $slug): void
    {
        // Получаем статью по slug
        $post = $this->postModel->getBySlug($slug);
        
        if (!$post) {
            throw new \Exception('Post not found');
        }
        
        // Увеличиваем счётчик просмотров
        $this->postModel->incrementViews($post['id']);
        
        // Получаем категории статьи
        $categories = $this->postModel->getCategories($post['id']);
        
        // Получаем похожие статьи
        $similarPosts = $this->postModel->getSimilar($post['id'], 3);
        
        // Присваиваем данные в шаблон
        $this->smarty->assign('post', $post);
        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('similarPosts', $similarPosts);
        $this->smarty->assign('pageTitle', $post['title']);
        $this->smarty->assign('currentUri', "/post/{$slug}");
        
        // Отображаем шаблон
        $this->smarty->display('pages/post.tpl');
    }
}