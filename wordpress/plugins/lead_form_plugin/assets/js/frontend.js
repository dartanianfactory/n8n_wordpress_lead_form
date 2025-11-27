(function($) {
    'use strict';

    class LeadForm {
        constructor() {
            this.form = $('#lead-capture-form');
            this.submitBtn = $('#submit-btn');
            this.messages = $('#form-messages');
            
            this.init();
        }
        
        init() {
            this.form.on('submit', (e) => this.handleSubmit(e));
        }
        
        handleSubmit(e) {
            e.preventDefault();
            
            this.clearErrors();
            this.messages.hide().removeClass('success error');
            this.submitBtn.prop('disabled', true).text('Отправка...');

            const formData = {
                action: 'submit_lead_form',
                nonce: lead_form_ajax.nonce,
                name: $('#name').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                message: $('#message').val()
            };

            const errors = this.validateForm(formData);
            
            if (Object.keys(errors).length > 0) {
                this.displayErrors(errors);
                this.submitBtn.prop('disabled', false).text('Отправить заявку');
                return;
            }

            this.sendFormData(formData);
        }
        
        validateForm(data) {
            const errors = {};
            
            if (!data.name.trim()) {
                errors.name = 'Пожалуйста, введите ваше имя';
            }
            
            if (!data.email.trim() || !this.isValidEmail(data.email)) {
                errors.email = 'Пожалуйста, введите корректный email';
            }
            
            if (!data.phone.trim()) {
                errors.phone = 'Пожалуйста, введите ваш телефон';
            }

            return errors;
        }
        
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        displayErrors(errors) {
            $.each(errors, (field, message) => {
                $(`#${field}-error`).text(message).show();
            });
        }
        
        clearErrors() {
            $('.error-message').hide();
        }
        
        sendFormData(formData) {
            $.ajax({
                url: lead_form_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        this.showSuccess(response.data);
                        this.form[0].reset();
                    } else {
                        this.displayErrors(response.data);
                    }
                },
                error: () => {
                    this.showError('Произошла ошибка при отправке');
                },
                complete: () => {
                    this.submitBtn.prop('disabled', false).text('Отправить заявку');
                }
            });
        }
        
        showSuccess(message) {
            this.messages.addClass('success').text(message).show();
        }
        
        showError(message) {
            this.messages.addClass('error').text(message).show();
        }
    }

    $(document).ready(() => {
        if ($('#lead-capture-form').length) {
            new LeadForm();
        }
    });

})(jQuery);
