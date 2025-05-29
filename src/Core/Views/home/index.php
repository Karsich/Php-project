<h1 class="mb-4">Последние темы</h1>

<?php if (empty($topics)): ?>
    <div class="alert alert-info">
        Пока нет ни одной темы. Будьте первым, кто создаст тему!
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($topics as $topic): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/topics/<?php echo $topic['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($topic['title']); ?>
                            </a>
                        </h5>
                        <p class="card-text"><?php echo htmlspecialchars($topic['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Автор: <?php echo htmlspecialchars($topic['username']); ?>
                            </small>
                            <small class="text-muted">
                                <?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['user'])): ?>
    <div class="text-center mt-4">
        <a href="/topics/create" class="btn btn-primary">Создать новую тему</a>
    </div>
<?php endif; ?> 