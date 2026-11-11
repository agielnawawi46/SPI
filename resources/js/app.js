import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

// ============================================
// Alpine.js - Global State & Components
// ============================================
Alpine.plugin(collapse);
window.Alpine = Alpine;

Alpine.data('sidebar', () => ({
    collapsed: false,
    toggle() {
        this.collapsed = !this.collapsed;
    }
}));

Alpine.data('notificationDropdown', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
}));

Alpine.data('userDropdown', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
}));

Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,
    show() { this.open = true; },
    hide() { this.open = false; },
    toggle() { this.open = !this.open; },
}));

Alpine.data('confirmDelete', () => ({
    open: false,
    formAction: '',
    show(action) {
        this.formAction = action;
        this.open = true;
    },
    hide() { this.open = false; },
}));

// Flash message auto-dismiss
Alpine.data('flashMessage', () => ({
    show: true,
    dismiss() {
        this.show = false;
    },
    init() {
        setTimeout(() => { this.show = false; }, 5000);
    }
}));

// Kertas Kerja PKPT 6-Category Upload Component
const createKertasKerjaCard = (config) => ({
    key: config.key,
    label: config.label,
    files: config.files || [],
    csrfToken: config.csrfToken,
    uploadUrl: config.uploadUrl,
    selectedFiles: [],
    isDragging: false,
    isUploading: false,
    uploadProgress: 0,
    errorMessage: '',

    get hasFiles() {
        return this.files.length > 0;
    },
    getDt() {
        if (!this.$el || !this.$el._dt) {
            const newDt = new DataTransfer();
            if (this.$el) this.$el._dt = newDt;
            else this._dt = newDt;
        }
        return this.$el ? this.$el._dt : this._dt;
    },
    setDt(newDt) {
        if (this.$el) this.$el._dt = newDt;
        this._dt = newDt;
    },
    addFiles(newFiles) {
        const dt = this.getDt();
        const files = Array.from(newFiles || []);
        for (let f of files) {
            const exists = Array.from(dt.files).some(x => x.name === f.name && x.size === f.size && x.lastModified === f.lastModified);
            if (!exists) dt.items.add(f);
        }
        if (this.$refs.fileInput) {
            this.$refs.fileInput.files = dt.files;
        }
        this.syncList();
    },
    handleFiles(e) {
        this.addFiles(e.target.files);
    },
    handleDrop(e) {
        this.isDragging = false;
        if (e.dataTransfer && e.dataTransfer.files) {
            this.addFiles(e.dataTransfer.files);
        }
    },
    removeSelectedFile(idx) {
        const dt = this.getDt();
        const newDt = new DataTransfer();
        Array.from(dt.files).forEach((f, i) => {
            if (i !== idx) newDt.items.add(f);
        });
        this.setDt(newDt);
        if (this.$refs.fileInput) {
            this.$refs.fileInput.files = newDt.files;
            if (newDt.files.length === 0) {
                this.$refs.fileInput.value = '';
            }
        }
        this.syncList();
    },
    clearFiles() {
        this.setDt(new DataTransfer());
        if (this.$refs.fileInput) {
            this.$refs.fileInput.value = '';
        }
        this.selectedFiles = [];
        this.errorMessage = '';
    },
    syncList() {
        const dt = this.getDt();
        this.selectedFiles = Array.from(dt.files).map(f => ({
            name: f.name,
            size: f.size > 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : Math.round(f.size / 1024) + ' KB'
        }));
    },
    submitUpload() {
        const dt = this.getDt();
        const filesToUpload = dt.files.length > 0 ? dt.files : (this.$refs.fileInput ? this.$refs.fileInput.files : []);
        if (filesToUpload.length === 0 || this.isUploading) return;
        
        this.isUploading = true;
        this.uploadProgress = 0;
        this.errorMessage = '';

        const formData = new FormData();
        formData.append('_token', this.csrfToken);
        formData.append('category', this.key);
        for (let i = 0; i < filesToUpload.length; i++) {
            formData.append('attachments[]', filesToUpload[i]);
        }

        const targetUrl = this.uploadUrl || (this.$refs.uploadForm ? this.$refs.uploadForm.action : window.location.href);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', targetUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                this.uploadProgress = Math.round((e.loaded / e.total) * 100);
            }
        };

        xhr.onload = () => {
            this.isUploading = false;
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        this.files = res.attachments || [];
                        this.clearFiles();
                        window.dispatchEvent(new CustomEvent('pkpt-attachments-updated', {
                            detail: { total: res.total_count, category: this.key, message: res.message }
                        }));
                    } else {
                        this.errorMessage = res.message || 'Gagal mengunggah berkas.';
                    }
                } catch (err) {
                    this.errorMessage = 'Terjadi kesalahan respons server.';
                }
            } else {
                try {
                    const errRes = JSON.parse(xhr.responseText);
                    this.errorMessage = errRes.message || 'Gagal mengunggah berkas. Pastikan format file PDF, Word, atau Excel.';
                } catch(e) {
                    this.errorMessage = 'Gagal mengunggah berkas (Error ' + xhr.status + ').';
                }
            }
        };

        xhr.onerror = () => {
            this.isUploading = false;
            this.errorMessage = 'Koneksi jaringan terputus saat mengunggah.';
        };

        xhr.send(formData);
    },
    deleteFile(att) {
        if (!att || !att.delete_url) return;
        const msg = 'Apakah Anda yakin ingin menghapus file "' + att.file_name + '"?';

        const proceed = async () => {
            try {
                const res = await fetch(att.delete_url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.files = this.files.filter(f => f.id !== att.id);
                        window.dispatchEvent(new CustomEvent('pkpt-attachments-updated', {
                            detail: { 
                                total: data.total_count, 
                                category: this.key, 
                                message: 'File ' + att.file_name + ' berhasil dihapus.' 
                            }
                        }));
                    } else {
                        this.errorMessage = data.message || 'Gagal menghapus file.';
                    }
                } else {
                    const errData = await res.json().catch(() => ({}));
                    this.errorMessage = errData.message || ('Gagal menghapus file (Error ' + res.status + ').');
                }
            } catch (e) {
                this.errorMessage = 'Terjadi kesalahan jaringan saat menghapus file.';
            }
        };

        if (typeof window.showConfirmDialog === 'function') {
            window.showConfirmDialog(msg, proceed);
        } else if (confirm(msg)) {
            proceed();
        }
    }
});

Alpine.data('kertasKerjaCard', createKertasKerjaCard);
window.kertasKerjaCard = createKertasKerjaCard;

Alpine.start();

// ============================================
// Chart.js - Global Defaults
// ============================================
window.Chart = Chart;

Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#888888';
Chart.defaults.plugins.legend.position = 'bottom';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 20;
Chart.defaults.elements.line.tension = 0.3;

// Brand palette for charts
window.SPI_CHART_COLORS = {
    primary:    '#158b9f',
    primaryLight: 'rgba(21, 139, 159, 0.15)',
    success:    '#16a34a',
    warning:    '#d97706',
    danger:     '#dc2626',
    info:       '#2563eb',
    muted:      '#9ca3af',
    // For multi-series charts
    palette: ['#158b9f', '#16a34a', '#d97706', '#dc2626', '#8b5cf6', '#0891b2'],
};

// ============================================
// In-App Notification Polling
// ============================================
function initNotificationPolling() {
    const badge = document.getElementById('notif-count');
    const list  = document.getElementById('notif-list');
    if (!badge) return;

    async function fetchNotifications() {
        try {
            const res = await fetch('/notifications/unread', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();

            // Update badge
            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            // Update dropdown list if open
            if (list) {
                renderNotifications(list, data.notifications);
            }
        } catch (err) {
            console.warn('Notification polling error:', err);
        }
    }

    function renderNotifications(container, items) {
        if (!items || items.length === 0) {
            container.innerHTML = `<div class="px-4 py-8 text-center text-sm text-gray-400">Tidak ada notifikasi baru</div>`;
            return;
        }
        container.innerHTML = items.map(n => `
            <a href="${n.link || '#'}"
               data-notif-id="${n.id}"
               data-notif-read="${n.is_read ? '1' : '0'}"
               class="notif-item flex gap-3 px-4 py-3 hover:bg-surface-50 transition-colors border-b border-surface-100 last:border-0 ${!n.is_read ? 'bg-primary-50/40' : ''}">
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${typeToClass(n.type)}">
                    ${typeToIcon(n.type)}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-charcoal truncate">${n.title}</p>
                    <p class="text-xs text-charcoal-muted mt-0.5 line-clamp-2">${n.message}</p>
                    <p class="text-xs text-charcoal-muted/70 mt-1">${n.time_ago}</p>
                </div>
                ${!n.is_read ? '<div class="flex-shrink-0 w-2 h-2 rounded-full bg-primary-500 self-center"></div>' : ''}
            </a>
        `).join('');

        // Attach click handlers to mark as read before navigating
        container.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', async function(e) {
                const id      = this.dataset.notifId;
                const isRead  = this.dataset.notifRead === '1';
                const href    = this.getAttribute('href');

                if (!isRead && id) {
                    e.preventDefault();
                    try {
                        await fetch(`/notifications/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                    } catch (_) {}
                    // Re-fetch to update badge count
                    fetchNotifications();
                    // Navigate
                    if (href && href !== '#') window.location.href = href;
                }
            });
        });
    }

    function typeToClass(type) {
        const map = {
            deadline: 'bg-red-100 text-red-600',
            approval: 'bg-amber-100 text-amber-600',
            info:     'bg-blue-100 text-blue-600',
            success:  'bg-green-100 text-green-600',
            revision: 'bg-orange-100 text-orange-600',
            warning:  'bg-yellow-100 text-yellow-600',
        };
        return map[type] || map.info;
    }

    function typeToIcon(type) {
        const icons = {
            deadline: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            approval: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            info:     '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            success:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            revision: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>',
            warning:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        };
        return icons[type] || icons.info;
    }

    const markAllBtn = document.getElementById('mark-all-read-btn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
            } catch (_) {}
            fetchNotifications(); // Refresh list and badge immediately
        });
    }

    fetchNotifications();
    setInterval(fetchNotifications, 5000); // poll every 5s for near real-time
}

document.addEventListener('DOMContentLoaded', initNotificationPolling);
