<div class="main-content">
    <?php $this->partial('_header'); ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Loans</h2>
            <a href="/loans/create" class="btn btn-primary">
                <i class="material-icons mr-1">add</i> New Loan
            </a>
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
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Member</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td><?= e($loan['book_title']) ?></td>
                                    <td><?= e($loan['member_name']) ?></td>
                                    <td><?= format_date($loan['issue_date']) ?></td>
                                    <td><?= format_date($loan['due_date']) ?></td>
                                    <td>
                                        <?php if ($loan['returned_at']): ?>
                                            <span class="badge badge-success">Returned</span>
                                        <?php elseif (strtotime($loan['due_date']) < time()): ?>
                                            <span class="badge badge-danger">Overdue</span>
                                        <?php else: ?>
                                            <span class="badge available">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/loans/<?= $loan['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        
                                        <?php if (!$loan['returned_at']): ?>
                                            <a href="/loans/<?= $loan['id'] ?>/return" class="btn btn-sm btn-success">Return</a>
                                            <a href="/loans/<?= $loan['id'] ?>/renew" class="btn btn-sm btn-outline-primary">Renew</a>
                                        <?php endif; ?>
                                        
                                        <a href="/loans/<?= $loan['id'] ?>/delete" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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