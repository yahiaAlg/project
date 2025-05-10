<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Reading History</h2>
        </div>
        <div class="card-body">
            <?php if (count($loans) > 0): ?>
                <div class="book-grid">
                    <?php foreach ($loans as $loan): ?>
                        <div class="card">
                            <div class="d-flex p-3">
                                <img src="<?= !empty($loan['book_cover']) ? e($loan['book_cover']) : '/assets/images/default-book.jpg' ?>" 
                                        alt="<?= e($loan['book_title']) ?>" class="rounded mr-3" style="width: 80px; height: 120px; object-fit: cover;">
                                
                                <div class="flex-grow-1">
                                    <h4 class="mb-1"><?= e($loan['book_title']) ?></h4>
                                    <p class="text-muted mb-2"><?= e($loan['book_author']) ?></p>
                                    
                                    <div class="book-meta-item">
                                        <span class="book-meta-label">Borrowed:</span>
                                        <span><?= format_date($loan['issue_date']) ?></span>
                                    </div>
                                    
                                    <div class="book-meta-item">
                                        <span class="book-meta-label">Returned:</span>
                                        <span><?= format_date($loan['returned_at']) ?></span>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <a href="/loans/<?= $loan['id'] ?>" class="btn btn-sm btn-primary">Details</a>
                                        <a href="/books/<?= $loan['book_id'] ?>" class="btn btn-sm btn-outline-primary">View Book</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <ul class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li><a href="/loans/history?page=<?= $pagination['current_page'] - 1 ?>" class="page-link">Previous</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li>
                                    <a href="/loans/history?page=<?= $i ?>" 
                                        class="page-link <?= $i == $pagination['current_page'] ? 'current' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li><a href="/loans/history?page=<?= $pagination['current_page'] + 1 ?>" class="page-link">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <p>You haven't borrowed any books yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php $this->partial('_footer'); ?>
</div>