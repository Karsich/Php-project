<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Темы форума</h1>
    <?php if (isset($_SESSION['user'])): ?>
        <a href="/topics/create" class="btn btn-primary">Создать тему</a>
    <?php endif; ?>
</div>

<?php foreach ($topics as $topic): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                <a href="/topics/<?= $topic['id'] ?>" class="text-decoration-none">
                    <?= htmlspecialchars($topic['title']) ?>
                </a>
            </h5>
            <div class="text-muted">
                <small>
                    Автор: <?= htmlspecialchars($topic['author']['username']) ?> |
                    Создано: <?= date('d.m.Y H:i', strtotime($topic['created_at'])) ?>
                </small>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if ($totalPages > 1): ?>
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