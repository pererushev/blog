{extends file='layouts/main.tpl'}

{block name='content'}
<h1>Добро пожаловать в блог</h1>

{if isset($categories) && $categories}
    {foreach $categories as $category}
        <section class="category-section">
            <div class="category-section__header">
                <h2 class="category-section__title">{$category.name|escape}</h2>
                <a href="/category/{$category.slug}" class="category-section__link">
                    Все статьи →
                </a>
            </div>
            
            {if isset($latestPostsByCategory[$category.id])}
                <div class="posts-grid">
                    {foreach $latestPostsByCategory[$category.id] as $post}
                        {include file='components/post_card.tpl'}
                    {/foreach}
                </div>
            {/if}
        </section>
    {/foreach}
{else}
    <p>Пока нет статей. Запустите сидер!</p>
{/if}
{/block}