<?php $this->partial('_header', ['pageTitle' => $book['title']]); ?>

<div class="card">
    <div class="card-body">
        <div class="book-detail">
            <img src="<?= $book['cover_image'] ?>" alt="<?= e($book['title']) ?>" class="book-cover">
            
            <div class="book-info">
                <h2><?= e($book['title']) ?></h2>
                <p class="text-muted">by <?= e($book['author']) ?></p>
                
                <div class="book-meta">
                    <div class="book-meta-item">
                        <span class="book-meta-label">ISBN:</span>
                        <span><?= e($book['isbn']) ?></span>
                    </div>
                    <div class="book-meta-item">
                        <span class="book-meta-label">Published Year:</span>
                        <span><?= e($book['published_year']) ?></span>
                    </div>
                    <div class="book-meta-item">
                        <span class="book-meta-label">Category:</span>
                        <span><?= e($book['category']) ?></span>
                    </div>
                    <div class="book-meta-item">
                        <span class="book-meta-label">Shelf Location:</span>
                        <span><?= e($book['shelf_location']) ?></span>
                    </div>
                    <div class="book-meta-item">
                        <span class="book-meta-label">Availability:</span>
                        <span class="badge <?= $book['available_copies'] > 0 ? 'available' : 'badge-danger' ?>">
                            <?= $book['available_copies'] ?>/<?= $book['total_copies'] ?> copies available
                        </span>
                    </div>
                </div>
                
                <div class="book-actions">
                    <a href="/loans/create?book_id=<?= $book['id'] ?>" class="btn btn-primary">
                        <i class="material-icons mr-1">book</i> Borrow Book
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (!empty($book['description'])): ?>
            <div class="book-description">
                <h3>Description</h3>
                <p><?= nl2br(e($book['description'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($related)): ?>
    <h3 class="related-books-title">Related Books</h3>
    <div class="related-books">
        <?php foreach ($related as $relatedBook): ?>
            <a href="/books/<?= $relatedBook['id'] ?>" class="related-book-card">
                <img src="<?= $relatedBook['cover_image'] ?>" alt="<?= e($relatedBook['title']) ?>" class="related-book-img">
                <div class="related-book-info">
                    <h4 class="related-book-title"><?= e($relatedBook['title']) ?></h4>
                    <p class="related-book-author"><?= e($relatedBook['author']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $this->partial('_footer'); ?>