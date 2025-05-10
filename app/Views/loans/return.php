<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Return Book</h2>
        </div>
        <div class="card-body">
            <div class="book-detail mb-4">
                <img src="<?= !empty($loan['book_cover']) ? e($loan['book_cover']) : '/assets/images/default-book.jpg' ?>" 
                        alt="<?= e($loan['book_title']) ?>" class="book-cover" style="max-width: 150px;">
                
                <div class="book-info">
                    <h3><?= e($loan['book_title']) ?></h3>
                    <p class="text-secondary mb-2"><?= e($loan['book_author']) ?></p>
                    
                    <div class="book-meta">
                        <div class="book-meta-item">
                            <span class="book-meta-label">Borrowed by:</span>
                            <span><?= e($loan['member_name']) ?></span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Issue Date:</span>
                            <span><?= format_date($loan['issue_date']) ?></span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Due Date:</span>
                            <span><?= format_date($loan['due_date']) ?></span>
                        </div>
                        
                        <div class="book-meta-item">
                            <span class="book-meta-label">Status:</span>
                            <span>
                                <?php if (strtotime($loan['due_date']) < time()): ?>
                                    <span class="badge badge-danger">Overdue by <?= days_difference($loan['due_date'], date('Y-m-d')) ?> days</span>
                                <?php else: ?>
                                    <span class="badge available">Active</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form action="/loans/<?= $loan['id'] ?>/return" method="POST">
                <?php if ($fine > 0): ?>
                    <div class="alert alert-warning">
                        <strong>This book is overdue!</strong> There is a fine of $<?= number_format($fine, 2) ?>.
                    </div>
                    
                    <div class="form-group">
                        <label for="fine_amount" class="form-label">Fine Amount:</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="fine_amount" id="fine_amount" class="form-control" 
                                    value="<?= number_format($fine, 2) ?>" required>
                        </div>
                        <div class="form-text">You can adjust the fine amount if needed.</div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="fine_amount" value="0">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="notes" class="form-label">Notes:</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group mt-4">
                    <a href="/loans/<?= $loan['id'] ?>" class="btn btn-outline-primary">Cancel</a>
                    <button type="submit" class="btn btn-success">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php $this->partial('_footer'); ?>
</div>