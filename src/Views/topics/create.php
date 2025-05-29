<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Создание новой темы</h4>
            </div>
            <div class="card-body">
                <form id="createTopicForm" action="/api/topics" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Заголовок</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                    <div class="d-flex justify-content-between">
                        <a href="/" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">Создать тему</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createTopicForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    
    // Преобразуем FormData в объект
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('/api/topics', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // При успешном создании темы перенаправляем на её страницу
            window.location.href = data.redirect || '/topics/' + data.data.id;
        } else {
            // Показываем ошибку
            errorDiv.textContent = data.message || 'Произошла ошибка при создании темы';
            errorDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Произошла ошибка при отправке запроса';
        errorDiv.classList.remove('d-none');
        console.error('Error:', error);
    });
});</script> 