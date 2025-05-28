<h2><?= htmlspecialchars($topic['title']) ?></h2>
<div class="text-muted mb-4">
    <small>
        Автор: <?= htmlspecialchars($topic['author']['username']) ?> |
        Создано: <?= date('d.m.Y H:i', strtotime($topic['created_at'])) ?>
    </small>
</div>

<?php foreach ($posts as $post): ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="post-content mb-3">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
            <div class="text-muted">
                <small>
                    <?= htmlspecialchars($post['author']['username']) ?> |
                    <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>
                </small>
            </div>

            <?php if (!empty($post['replies'])): ?>
                <div class="replies mt-3">
                    <h6>Ответы:</h6>
                    <?php foreach ($post['replies'] as $reply): ?>
                        <div class="card reply-card mb-2">
                            <div class="card-body py-2">
                                <div class="reply-content">
                                    <?= nl2br(htmlspecialchars($reply['content'])) ?>
                                </div>
                                <div class="text-muted">
                                    <small>
                                        <?= htmlspecialchars($reply['author']['username']) ?> |
                                        <?= date('d.m.Y H:i', strtotime($reply['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user'])): ?>
                <div class="mt-3">
                    <form action="/topics/<?= $topic['id'] ?>/posts/<?= $post['id'] ?>/reply" method="POST">
                        <div class="input-group">
                            <input type="text" class="form-control" name="content" placeholder="Написать ответ" required>
                            <button class="btn btn-outline-primary" type="submit">Ответить</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if (isset($_SESSION['user'])): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Написать сообщение</h5>
            <form action="/topics/<?= $topic['id'] ?>/posts" method="POST">
                <div class="mb-3">
                    <textarea class="form-control" name="content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <nav aria-label="Навигация по страницам" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
                    <a class="page-link" href="/topics/<?= $topic['id'] ?>?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?> 