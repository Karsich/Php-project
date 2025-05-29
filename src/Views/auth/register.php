<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Регистрация</h4>
            </div>
            <div class="card-body">
                <form id="registerForm" action="/api/auth/register" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Подтверждение пароля</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">
                    Уже есть аккаунт? <a href="/auth/login">Войдите</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('errorMessage');
    
    // Проверка паролей
    if (formData.get('password') !== formData.get('password_confirm')) {
        errorDiv.textContent = 'Пароли не совпадают';
        errorDiv.classList.remove('d-none');
        return;
    }
    
    fetch('/api/auth/register', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // При успешной регистрации сохраняем данные пользователя
            sessionStorage.setItem('user', JSON.stringify(data.data));
            // Перенаправляем на главную
            window.location.href = '/';
        } else {
            // Показываем ошибку
            errorDiv.textContent = data.message || 'Произошла ошибка при регистрации';
            errorDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'Произошла ошибка при отправке запроса';
        errorDiv.classList.remove('d-none');
        console.error('Error:', error);
    });
});</script> 