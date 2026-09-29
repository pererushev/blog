{extends file='layouts/main.tpl'}

{block name='content'}
<div class="category-page">
    <h1>{$category.name|escape}</h1>
    
    {if $category.description}
        <p class="category-description">{$category.description|escape}</p>
    {/if}
    
    {if $posts}
        <div class="sort-controls">
            <span>Сортировка:</span>
            <a href="?sort=date&page=1" class="sort-controls__link {if $currentSort === 'date'}active{/if}">
                По дате
            </a>
            <a href="?sort=views&page=1" class="sort-controls__link {if $currentSort === 'views'}active{/if}">
                По просмотрам
            </a>
        </div>
        
        <div class="posts-grid">
            {foreach $posts as $post}
                {include file='components/post_card.tpl'}
            {/foreach}
        </div>
        
        {include file='components/pagination.tpl'}
    {else}
        <p>В этой категории пока нет статей.</p>
    {/if}
</div>
{/block}