<?php $this->partial('_header', ['pageTitle' => 'Members']); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">Members</h2>
            <?php if (is_admin()): ?>
                <a href="/members/create" class="btn btn-primary">
                    <i class="material-icons mr-1">person_add</i>
                    Add New Member
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Search and Filters -->
        <form action="/members" method="GET" class="mt-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="text" name="query" class="form-control" 
                               placeholder="Search by name, email, or phone"
                               value="<?= isset($filters['query']) ? e($filters['query']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="status" class="form-select">
                            <option value="all">All Status</option>
                            <option value="active" <?= isset($filters['status']) && $filters['status'] === 'active' ? 'selected' : '' ?>>
                                Active
                            </option>
                            <option value="inactive" <?= isset($filters['status']) && $filters['status'] === 'inactive' ? 'selected' : '' ?>>
                                Inactive
                            </option>
                            <option value="suspended" <?= isset($filters['status']) && $filters['status'] === 'suspended' ? 'selected' : '' ?>>
                                Suspended
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="material-icons mr-1">search</i>
                        Search
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="card-body">
        <?php if (empty($members)): ?>
            <div class="text-center py-5">
                <i class="material-icons" style="font-size: 48px; color: var(--gray-400);">people</i>
                <p class="mt-3">No members found matching your criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?= e($member['id']) ?></td>
                                <td><?= e($member['name']) ?></td>
                                <td><?= e($member['email']) ?></td>
                                <td><?= e($member['phone']) ?></td>
                                <td>
                                    <span class="badge <?= $member['role'] === 'librarian' ? 'badge-primary' : 'badge-secondary' ?>">
                                        <?= ucfirst(e($member['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= 
                                        $member['status'] === 'active' ? 'badge-success' : 
                                        ($member['status'] === 'inactive' ? 'badge-warning' : 'badge-danger')
                                    ?>">
                                        <?= ucfirst(e($member['status'])) ?>
                                    </span>
                                </td>
                                <td><?= format_date($member['created_at'], 'M j, Y') ?></td>
                                <td>
                                    <a href="/members/<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="material-icons">visibility</i>
                                    </a>
                                    <?php if (is_admin()): ?>
                                        <a href="/members/<?= $member['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
                                            <i class="material-icons">edit</i>
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
                        '/members?page=:page' . 
                        (isset($filters['status']) ? '&status=' . urlencode($filters['status']) : '') .
                        (isset($filters['query']) ? '&query=' . urlencode($filters['query']) : '')
                    ) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->partial('_footer'); ?>