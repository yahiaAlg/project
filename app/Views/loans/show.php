<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="book-detail">
        <img src="<?= !empty($loan['book_cover']) ? e($loan['book_cover']) : '/assets/images/default-book.jpg' ?>" 
                alt="<?= e($loan['book_title']) ?>" class="book-cover">
        
        <div class="book-info">
            <h2><?= e($loan['book_title']) ?></h2>
            <p class="text-secondary mb-2"><?= e($loan['book_author']) ?></p>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Loan Information</h3>
                </div>
                <div class="card-body">
                    <div class="book-meta">
                        <div class="book-meta-item">
                            <span class="book-meta-label">Loan ID:</span>
                            <span><?= e($loan['id']) ?></span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Status:</span>
                            <span>
                                <?php if ($loan['returned_at']): ?>
                                    <span class="badge badge-success">Returned on <?= format_date($loan['returned_at']) ?></span>
                                <?php elseif (strtotime($loan['due_date']) < time()): ?>
                                    <span class="badge badge-danger">Overdue by <?= days_difference($loan['due_date'], date('Y-m-d')) ?> days</span>
                                <?php else: ?>
                                    <span class="badge available">Active</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Borrowed by:</span>
                            <span><?= e($loan['member_name']) ?> (<?= e($loan['member_email']) ?>)</span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Issue Date:</span>
                            <span><?= format_date($loan['issue_date']) ?></span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Due Date:</span>
                            <span><?= format_date($loan['due_date']) ?></span>
                        </div>
                        
                        <?php if (!$loan['returned_at'] && $fine > 0): ?>
                            <div class="book-meta-item">
                                <span class="book-meta-label">Current Fine:</span>
                                <span class="text-danger">$<?= number_format($fine, 2) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($loan['returned_at'] && $loan['fine_amount'] > 0): ?>
                            <div class="book-meta-item">
                                <span class="book-meta-label">Fine Paid:</span>
                                <span>$<?= number_format($loan['fine_amount'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($loan['notes'])): ?>
                            <div class="book-meta-item">
                                <span class="book-meta-label">Notes:</span>
                                <span><?= nl2br(e($loan['notes'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-actions mt-4">
                        <a href="/loans" class="btn btn-outline-primary">Back to Loans</a>
                        
                        <?php if (!$loan['returned_at']): ?>
                            <?php if (is_admin()): ?>
                                <a href="/loans/<?= $loan['id'] ?>/return" class="btn btn-success">Return Book</a>
                            <?php endif; ?>
                            
                            <a href="/loans/<?= $loan['id'] ?>/renew" class="btn btn-primary">Renew Loan</a>
                        <?php endif; ?>
                        
                        <?php if (is_admin()): ?>
                            <a href="/loans/<?= $loan['id'] ?>/delete" class="btn btn-danger">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php $this->partial('_footer'); ?>
</div>