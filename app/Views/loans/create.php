<?php $this->partial('_header', ['pageTitle' => 'Create New Loan']); ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Create New Loan</h2>
    </div>
    
    <div class="card-body">
        <form action="/loans" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="book_id" class="form-label">Book</label>
                        <select id="book_id" name="book_id" class="form-select" required>
                            <option value="">Select Book</option>
                            <?php foreach ($books as $book): ?>
                                <option value="<?= e($book['id']) ?>" 
                                    <?= isset($loan['book_id']) && $loan['book_id'] == $book['id'] ? 'selected' : '' ?>>
                                    <?= e($book['title']) ?> by <?= e($book['author']) ?> 
                                    (<?= e($book['available_copies']) ?> available)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['book_id'])): ?>
                            <div class="form-text text-danger"><?= $errors['book_id'][0] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="member_id" class="form-label">Member</label>
                        <select id="member_id" name="member_id" class="form-select" required>
                            <option value="">Select Member</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= e($member['id']) ?>"
                                    <?= isset($loan['member_id']) && $loan['member_id'] == $member['id'] ? 'selected' : '' ?>>
                                    <?= e($member['name']) ?> (<?= e($member['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['member_id'])): ?>
                            <div class="form-text text-danger"><?= $errors['member_id'][0] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" required
                               min="<?= date('Y-m-d') ?>"
                               value="<?= isset($loan['due_date']) ? e($loan['due_date']) : e($default_due_date) ?>">
                        <?php if (isset($errors['due_date'])): ?>
                            <div class="form-text text-danger"><?= $errors['due_date'][0] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?= isset($loan['notes']) ? e($loan['notes']) : '' ?></textarea>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons mr-1">save</i>
                    Create Loan
                </button>
                <a href="/loans" class="btn btn-outline-primary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->partial('_footer'); ?>