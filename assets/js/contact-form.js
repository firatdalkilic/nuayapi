document.addEventListener('DOMContentLoaded', function() {
    let forms = document.querySelectorAll('.contact-form');

    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Loading mesajını göster
            form.querySelector('.loading').classList.add('d-block');
            form.querySelector('.error-message').classList.remove('d-block');
            form.querySelector('.sent-message').classList.remove('d-block');

            // Form verilerini topla
            let formData = new FormData(form);

            // AJAX isteği gönder
            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error(`${response.status} ${response.statusText}`);
            })
            .then(data => {
                form.querySelector('.loading').classList.remove('d-block');
                if (data.trim() === 'OK') {
                    form.querySelector('.sent-message').classList.add('d-block');
                    form.reset();
                } else {
                    throw new Error(data || 'Form gönderimi başarısız oldu.');
                }
            })
            .catch(error => {
                form.querySelector('.loading').classList.remove('d-block');
                form.querySelector('.error-message').innerHTML = error.message;
                form.querySelector('.error-message').classList.add('d-block');
            });
        });
    });
}); 