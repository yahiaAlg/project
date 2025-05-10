<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Loans</h2>
        </div>
        <div class="card-body">
            <div class="filter-bar mb-4">
                <form action="/loans" method="GET" class="d-flex">
                    <div class="form-group mr-3">
                        <label for="status" class="mr-2">Status:</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $status == 'all' ? 'selected' : '' ?>>All Loans</option>
                            <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active Loans</option>
                            <option value="overdue" <?= $status == 'overdue' ? 'selected' : '' ?>>Overdue Loans</option>
                            <option value="returned" <?= $status == 'returned' ? 'selected' : '' ?>>Returned Books</option>
                        </select>
                    </div>
                </form>
            </div>

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
                                        <span class="book-meta-label">Due Date:</span>
                                        <span><?= format_date($loan['due_date']) ?></span>
                                    </div>
                                    
                                    <div class="book-meta-item">
                                        <span class="book-meta-label">Status:</span>
                                        <span>
                                            <?php if ($loan['returned_at']): ?>
                                                <span class="badge badge-success">Returned</span>
                                            <?php elseif (strtotime($loan['due_date']) < time()): ?>
                                                <span class="badge badge-danger">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge available">Active</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <a href="/loans/<?= $loan['id'] ?>" class="btn btn-sm btn-primary">Details</a>
                                        
                                        <?php if (!$loan['returned_at'] && strtotime($loan['due_date']) >= time()): ?>
                                            <a href="/loans/<?= $loan['id'] ?>/renew" class="btn btn-sm btn-outline-primary">Renew</a>
                                        <?php endif; ?>
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
                                <li><a href="/loans?page=<?= $pagination['current_page'] - 1 ?>&status=<?= $status ?>" class="page-link">Previous</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li>
                                    <a href="/loans?page=<?= $i ?>&status=<?= $status ?>" 
                                        class="page-link <?= $i == $pagination['current_page'] ? 'current' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li><a href="/loans?page=<?= $pagination['current_page'] + 1 ?>&status=<?= $status ?>" class="page-link">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    No loans found. <?php if ($status != 'all'): ?>Try changing the filter.<?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php $this->partial('_footer'); ?>
</div>