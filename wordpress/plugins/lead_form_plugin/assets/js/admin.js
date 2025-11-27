(function($) {
    'use strict';

    class LeadFormAdmin {
        constructor() {
            this.init();
        }
        
        init() {
            if ($('#leadsChart').length) {
                this.initLeadsChart();
            }
            
            $('#test-webhook').on('click', () => this.testWebhook());
            $('#test-crm').on('click', () => this.testCRM());
        }
        
        initLeadsChart() {
            const ctx = document.getElementById('leadsChart').getContext('2d');

            const chartData = {
                labels: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
                datasets: [{
                    label: 'Новые заявки',
                    data: [12, 19, 8, 15, 12, 6, 9],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };
            
            new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        testWebhook() {
            const button = $('#test-webhook');
            const results = $('#test-results');
            
            button.prop('disabled', true).text('Тестирование...');
            
            $.ajax({
                url: lead_form_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'test_webhook',
                    nonce: lead_form_admin.nonce
                },
                success: (response) => {
                    results.show();
                    if (response.success) {
                        results.removeClass('test-error').addClass('test-success').text(response.data);
                    } else {
                        results.removeClass('test-success').addClass('test-error').text(response.data);
                    }
                },
                error: () => {
                    results.show().removeClass('test-success').addClass('test-error').text('Ошибка при тестировании');
                },
                complete: () => {
                    button.prop('disabled', false).text('Протестировать Webhook');
                }
            });
        }
        
        testCRM() {
            const button = $('#test-crm');
            const results = $('#test-results');
            
            button.prop('disabled', true).text('Тестирование...');
            
            $.ajax({
                url: lead_form_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'test_crm',
                    nonce: lead_form_admin.nonce
                },
                success: (response) => {
                    results.show();
                    if (response.success) {
                        results.removeClass('test-error').addClass('test-success').text(response.data);
                    } else {
                        results.removeClass('test-success').addClass('test-error').text(response.data);
                    }
                },
                error: () => {
                    results.show().removeClass('test-success').addClass('test-error').text('Ошибка при тестировании CRM');
                },
                complete: () => {
                    button.prop('disabled', false).text('Протестировать CRM');
                }
            });
        }
    }

    $(document).ready(() => {
        new LeadFormAdmin();
    });

})(jQuery);
