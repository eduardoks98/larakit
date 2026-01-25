/**
 * Ad Units Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja excluir este Ad Unit?')) {
                e.preventDefault();
            }
        });
    });

    // Preview modal
    const previewButtons = document.querySelectorAll('.btn-preview');
    const previewModal = document.getElementById('previewModal');
    const previewContent = document.getElementById('previewContent');
    const closeModal = document.getElementById('closeModal');
    const modalBackdrop = document.querySelector('.modal-backdrop');

    if (previewModal) {
        previewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const adUnitId = this.dataset.adUnitId;
                const content = document.getElementById('preview-' + adUnitId);

                if (content) {
                    previewContent.innerHTML = content.innerHTML;
                    previewModal.style.display = 'flex';
                }
            });
        });

        function closePreviewModal() {
            previewModal.style.display = 'none';
            previewContent.innerHTML = '';
        }

        if (closeModal) {
            closeModal.addEventListener('click', closePreviewModal);
        }

        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', closePreviewModal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && previewModal.style.display === 'flex') {
                closePreviewModal();
            }
        });
    }

    // Format preview in form
    const formatSelect = document.getElementById('format');
    const adPreview = document.getElementById('adPreview');

    if (formatSelect && adPreview) {
        const formatDimensions = {
            'banner': { width: 468, height: 60, label: 'Banner (468x60)' },
            'leaderboard': { width: 728, height: 90, label: 'Leaderboard (728x90)' },
            'rectangle': { width: 300, height: 250, label: 'Rectangle (300x250)' },
            'skyscraper': { width: 120, height: 600, label: 'Skyscraper (120x600)' },
            'large_rectangle': { width: 336, height: 280, label: 'Large Rectangle (336x280)' },
            'responsive': { width: '100%', height: 'auto', label: 'Responsive (Auto)' }
        };

        function updatePreview() {
            const format = formatSelect.value;
            const dims = formatDimensions[format];

            if (!dims) return;

            if (format === 'responsive') {
                adPreview.innerHTML = `
                    <div style="width: 100%; background: linear-gradient(135deg, #374151 0%, #1f2937 100%); border: 2px dashed #4b5563; border-radius: 8px; padding: 40px; text-align: center;">
                        <div style="color: #9ca3af; font-size: 14px;">
                            <svg style="width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <p style="margin: 0;"><strong>Responsive</strong></p>
                            <p style="margin: 8px 0 0; font-size: 12px;">Ajusta automaticamente ao container</p>
                        </div>
                    </div>
                `;
            } else {
                const scale = dims.width > 400 ? 0.5 : 1;
                const displayWidth = dims.width * scale;
                const displayHeight = dims.height * scale;

                adPreview.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 16px;">
                        <div style="width: ${displayWidth}px; height: ${displayHeight}px; background: linear-gradient(135deg, #374151 0%, #1f2937 100%); border: 2px dashed #4b5563; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                            <span style="color: #9ca3af; font-size: 12px;">${dims.width} x ${dims.height}</span>
                        </div>
                        <span style="color: #6b7280; font-size: 12px;">${dims.label}${scale < 1 ? ' (escala 50%)' : ''}</span>
                    </div>
                `;
            }
        }

        formatSelect.addEventListener('change', updatePreview);
        updatePreview();
    }
});
