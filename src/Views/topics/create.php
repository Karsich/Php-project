<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Создать новую тему</h4>
            </div>
            <div class="card-body">
                <form action="/topics/create" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Заголовок</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Содержание</label>
                        <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Создать тему</button>
                </form>
            </div>
        </div>
    </div>
</div> 