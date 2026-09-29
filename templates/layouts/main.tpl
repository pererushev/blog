<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$pageTitle|default:'Блог'} | Мой Блог</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    {include file='components/header.tpl'}
    
    <main class="container">
        {block name='content'}{/block}
    </main>
    
    {include file='components/footer.tpl'}
</body>
</html>