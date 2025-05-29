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
                    Автор: <?php echo htmlspecialchars($topic[0]['username']); ?><br>
                    Создано: <?php echo date('d.m.Y H:i', strtotime($topic[0]['created_at'])); ?><br>
                    <?php if ($topic[0]['updated_at'] && $topic[0]['updated_at'] !== $topic[0]['created_at']): ?>
                        Изменено: <?php echo date('d.m.Y H:i', strtotime($topic[0]['updated_at'])); ?><br>
                    <?php endif; ?>
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
                    <div class="mb-3 pb-3 border-bottom" id="post-<?php echo $post['id']; ?>">
                        <?php if (!$post['is_deleted']): ?>
                            <?php if ($post['reply_to_id']): ?>
                                <div class="mb-2 ps-3 border-start border-primary">
                                    <small class="text-muted">
                                        <?php if (!empty($post['reply_to_username']) && !empty($post['reply_to_content'])): ?>
                                            В ответ <?php echo htmlspecialchars($post['reply_to_username']); ?>:
                                            <a href="#post-<?php echo $post['reply_to_id']; ?>" class="text-decoration-none">
                                                <?php echo mb_substr(htmlspecialchars($post['reply_to_content']), 0, 100); ?>...
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Сообщение было удалено</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                                    <div class="text-muted small">
                                        Создано: <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                                        <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                                            <br>Изменено: <?php echo date('d.m.Y H:i', strtotime($post['updated_at'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] === $post['author_id'] || $isAuthor)): ?>
                                <div class="btn-group">
                                    <?php if ($_SESSION['user']['id'] === $post['author_id']): ?>
                                        <a href="/posts/<?php echo $post['id']; ?>/edit" class="btn btn-sm btn-outline-primary">Редактировать</a>
                                    <?php endif; ?>
                                    <form action="/posts/<?php echo $post['id']; ?>/delete" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Вы уверены, что хотите удалить этот ответ?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </div>
                            <div class="mt-2 d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] !== $post['author_id']): ?>
                                        <button type="button" class="reaction-btn" 
                                                data-post-id="<?php echo $post['id']; ?>"
                                                data-has-reaction="<?php echo !empty($post['has_user_reaction']) ? 'true' : 'false'; ?>">
                                            <i class="bi bi-heart<?php echo !empty($post['has_user_reaction']) ? '-fill text-danger' : ''; ?>"></i>
                                            <span class="ms-1"><?php echo (int)($post['reaction_count'] ?? 0); ?></span>
                                        </button>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-heart<?php echo ($post['reaction_count'] ?? 0) > 0 ? '-fill text-danger' : ''; ?>"></i>
                                            <span class="ms-1"><?php echo (int)($post['reaction_count'] ?? 0); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($_SESSION['user'])): ?>
                                    <button class="btn btn-sm btn-outline-secondary reply-btn" 
                                            data-post-id="<?php echo $post['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($post['username']); ?>">
                                        <i class="bi bi-reply"></i> Ответить
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted fst-italic">
                                <i class="bi bi-trash"></i> Это сообщение было удалено
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['user']) && !$topic[0]['is_closed']): ?>
        <div class="card" id="reply-form">
            <div class="card-header">
                <h4 class="mb-0">Добавить ответ</h4>
            </div>
            <div class="card-body">
                <form action="/posts/create" method="POST">
                    <input type="hidden" name="topic_id" value="<?php echo $topic[0]['id']; ?>">
                    <input type="hidden" name="reply_to_id" id="reply_to_id" value="">
                    <div id="reply-to-info" class="mb-3 d-none">
                        <div class="alert alert-info">
                            Ответ на сообщение <span id="reply-username"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger cancel-reply">Отменить</button>
                        </div>
                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing reaction buttons...');
    
    // Обработка реакций
    const reactionButtons = document.querySelectorAll('.reaction-btn');
    console.log('Found reaction buttons:', reactionButtons.length);
    
    reactionButtons.forEach(button => {
        console.log('Adding click handler to button:', button.dataset.postId);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const postId = this.dataset.postId;
            const hasReaction = this.dataset.hasReaction === 'true';
            const icon = this.querySelector('i');
            const countSpan = this.querySelector('span');

            console.log('Reaction button clicked:', {
                postId,
                hasReaction,
                icon: icon.className,
                currentCount: countSpan.textContent
            });

            fetch(`/posts/${postId}/reaction`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    this.dataset.hasReaction = (!hasReaction).toString();
                    icon.className = `bi bi-heart${!hasReaction ? '-fill text-danger' : ''}`;
                    countSpan.textContent = data.reaction_count;
                } else {
                    alert(data.error || 'Произошла ошибка при обработке реакции');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обработке реакции');
            });
        });
    });

    // Обработка ответов на сообщения
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const username = this.dataset.username;
            
            document.getElementById('reply_to_id').value = postId;
            document.getElementById('reply-username').textContent = username;
            document.getElementById('reply-to-info').classList.remove('d-none');
            
            document.getElementById('reply-form').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('content').focus();
        });
    });

    // Отмена ответа
    document.querySelector('.cancel-reply')?.addEventListener('click', function() {
        document.getElementById('reply_to_id').value = '';
        document.getElementById('reply-to-info').classList.add('d-none');
    });

    // Плавная прокрутка к ответу при клике на ссылку
    document.querySelectorAll('a[href^="#post-"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            document.querySelector(targetId).scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script> 
