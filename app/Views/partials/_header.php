<header class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1><?= isset($pageTitle) ? e($pageTitle) : 'Library Management System' ?></h1>
        
        <?php if (isset($headerButton)): ?>
            <a href="<?= $headerButton['url'] ?>" class="btn <?= $headerButton['class'] ?? 'btn-primary' ?>">
                <?php if (isset($headerButton['icon'])): ?>
                    <i class="material-icons mr-1"><?= $headerButton['icon'] ?></i>
                <?php endif; ?>
                <?= $headerButton['text'] ?>
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($breadcrumbs)): ?>
        <nav class="breadcrumb mt-2">
            <a href="/" class="breadcrumb-item">Home</a>
            <?php foreach ($breadcrumbs as $link => $title): ?>
                <?php if ($link === ''): ?>
                    <span class="breadcrumb-item active"><?= e($title) ?></span>
                <?php else: ?>
                    <a href="<?= $link ?>" class="breadcrumb-item"><?= e($title) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</header>