<div class="text-center py-5">
    <div class="display-1 text-primary mb-4">
        <i class="fas fa-search"></i> 404
    </div>
    <h2 class="mb-4">Упс! Страница потерялась в космосе 🚀</h2>
    <div class="mb-4">
        <p class="lead">
            Похоже, что эта страница отправилась в путешествие по галактике... 
            Но не волнуйтесь, у нас есть печеньки! 🍪
        </p>
        <p class="text-muted">
            Пока мы ищем потерянную страницу, может быть, хотите вернуться домой?
        </p>
    </div>
    <div class="mb-4">
        <img src="https://media.giphy.com/media/VIPdgcooFJHtC/giphy.gif" alt="Cute Dog" class="img-fluid rounded" style="max-width: 300px;">
    </div>
    <a href="/" class="btn btn-primary btn-lg">
        <i class="fas fa-home"></i> Вернуться домой
    </a>
</div>

<!-- Добавляем Font Awesome для иконок -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

.display-1 {
    animation: float 3s ease-in-out infinite;
}

.btn-primary {
    transition: transform 0.3s ease;
}

.btn-primary:hover {
    transform: scale(1.1);
}
</style> 