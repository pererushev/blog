<article class="post-card">
    {if $post.image}
        <img src="{$post.image}" alt="{$post.title|escape}" class="post-card__image">
    {/if}
    <div class="post-card__content">
        <h3 class="post-card__title">
            <a href="/post/{$post.slug}">{$post.title|escape}</a>
        </h3>
        <p class="post-card__description">{$post.description|escape|truncate:150}</p>
        <div class="post-card__meta">
            <span class="post-card__date">{$post.created_at|date_format:'%d.%m.%Y'}</span>
            <span class="post-card__views">👁 {$post.views}</span>
        </div>
    </div>
</article>