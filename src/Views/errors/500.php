<div class="text-center py-5">
    <h1 class="display-1">500</h1>
    <h2 class="mb-4">Внутренняя ошибка сервера</h2>
    <p class="lead mb-4">Произошла непредвиденная ошибка. Пожалуйста, попробуйте позже.</p>
    <?php if (isset($error) && $_ENV['APP_DEBUG']): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <a href="/" class="btn btn-primary">Вернуться на главную</a>
</div> 