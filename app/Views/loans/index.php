<?php $this->partial('_header', ['pageTitle' => 'Loans']); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">Loans</h2>
            <?php if (is_admin()): ?>
                <a href="/loans/create" class="btn btn-primary">
                    <i class="material-icons mr-1">add</i>
                    New Loan
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Filters -->
        <form action="/loans" method="GET" class="mt-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <select name="status" class="form-select">
                            <option value="all">All Loans</option>
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active Loans</option>
                            <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
                            <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="material-icons mr-1">filter_list</i>
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="card-body">
        <?php if (empty($loans)): ?>
            <div class="text-center py-5">
                <i class="material-icons" style="font-size: 48px; color: var(--gray-400);">book</i>
                <p class="mt-3">No loans found matching your criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book</th>
                            <?php if (is_admin()): ?>
                                <th>Member</th>
                            <?php endif; ?>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?= e($loan['id']) ?></td>
                                <td>
                                    <a href="/books/<?= $loan['book_id'] ?>"><?= e($loan['book_title']) ?></a>
                                    <br>
                                    <small class="text-muted"><?= e($loan['book_author']) ?></small>
                                </td>
                                <?php if (is_admin()): ?>
                                    <td>
                                        <a href="/members/<?= $loan['member_id'] ?>"><?= e($loan['member_name']) ?></a>
                                        <br>
                                        <small class="text-muted"><?= e($loan['member_email']) ?></small>
                                    </td>
                                <?php endif; ?>
                                <td><?= format_date($loan['issue_date']) ?></td>
                                <td><?= format_date($loan['due_date']) ?></td>
                                <td>
                                    <?php if ($loan['returned_at']): ?>
                                        <span class="badge badge-success">Returned</span>
                                    <?php elseif (strtotime($loan['due_date']) < time()): ?>
                                        <span class="badge badge-danger">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/loans/<?= $loan['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="material-icons">visibility</i>
                                    </a>
                                    <?php if (!$loan['returned_at']): ?>
                                        <?php if (is_admin()): ?>
                                            <a href="/loans/<?= $loan['id'] ?>/return" class="btn btn-sm btn-outline-success">
                                                <i class="material-icons">check</i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/loans/<?= $loan['id'] ?>/renew" class="btn btn-sm btn-outline-info">
                                            <i class="material-icons">refresh</i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="mt-4">
                    <?= paginate(
                        $pagination['current_page'],
                        $pagination['total_pages'],
                        '/loans?page=:page' . ($status !== 'all' ? '&status=' . urlencode($status) : '')
                    ) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->partial('_footer'); ?>