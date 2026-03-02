<?php
if (!function_exists('render_pagination')) {
    /**
     * Render pagination controls
     *
     * @param int $currentPage Current page number
     * @param int $totalPages Total number of pages
     * @param string $endpoint The PHP file to fetch (without .php)
     */
    function render_pagination(int $currentPage, int $totalPages, string $endpoint): void {
        if ($totalPages <= 1) return;
        ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="#" class="page-link" data-endpoint="<?php echo $endpoint; ?>" data-page="<?php echo $currentPage - 1; ?>">Previous</a>
            <?php endif; ?>

            <span>Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>

            <?php if ($currentPage < $totalPages): ?>
                <a href="#" class="page-link" data-endpoint="<?php echo $endpoint; ?>" data-page="<?php echo $currentPage + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
        <?php
    }
}
