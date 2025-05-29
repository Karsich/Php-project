<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Вход в систему</h4>
            </div>
            <div class="card-body">
                <form id="loginForm" action="/api/auth/login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                    <button type="submit" class="btn btn-primary">Войти</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">
                    Нет аккаунта? <a href="/auth/register">Зарегистрируйтесь</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    
    fetch('/api/auth/login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // При успешном входе сохраняем данные пользователя
            sessionStorage.setItem('user', JSON.stringify(data.data));
            // Перенаправляем на главную или указанную страницу
            window.location.href = data.redirect || '/';
        } else {
            // Показываем ошибку
            errorDiv.textContent = data.message || 'Неверный email или пароль';
            errorDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Произошла ошибка при отправке запроса';
        errorDiv.classList.remove('d-none');
        console.error('Error:', error);
    });
});</script> 