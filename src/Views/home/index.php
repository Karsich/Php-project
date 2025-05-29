<div class="row">
    <div class="col-md-12 mb-4">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="/topics/create" class="btn btn-primary">Создать новую тему</a>
        <?php endif; ?>
    </div>

    <?php if (empty($topics)): ?>
        <div class="col-md-12">
            <div class="alert alert-info">
                Пока нет ни одной темы. Будьте первым, кто создаст тему!
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($topics as $topic): ?>
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/topics/<?= htmlspecialchars($topic['id']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($topic['title']) ?>
                            </a>
                        </h5>
                        <p class="card-text"><?= htmlspecialchars($topic['description']) ?></p>
                        <div class="text-muted small">
                            Создано: <?= htmlspecialchars($topic['created_at']) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (isset($totalPages) && isset($currentPage) && $totalPages > 1): ?>
    <nav aria-label="Навигация по страницам">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
                    <a class="page-link" href="/?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?> 