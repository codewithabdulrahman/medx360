/*!
 * MedX360 Admin JavaScript
 */
(function($) {
    'use strict';

    // MedX360 Admin Object
    window.MedX360Admin = {
        init: function() {
            this.bindEvents();
            this.loadDashboard();
        },

        bindEvents: function() {
            // API Test Button
            $(document).on('click', '.medx360-test-api', this.testAPI);
            
            // Save Settings
            $(document).on('click', '.medx360-save-settings', this.saveSettings);
            
            // Setup Wizard
            $(document).on('click', '.medx360-start-setup', this.startSetup);
            $(document).on('click', '.medx360-complete-setup', this.completeSetup);
        },

        loadDashboard: function() {
            var self = this;
            
            // Load setup status
            this.getSetupStatus().done(function(data) {
                self.updateSetupStatus(data);
            });
            
            // Load statistics
            this.getStatistics().done(function(data) {
                self.updateStatistics(data);
            });
        },

        testAPI: function(e) {
            e.preventDefault();
            var button = $(this);
            var originalText = button.text();
            
            button.addClass('loading').text('Testing...');
            
            $.ajax({
                url: medx360.api_url + 'onboarding/status',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                },
                success: function(response) {
                    button.removeClass('loading').text('API Working ✓');
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                },
                error: function(xhr) {
                    button.removeClass('loading').text('API Error ✗');
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                }
            });
        },

        getSetupStatus: function() {
            return $.ajax({
                url: medx360.api_url + 'onboarding/status',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                }
            });
        },

        getStatistics: function() {
            return $.ajax({
                url: medx360.api_url + 'onboarding/statistics',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                }
            });
        },

        updateSetupStatus: function(data) {
            var statusElement = $('.medx360-setup-status');
            var progressElement = $('.medx360-progress-bar');
            var nextStepElement = $('.medx360-next-step');
            
            if (data.is_completed) {
                statusElement.html('<span class="api-status active">Setup Complete</span>');
                progressElement.css('width', '100%');
                nextStepElement.text('Setup completed successfully!');
            } else {
                statusElement.html('<span class="api-status pending">Setup In Progress</span>');
                progressElement.css('width', data.progress + '%');
                nextStepElement.text('Next: ' + data.next_step);
            }
        },

        updateStatistics: function(data) {
            $('.medx360-stat-clinics .number').text(data.clinics || 0);
            $('.medx360-stat-hospitals .number').text(data.hospitals || 0);
            $('.medx360-stat-doctors .number').text(data.doctors || 0);
            $('.medx360-stat-services .number').text(data.services || 0);
            $('.medx360-stat-staff .number').text(data.staff || 0);
            $('.medx360-stat-bookings .number').text(data.bookings || 0);
        },

        saveSettings: function(e) {
            e.preventDefault();
            var button = $(this);
            var form = button.closest('form');
            var originalText = button.text();
            
            button.addClass('loading').text('Saving...');
            
            var formData = form.serializeArray();
            var data = {};
            
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });
            
            $.ajax({
                url: medx360.api_url + 'settings',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                },
                success: function(response) {
                    button.removeClass('loading').text('Saved ✓');
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                },
                error: function(xhr) {
                    button.removeClass('loading').text('Error ✗');
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                }
            });
        },

        startSetup: function(e) {
            e.preventDefault();
            var button = $(this);
            var originalText = button.text();
            
            button.addClass('loading').text('Starting...');
            
            // Create default clinic
            var clinicData = {
                name: 'My Medical Clinic',
                description: 'A comprehensive medical facility',
                email: 'admin@example.com',
                phone: '+1234567890',
                address: '123 Medical Street',
                city: 'Medical City',
                state: 'MC',
                country: 'USA',
                postal_code: '12345'
            };
            
            $.ajax({
                url: medx360.api_url + 'onboarding/clinic',
                method: 'POST',
                data: JSON.stringify(clinicData),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                },
                success: function(response) {
                    // Create default services
                    $.ajax({
                        url: medx360.api_url + 'onboarding/services',
                        method: 'POST',
                        data: JSON.stringify({ clinic_id: response.clinic_id }),
                        contentType: 'application/json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                        },
                        success: function(serviceResponse) {
                            button.removeClass('loading').text('Setup Started ✓');
                            setTimeout(function() {
                                button.text(originalText);
                                location.reload();
                            }, 2000);
                        },
                        error: function(xhr) {
                            button.removeClass('loading').text('Error ✗');
                            setTimeout(function() {
                                button.text(originalText);
                            }, 2000);
                        }
                    });
                },
                error: function(xhr) {
                    button.removeClass('loading').text('Error ✗');
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                }
            });
        },

        completeSetup: function(e) {
            e.preventDefault();
            var button = $(this);
            var originalText = button.text();
            
            button.addClass('loading').text('Completing...');
            
            $.ajax({
                url: medx360.api_url + 'onboarding/complete',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', medx360.nonce);
                },
                success: function(response) {
                    button.removeClass('loading').text('Completed ✓');
                    setTimeout(function() {
                        button.text(originalText);
                        location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    button.removeClass('loading').text('Error ✗');
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        MedX360Admin.init();
    });

})(jQuery);
