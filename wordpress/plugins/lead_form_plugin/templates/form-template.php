<div id="lead-form-container" class="lead-form-container <?php echo esc_attr($class); ?>">
    <?php if (!empty($title)): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>
    
    <form id="lead-capture-form" method="post" class="lead-capture-form">
        <div class="form-group">
            <label for="name">Имя *</label>
            <input type="text" id="name" name="name" required class="form-control">
            <span class="error-message" id="name-error"></span>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required class="form-control">
            <span class="error-message" id="email-error"></span>
        </div>
        
        <div class="form-group">
            <label for="phone">Телефон *</label>
            <input type="tel" id="phone" name="phone" required class="form-control">
            <span class="error-message" id="phone-error"></span>
        </div>
        
        <div class="form-group">
            <label for="message">Комментарий</label>
            <textarea id="message" name="message" rows="4" class="form-control"></textarea>
        </div>
        
        <button type="submit" id="submit-btn" class="submit-btn">Отправить заявку</button>
        
        <div id="form-messages" class="form-messages"></div>
    </form>
</div>
