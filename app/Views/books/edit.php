<?php $this->partial('_header', ['pageTitle' => 'Edit Book']); ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Edit Book: <?= e($book['title']) ?></h2>
    </div>
    
    <div class="card-body">
        <form action="/books/<?= $book['id'] ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?= e($book['title']) ?>">
                        <?php if (isset($errors['title'])): ?>
                            <div class="form-text text-danger"><?= $errors['title'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" id="author" name="author" class="form-control" required
                               value="<?= e($book['author']) ?>">
                        <?php if (isset($errors['author'])): ?>
                            <div class="form-text text-danger"><?= $errors['author'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="isbn" class="form-label">ISBN</label>
                                <input type="text" id="isbn" name="isbn" class="form-control" required
                                       value="<?= e($book['isbn']) ?>">
                                <?php if (isset($errors['isbn'])): ?>
                                    <div class="form-text text-danger"><?= $errors['isbn'][0] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="published_year" class="form-label">Published Year</label>
                                <input type="number" id="published_year" name="published_year" class="form-control" required
                                       min="1000" max="<?= date('Y') ?>"
                                       value="<?= e($book['published_year']) ?>">
                                <?php if (isset($errors['published_year'])): ?>
                                    <div class="form-text text-danger"><?= $errors['published_year'][0] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select id="category" name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= e($category) ?>" 
                                            <?= $book['category'] === $category ? 'selected' : '' ?>>
                                            <?= e($category) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category'])): ?>
                                    <div class="form-text text-danger"><?= $errors['category'][0] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_copies" class="form-label">Total Copies</label>
                                <input type="number" id="total_copies" name="total_copies" class="form-control" required
                                       min="1" value="<?= e($book['total_copies']) ?>">
                                <?php if (isset($errors['total_copies'])): ?>
                                    <div class="form-text text-danger"><?= $errors['total_copies'][0] ?></div>
                                <?php endif; ?>
                                <div class="form-text">Current available copies: <?= e($book['available_copies']) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="shelf_location" class="form-label">Shelf Location</label>
                        <input type="text" id="shelf_location" name="shelf_location" class="form-control"
                               value="<?= e($book['shelf_location']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?= e($book['description']) ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cover_image" class="form-label">Cover Image</label>
                        <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
                        <div class="form-text">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF</div>
                        <?php if (isset($errors['cover_image'])): ?>
                            <div class="form-text text-danger"><?= $errors['cover_image'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-3">
                        <img src="<?= e($book['cover_image']) ?>" alt="Book cover" class="img-fluid rounded" id="cover-preview">
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons mr-1">save</i>
                    Update Book
                </button>
                <a href="/books/<?= $book['id'] ?>" class="btn btn-outline-primary ml-2">Cancel</a>
                
                <?php if (is_admin()): ?>
                    <a href="/books/<?= $book['id'] ?>/delete" class="btn btn-danger float-right">
                        <i class="material-icons mr-1">delete</i>
                        Delete Book
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('cover_image').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('cover-preview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

<?php $this->partial('_footer'); ?>