<?php
$title = htmlspecialchars($topic[0]['title']);
$isAuthor = isset($_SESSION['user']) && $_SESSION['user']['id'] === $topic[0]['author_id'];
?>

<div class="container">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1><?php echo $title; ?></h1>
                <div class="text-muted">
                    Автор: <?php echo htmlspecialchars($topic[0]['username']); ?> | 
                    Создано: <?php echo date('d.m.Y H:i', strtotime($topic[0]['created_at'])); ?>
                    <?php if ($topic[0]['is_closed']): ?>
                        <span class="badge bg-danger">Закрыта</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($isAuthor): ?>
            <div class="btn-group">
                <a href="/topics/<?php echo $topic[0]['id']; ?>/edit" class="btn btn-outline-primary">Редактировать</a>
                <form action="/topics/<?php echo $topic[0]['id']; ?>/toggle" method="POST" class="d-inline">
                    <button type="submit" class="btn btn-outline-warning">
                        <?php echo $topic[0]['is_closed'] ? 'Открыть' : 'Закрыть'; ?>
                    </button>
                </form>
                <form action="/topics/<?php echo $topic[0]['id']; ?>/delete" method="POST" class="d-inline" 
                      onsubmit="return confirm('Вы уверены, что хотите удалить эту тему?');">
                    <button type="submit" class="btn btn-outline-danger">Удалить</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-3">
            <?php echo nl2br(htmlspecialchars($topic[0]['description'])); ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Ответы</h4>
        </div>
        <div class="card-body">
            <?php if (empty($posts)): ?>
                <p class="text-muted">Пока нет ответов. Будьте первым!</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                                <div class="text-muted small">
                                    <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['user']) && !$topic[0]['is_closed']): ?>
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Добавить ответ</h4>
            </div>
            <div class="card-body">
                <form action="/posts/create" method="POST">
                    <input type="hidden" name="topic_id" value="<?php echo $topic[0]['id']; ?>">
                    <div class="mb-3">
                        <label for="content" class="form-label">Ваш ответ</label>
                        <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($topic[0]['is_closed']): ?>
        <div class="alert alert-warning">
            Тема закрыта для новых ответов.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <a href="/auth/login">Войдите</a> или <a href="/auth/register">зарегистрируйтесь</a>, чтобы оставить ответ.
        </div>
    <?php endif; ?>
</div> 