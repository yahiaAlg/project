<?php $this->partial('_header', ['pageTitle' => 'register']); ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Register</h2>
            </div>
            <div class="card-body">
                <form action="/register" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?= isset($name) ? e($name) : '' ?>">
                        <?php if (isset($errors['name'])): ?>
                            <div class="form-text text-danger"><?= $errors['name'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?= isset($email) ? e($email) : '' ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-text text-danger"><?= $errors['email'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-text text-danger"><?= $errors['password'][0] ?></div>
                        <?php endif; ?>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="form-text text-danger"><?= $errors['password_confirm'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </div>
                    
                    <p class="text-center mt-3">
                        Already have an account? <a href="/login">Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->partial('_footer'); ?>