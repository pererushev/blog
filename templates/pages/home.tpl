<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Blog</title>
</head>
<body>
    <h1>Блог</h1>
    {if $categories}
        <ul>
        {foreach $categories as $cat}
            <li>{$cat.name|escape} ({$cat.posts_count} статей)</li>
        {/foreach}
        </ul>
    {else}
        <p>Пока нет категорий со статьями. Запусти сидер!</p>
    {/if}
</body>
</html>