<?php $this->partial('_header', ['pageTitle' => 'My Profile']); ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="material-icons" style="font-size: 64px; color: var(--primary);">account_circle</i>
                    <h2 class="mt-2"><?= e($member['name']) ?></h2>
                    <span class="badge <?= $member['role'] === 'librarian' ? 'badge-primary' : 'badge-secondary' ?>">
                        <?= ucfirst(e($member['role'])) ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Email</label>
                    <p><?= e($member['email']) ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Phone</label>
                    <p><?= e($member['phone']) ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Address</label>
                    <p><?= nl2br(e($member['address'])) ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Member Since</label>
                    <p><?= format_date($member['created_at'], 'F j, Y') ?></p>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Account Statistics</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Active Loans</span>
                    <span class="badge <?= $stats['active_loans'] > 0 ? 'badge-primary' : 'badge-secondary' ?>">
                        <?= $stats['active_loans'] ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Overdue Books</span>
                    <span class="badge <?= $stats['overdue_loans'] > 0 ? 'badge-danger' : 'badge-secondary' ?>">
                        <?= $stats['overdue_loans'] ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total Books Borrowed</span>
                    <span class="badge badge-info"><?= $stats['total_loans'] ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Update Profile</h3>
            </div>
            <div class="card-body">
                <form action="/members/profile" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="<?= e($member['name']) ?>">
                        <?php if (isset($errors['name'])): ?>
                            <div class="form-text text-danger"><?= $errors['name'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?= e($member['email']) ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-text text-danger"><?= $errors['email'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required
                               value="<?= e($member['phone']) ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="form-text text-danger"><?= $errors['phone'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3" required><?= e($member['address']) ?></textarea>
                        <?php if (isset($errors['address'])): ?>
                            <div class="form-text text-danger"><?= $errors['address'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-control">
                        <div class="form-text">Leave blank to keep current password</div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-text text-danger"><?= $errors['password'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control">
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="form-text text-danger"><?= $errors['password_confirm'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="material-icons mr-1">save</i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($stats['recent_books'])): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Recent Books</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Borrowed</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent_books'] as $book): ?>
                                    <tr>
                                        <td><?= e($book['title']) ?></td>
                                        <td><?= format_date($book['issue_date']) ?></td>
                                        <td><?= format_date($book['due_date']) ?></td>
                                        <td>
                                            <?php if ($book['returned_at']): ?>
                                                <span class="badge badge-success">Returned</span>
                                            <?php elseif (strtotime($book['due_date']) < time()): ?>
                                                <span class="badge badge-danger">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge badge-primary">Active</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->partial('_footer'); ?>