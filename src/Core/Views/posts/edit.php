<?php
$post = $result['data'];
?>

<div class="container">
    <h1>Редактирование ответа</h1>
    
    <form action="/posts/<?php echo $post['id']; ?>/update" method="POST">
        <div class="mb-3">
            <label for="content" class="form-label">Содержание</label>
            <textarea class="form-control" id="content" name="content" 
                      rows="5" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="/topics/<?php echo $post['topic_id']; ?>" class="btn btn-outline-secondary">Отмена</a>
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        </div>
    </form>
</div> 