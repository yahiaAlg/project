<?php $this->partial('_header', ['pageTitle' => $book['title']]); ?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <img src="<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>" class="img-fluid rounded shadow">
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge <?= $book['available_copies'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                            <?= $book['available_copies'] > 0 ? 'Available' : 'Unavailable' ?>
                        </span>
                        <span class="text-muted">
                            <?= $book['available_copies'] ?>/<?= $book['total_copies'] ?> copies available
                        </span>
                    </div>
                    
                    <?php if (is_logged_in()): ?>
                        <?php if ($book['available_copies'] > 0): ?>
                            <a href="/loans/create?book_id=<?= $book['id'] ?>" class="btn btn-primary w-100">
                                <i class="material-icons mr-1">book</i>
                                Borrow Book
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="material-icons mr-1">block</i>
                                Currently Unavailable
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary w-100">
                            <i class="material-icons mr-1">login</i>
                            Login to Borrow
                        </a>
                    <?php endif; ?>
                    
                    <?php if (is_admin()): ?>
                        <div class="mt-3">
                            <a href="/books/<?= $book['id'] ?>/edit" class="btn btn-outline-primary w-100">
                                <i class="material-icons mr-1">edit</i>
                                Edit Book
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-8">
                <h1 class="mb-2"><?= e($book['title']) ?></h1>
                <h3 class="text-muted mb-4">by <?= e($book['author']) ?></h3>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>ISBN:</strong> <?= e($book['isbn']) ?></p>
                        <p><strong>Published Year:</strong> <?= e($book['published_year']) ?></p>
                        <p><strong>Category:</strong> <?= e($book['category']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Shelf Location:</strong> <?= e($book['shelf_location']) ?></p>
                        <p><strong>Added:</strong> <?= format_date($book['created_at'], 'F j, Y') ?></p>
                        <p><strong>Last Updated:</strong> <?= format_date($book['updated_at'], 'F j, Y') ?></p>
                    </div>
                </div>
                
                <?php if (!empty($book['description'])): ?>
                    <h4>Description</h4>
                    <p class="mb-4"><?= nl2br(e($book['description'])) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($related)): ?>
                    <h4>Related Books</h4>
                    <div class="row">
                        <?php foreach ($related as $relatedBook): ?>
                            <div class="col-md-3">
                                <a href="/books/<?= $relatedBook['id'] ?>" class="text-decoration-none">
                                    <div class="card book-card">
                                        <img src="<?= e($relatedBook['cover_image']) ?>" 
                                             alt="<?= e($relatedBook['title']) ?>" 
                                             class="book-card-img">
                                        <div class="card-body">
                                            <h5 class="card-title text-truncate"><?= e($relatedBook['title']) ?></h5>
                                            <p class="card-text text-muted"><?= e($relatedBook['author']) ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->partial('_footer'); ?>