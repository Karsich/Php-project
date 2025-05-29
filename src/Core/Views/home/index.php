<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['flash']['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Темы форума</h1>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="/topics/create" class="btn btn-primary">Создать тему</a>
        <?php endif; ?>
    </div>

    <?php if (empty($topics)): ?>
        <div class="alert alert-info">
            Пока нет ни одной темы. Будьте первым, кто создаст тему!
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($topics as $topic): ?>
                <a href="/topics/<?php echo $topic['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($topic['title']); ?></h5>
                            <p class="mb-1 text-truncate" style="max-width: 500px;">
                                <?php echo htmlspecialchars($topic['description']); ?>
                            </p>
                            <small>
                                Автор: <?php echo htmlspecialchars($topic['username']); ?> | 
                                Последнее изменение: <?php echo date('d.m.Y H:i', strtotime($topic['updated_at'] ?? $topic['created_at'])); ?>
                            </small>
                        </div>
                        <div class="d-flex align-items-center">
                            <?php if ($topic['is_closed']): ?>
                                <span class="badge bg-danger me-2">Закрыта</span>
                            <?php endif; ?>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $topic['post_count'] ?? 0; ?> ответов
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div> 