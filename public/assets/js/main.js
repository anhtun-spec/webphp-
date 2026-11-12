/* TMU Tài Liệu - Main JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Bạn có chắc chắn muốn thực hiện hành động này?')) {
                e.preventDefault();
            }
        });
    });

    // Favorite toggle (AJAX)
    document.querySelectorAll('.btn-favorite').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const docId = this.dataset.id;
            const icon = this.querySelector('i');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.content : '';

            fetch(url('documents/' + docId + '/favorite'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: '_csrf_token=' + encodeURIComponent(csrf)
            })
            .then(async r => {
                if (r.redirected) {
                    window.location.href = r.url;
                    return null;
                }
                const data = await r.json();
                if (!r.ok) {
                    throw new Error(data.error || 'Không thể cập nhật yêu thích.');
                }
                return data;
            })
            .then(data => {
                if (!data) return;
                const labelEl = this.querySelector('.favorite-label');
                if (labelEl) {
                    icon.className = (data.favorited ? 'bi bi-bookmark-heart-fill' : 'bi bi-bookmark-heart') + ' me-1';
                    labelEl.textContent = data.favorited ? 'Đã lưu' : 'Lưu tài liệu';
                } else {
                    icon.className = data.favorited ? 'bi bi-bookmark-heart-fill text-danger' : 'bi bi-bookmark-heart';
                }
                this.title = data.favorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
            })
            .catch(console.error);
        });
    });
});

// Helper for relative URL matching PHP's url()
function url(path) {
    const base = window.location.pathname.includes('/public')
        ? window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7)
        : window.location.origin;
    return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
}

