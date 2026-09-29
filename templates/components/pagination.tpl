{if $totalPages > 1}
<nav class="pagination">
    {if $currentPage > 1}
        <a href="?page={$currentPage - 1}&sort={$currentSort}" class="pagination__link">← Назад</a>
    {/if}
    
    {for $page=1 to $totalPages}
        {if $page === $currentPage}
            <span class="pagination__link pagination__link--active">{$page}</span>
        {else}
            <a href="?page={$page}&sort={$currentSort}" class="pagination__link">{$page}</a>
        {/if}
    {/for}
    
    {if $currentPage < $totalPages}
        <a href="?page={$currentPage + 1}&sort={$currentSort}" class="pagination__link">Вперёд →</a>
    {/if}
</nav>
{/if}