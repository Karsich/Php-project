<?php
$title = 'Создание новой темы';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Создание новой темы</h4>
                </div>
                <div class="card-body">
                    <form action="/topics/create" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Название темы</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Создать тему</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 