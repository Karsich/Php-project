<?php
$title = htmlspecialchars($topic[0]['title']);
?>

<div class="container">
    <h1>Редактирование темы</h1>
    
    <form action="/topics/<?php echo $topic[0]['id']; ?>/update" method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Название темы</label>
            <input type="text" class="form-control" id="title" name="title" 
                   value="<?php echo $title; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Описание</label>
            <textarea class="form-control" id="description" name="description" 
                      rows="5"><?php echo htmlspecialchars($topic[0]['description']); ?></textarea>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="/topics/<?php echo $topic[0]['id']; ?>" class="btn btn-outline-secondary">Отмена</a>
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        </div>
    </form>
</div> 