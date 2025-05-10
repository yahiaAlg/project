<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Create New Loan</h2>
        </div>
        <div class="card-body">
            <?php if (isset($errors) && count($errors) > 0): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="/loans/store" method="POST">
                <div class="form-group">
                    <label for="book_id" class="form-label">Book:</label>
                    <select name="book_id" id="book_id" class="form-select" required>
                        <option value="">Select a book</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book['id'] ?>" <?= isset($loan['book_id']) && $loan['book_id'] == $book['id'] ? 'selected' : '' ?>>
                                <?= e($book['title']) ?> by <?= e($book['author']) ?> (<?= $book['available_copies'] ?> available)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="member_id" class="form-label">Member:</label>
                    <select name="member_id" id="member_id" class="form-select" required>
                        <option value="">Select a member</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>" <?= isset($loan['member_id']) && $loan['member_id'] == $member['id'] ? 'selected' : '' ?>>
                                <?= e($member['name']) ?> (<?= e($member['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="due_date" class="form-label">Due Date:</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" 
                            value="<?= isset($loan['due_date']) ? $loan['due_date'] : $default_due_date ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="notes" class="form-label">Notes:</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"><?= isset($loan['notes']) ? e($loan['notes']) : '' ?></textarea>
                </div>
                
                <div class="form-group mt-4">
                    <a href="/loans" class="btn btn-outline-primary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Loan</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php $this->partial('_footer'); ?>
</div>