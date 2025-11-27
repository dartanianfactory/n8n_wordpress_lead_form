<div class="wrap">
    <h1>Lead Form Settings</h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('lead_form_settings'); ?>
        <?php do_settings_sections('lead_form_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">Webhook URL</th>
                <td>
                    <input type="url" name="lead_form_webhook_url" value="<?php echo esc_attr(get_option('lead_form_webhook_url')); ?>" class="regular-text" />
                    <p class="description">URL вебхука n8n для отправки данных</p>
                </td>
            </tr>
            <tr>
                <th scope="row">CRM URL</th>
                <td>
                    <input type="url" name="lead_form_crm_url" value="<?php echo esc_attr(get_option('lead_form_crm_url')); ?>" class="regular-text" />
                    <p class="description">URL вашего EspoCRM инстанса</p>
                </td>
            </tr>
            <tr>
                <th scope="row">API Key</th>
                <td>
                    <input type="password" name="lead_form_api_key" value="<?php echo esc_attr(get_option('lead_form_api_key')); ?>" class="regular-text" />
                    <p class="description">API ключ для интеграции с CRM</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Сообщение об успехе</th>
                <td>
                    <input type="text" name="lead_form_success_message" value="<?php echo esc_attr(get_option('lead_form_success_message', 'Заявка успешно отправлена!')); ?>" class="regular-text" />
                    <p class="description">Сообщение, которое показывается после успешной отправки формы</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <div class="test-section">
        <h2>Тестирование интеграции</h2>
        <button type="button" id="test-webhook" class="button button-secondary">Протестировать Webhook</button>
        <button type="button" id="test-crm" class="button button-secondary">Протестировать CRM</button>
        <div id="test-results"></div>
    </div>
</div>
