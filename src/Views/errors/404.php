<div class="text-center py-5">
    <div class="mb-4">
        <img src="https://http.cat/404" alt="404 Cat" class="img-fluid rounded" style="max-width: 500px;">
    </div>
    <h1 class="display-1 mb-4">Упс! 404</h1>
    <h2 class="mb-4">Похоже, эта страница уехала на Марс 🚀</h2>
    <p class="lead mb-4">
        Не волнуйтесь, такое случается даже с лучшими страницами. 
        Возможно, она просто отправилась на поиски приключений или решила взять выходной.
    </p>
    <p class="mb-4">
        Пока она в отпуске, почему бы не вернуться на главную?
    </p>
    <a href="/" class="btn btn-primary btn-lg">
        <i class="fas fa-home"></i> Вернуться домой
    </a>
</div>

<style>
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

.display-1 {
    animation: float 3s ease-in-out infinite;
    color: #6c757d;
}

img {
    transition: transform 0.3s ease;
}

img:hover {
    transform: scale(1.05);
}
</style> 