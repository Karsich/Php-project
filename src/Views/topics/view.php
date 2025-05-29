<?php if ($topic): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><?= htmlspecialchars($topic['title']) ?></h2>
                    <p class="card-text"><?= htmlspecialchars($topic['description']) ?></p>
                    <div class="text-muted small">
                        Создано: <?= htmlspecialchars($topic['created_at']) ?>
                    </div>
                </div>
            </div>

            <div id="postsList">
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">
                        В этой теме пока нет сообщений. Будьте первым!
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card mb-3" data-post-id="<?= htmlspecialchars($post['id']) ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="card-text post-content"><?= htmlspecialchars($post['content']) ?></p>
                                        <div class="text-muted small">
                                            Автор: <?= htmlspecialchars($post['author']['username']) ?> |
                                            Создано: <?= htmlspecialchars($post['created_at']) ?>
                                        </div>
                                    </div>
                                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $post['author']['id']): ?>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary edit-post-btn">
                                                <i class="fas fa-edit"></i> Редактировать
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-post-btn">
                                                <i class="fas fa-trash"></i> Удалить
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['user'])): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="createPostForm" action="/api/posts" method="POST">
                            <input type="hidden" name="topic_id" value="<?= htmlspecialchars($topic['id']) ?>">
                            <div class="mb-3">
                                <label for="content" class="form-label">Ваш ответ</label>
                                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                            </div>
                            <div id="errorMessage" class="alert alert-danger d-none"></div>
                            <button type="submit" class="btn btn-primary">Отправить</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger">
        Тема не найдена
    </div>
<?php endif; ?>

<?php if (isset($totalPages) && isset($currentPage) && $totalPages > 1): ?>
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

<!-- Модальное окно для редактирования поста -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактировать пост</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPostForm">
                    <input type="hidden" name="post_id" id="editPostId">
                    <div class="mb-3">
                        <label for="editContent" class="form-label">Содержание</label>
                        <textarea class="form-control" id="editContent" name="content" rows="3" required></textarea>
                    </div>
                    <div id="editErrorMessage" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createPostForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    const textarea = this.querySelector('textarea');
    
    // Преобразуем FormData в объект
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('/api/posts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Очищаем форму
            textarea.value = '';
            errorDiv.classList.add('d-none');
            
            // Добавляем новый пост в конец списка
            const postsList = document.getElementById('postsList');
            const alertInfo = postsList.querySelector('.alert-info');
            if (alertInfo) {
                alertInfo.remove();
            }
            
            const newPost = document.createElement('div');
            newPost.className = 'card mb-3';
            newPost.dataset.postId = data.data.id;
            newPost.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="card-text post-content">${data.data.content}</p>
                            <div class="text-muted small">
                                Автор: ${data.data.author.username} |
                                Создано: ${data.data.created_at}
                            </div>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary edit-post-btn">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-post-btn">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                </div>
            `;
            postsList.appendChild(newPost);
            addPostEventListeners(newPost);
        } else {
            // Показываем ошибку
            errorDiv.textContent = data.message || 'Произошла ошибка при создании поста';
            errorDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Произошла ошибка при отправке запроса';
        errorDiv.classList.remove('d-none');
        console.error('Error:', error);
    });
});

// Инициализация модального окна
const editPostModal = new bootstrap.Modal(document.getElementById('editPostModal'));

function addPostEventListeners(postElement) {
    // Обработчик редактирования
    postElement.querySelector('.edit-post-btn')?.addEventListener('click', function() {
        const postId = this.closest('[data-post-id]').dataset.postId;
        const content = this.closest('.card-body').querySelector('.post-content').textContent.trim();
        
        document.getElementById('editPostId').value = postId;
        document.getElementById('editContent').value = content;
        editPostModal.show();
    });

    // Обработчик удаления
    postElement.querySelector('.delete-post-btn')?.addEventListener('click', function() {
        if (confirm('Вы уверены, что хотите удалить этот пост?')) {
            const postId = this.closest('[data-post-id]').dataset.postId;
            
            fetch(`/api/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('.card').remove();
                    
                    // Проверяем, остались ли посты
                    const postsList = document.getElementById('postsList');
                    if (!postsList.querySelector('.card')) {
                        postsList.innerHTML = `
                            <div class="alert alert-info">
                                В этой теме пока нет сообщений. Будьте первым!
                            </div>
                        `;
                    }
                } else {
                    alert(data.message || 'Произошла ошибка при удалении поста');
                }
            })
            .catch(error => {
                alert('Произошла ошибка при отправке запроса');
                console.error('Error:', error);
            });
        }
    });
}

// Добавляем обработчики для существующих постов
document.querySelectorAll('[data-post-id]').forEach(addPostEventListeners);

// Обработчик сохранения изменений
document.getElementById('saveEditBtn').addEventListener('click', function() {
    const form = document.getElementById('editPostForm');
    const postId = document.getElementById('editPostId').value;
    const content = document.getElementById('editContent').value;
    const errorDiv = document.getElementById('editErrorMessage');
    
    if (!content.trim()) {
        errorDiv.textContent = 'Содержание поста не может быть пустым';
        errorDiv.classList.remove('d-none');
        return;
    }
    
    fetch(`/api/posts/${postId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ content: content.trim() })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Обновляем содержимое поста
            const post = document.querySelector(`[data-post-id="${postId}"]`);
            if (post) {
                post.querySelector('.post-content').textContent = content.trim();
                
                // Добавляем информацию об обновлении, если её нет
                let timeInfo = post.querySelector('.text-muted.small');
                if (!timeInfo.textContent.includes('Обновлено:')) {
                    timeInfo.textContent += ` | Обновлено: ${new Date().toLocaleString()}`;
                }
            }
            
            // Закрываем модальное окно и очищаем сообщение об ошибке
            errorDiv.classList.add('d-none');
            editPostModal.hide();
        } else {
            errorDiv.textContent = data.message || 'Произошла ошибка при обновлении поста';
            errorDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Произошла ошибка при отправке запроса';
        errorDiv.classList.remove('d-none');
        console.error('Error:', error);
    });
});
</script> 