jQuery(document).ready(function($) {
    $('#lead-capture-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submit-btn');
        var messages = $('#form-messages');

        $('.error-message').hide();
        messages.hide().removeClass('success error');

        submitBtn.prop('disabled', true).text('Отправка...');

        var formData = {
            action: 'submit_lead_form',
            nonce: ajax_object.nonce,
            name: $('#name').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            message: $('#message').val()
        };

        var errors = {};
        
        if (!formData.name.trim()) {
            errors.name = 'Пожалуйста, введите ваше имя';
        }
        
        if (!formData.email.trim() || !isValidEmail(formData.email)) {
            errors.email = 'Пожалуйста, введите корректный email';
        }
        
        if (!formData.phone.trim()) {
            errors.phone = 'Пожалуйста, введите ваш телефон';
        }

        if (Object.keys(errors).length > 0) {
            $.each(errors, function(field, message) {
                $('#' + field + '-error').text(message).show();
            });
            submitBtn.prop('disabled', false).text('Отправить заявку');
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    messages.addClass('success').text(response.data).show();
                    form[0].reset();
                } else {
                    $.each(response.data, function(field, message) {
                        $('#' + field + '-error').text(message).show();
                    });
                }
            },
            error: function() {
                messages.addClass('error').text('Произошла ошибка при отправке').show();
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Отправить заявку');
            }
        });
    });
    
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
