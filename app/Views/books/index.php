<?php $this->partial('_header', ['pageTitle' => 'Books']); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">Books</h2>
            <?php if (is_admin()): ?>
                <a href="/books/create" class="btn btn-primary">
                    <i class="material-icons mr-1">add</i>
                    Add New Book
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Search and Filters -->
        <form action="/books" method="GET" class="mt-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="text" name="query" class="form-control" 
                               placeholder="Search by title, author, or ISBN"
                               value="<?= isset($filters['query']) ? e($filters['query']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category) ?>" 
                                    <?= isset($filters['category']) && $filters['category'] === $category ? 'selected' : '' ?>>
                                    <?= e($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <select name="availability" class="form-select">
                            <option value="all">All Books</option>
                            <option value="available" <?= isset($filters['availability']) && $filters['availability'] === 'available' ? 'selected' : '' ?>>
                                Available Only
                            </option>
                            <option value="unavailable" <?= isset($filters['availability']) && $filters['availability'] === 'unavailable' ? 'selected' : '' ?>>
                                Unavailable
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
        <?php if (empty($books)): ?>
            <div class="text-center py-5">
                <i class="material-icons" style="font-size: 48px; color: var(--gray-400);">menu_book</i>
                <p class="mt-3">No books found matching your criteria.</p>
            </div>
        <?php else: ?>
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <div class="card book-card" onclick="window.location.href='/books/<?= $book['id'] ?>'">
                        <img src="<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>" class="book-card-img">
                        <div class="book-card-body">
                            <h3 class="book-card-title"><?= e($book['title']) ?></h3>
                            <p class="book-card-author"><?= e($book['author']) ?></p>
                            <div class="book-card-footer">
                                <span class="badge <?= $book['available_copies'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $book['available_copies'] > 0 ? 'Available' : 'Unavailable' ?>
                                </span>
                                <small class="text-muted">
                                    <?= $book['available_copies'] ?>/<?= $book['total_copies'] ?> copies
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="mt-4">
                    <?= paginate(
                        $pagination['current_page'],
                        $pagination['total_pages'],
                        '/books?page=:page' . 
                        (isset($filters['category']) ? '&category=' . urlencode($filters['category']) : '') .
                        (isset($filters['query']) ? '&query=' . urlencode($filters['query']) : '') .
                        (isset($filters['availability']) ? '&availability=' . urlencode($filters['availability']) : '')
                    ) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->partial('_footer'); ?>