{extends file='layouts/main.tpl'}

{block name='content'}
<article class="post-page">
    {if $post.image}
        <img src="{$post.image}" alt="{$post.title|escape}" class="post-page__image">
    {/if}
    
    <h1 class="post-page__title">{$post.title|escape}</h1>
    
    <div class="post-page__meta">
        <span>📅 {$post.created_at|date_format:'%d.%m.%Y'}</span>
        <span>👁 {$post.views} просмотров</span>
        {if $categories}
            <span>📂 
                {foreach $categories as $cat}
                    <a href="/category/{$cat.slug}">{$cat.name|escape}</a>{if !$cat@last}, {/if}
                {/foreach}
            </span>
        {/if}
    </div>
    
    <div class="post-page__content">
        {$post.content}
    </div>
</article>

{if $similarPosts}
<section class="similar-posts">
    <h2 class="similar-posts__title">Похожие статьи</h2>
    <div class="posts-grid">
        {foreach $similarPosts as $post}
            {include file='components/post_card.tpl'}
        {/foreach}
    </div>
</section>
{/if}
{/block}