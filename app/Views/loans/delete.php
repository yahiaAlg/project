<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Delete Loan</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <h4 class="alert-heading">Warning!</h4>
                <p>You are about to delete a loan record. This action cannot be undone.</p>
                <p>If the book is still on loan, deleting this record will also increase the book's available copies.</p>
            </div>
            
            <div class="book-detail mb-4">
                <img src="<?= !empty($loan['book_cover']) ? e($loan['book_cover']) : '/assets/images/default-book.jpg' ?>" 
                        alt="<?= e($loan['book_title']) ?>" class="book-cover" style="max-width: 150px;">
                
                <div class="book-info">
                    <h3><?= e($loan['book_title']) ?></h3>
                    <p class="text-secondary mb-2"><?= e($loan['book_author']) ?></p>
                    
                    <div class="book-meta">
                        <div class="book-meta-item">
                            <span class="book-meta-label">Loan ID:</span>
                            <span><?= e($loan['id']) ?></span>
                        </div>
                        
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
                                <?php if ($loan['returned_at']): ?>
                                    <span class="badge badge-success">Returned on <?= format_date($loan['returned_at']) ?></span>
                                <?php elseif (strtotime($loan['due_date']) < time()): ?>
                                    <span class="badge badge-danger">Overdue</span>
                                <?php else: ?>
                                    <span class="badge available">Active</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form action="/loans/<?= $loan['id'] ?>/destroy" method="POST">
                <div class="form-group mt-4">
                    <a href="/loans/<?= $loan['id'] ?>" class="btn btn-outline-primary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php $this->partial('_footer'); ?>
</div>