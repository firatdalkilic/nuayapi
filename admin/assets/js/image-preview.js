// Resim önizleme fonksiyonları
document.addEventListener('DOMContentLoaded', function() {
    // Resim seçildiğinde önizleme göster
    document.querySelectorAll('.property-image-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const files = e.target.files;
            const previewContainer = this.closest('.image-upload-container').querySelector('.image-preview-container');
            
            // Önceki önizlemeleri temizle
            previewContainer.innerHTML = '';

            // Her dosya için önizleme oluştur
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert('Lütfen sadece resim dosyası yükleyin!');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewWrapper = document.createElement('div');
                    previewWrapper.className = 'preview-wrapper position-relative d-inline-block m-2';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail';
                    img.style.maxWidth = '150px';
                    img.style.height = 'auto';

                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0';
                    removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                    removeBtn.onclick = function() {
                        previewWrapper.remove();
                    };

                    previewWrapper.appendChild(img);
                    previewWrapper.appendChild(removeBtn);
                    previewContainer.appendChild(previewWrapper);
                };
                reader.readAsDataURL(file);
            });
        });
    });

    // Mevcut resimlerin silinmesi
    document.querySelectorAll('.delete-image-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Bu resmi silmek istediğinizden emin misiniz?')) {
                const imageContainer = this.closest('.image-container');
                imageContainer.remove();
            }
        });
    });
}); 