/**
 * Profiles Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const isAdminCheckbox = document.getElementById('is_admin');
    const permissionsSection = document.getElementById('permissionsSection');
    const moduleSelectAllCheckboxes = document.querySelectorAll('.module-select-all');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

    // Toggle permissions section when is_admin changes
    if (isAdminCheckbox && permissionsSection) {
        function togglePermissionsSection() {
            if (isAdminCheckbox.checked) {
                permissionsSection.classList.add('disabled');
                // Uncheck all permissions when admin is enabled
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                moduleSelectAllCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.indeterminate = false;
                });
            } else {
                permissionsSection.classList.remove('disabled');
            }
        }

        isAdminCheckbox.addEventListener('change', togglePermissionsSection);

        // Initial state
        togglePermissionsSection();
    }

    // Module select-all functionality
    moduleSelectAllCheckboxes.forEach(moduleCheckbox => {
        const module = moduleCheckbox.dataset.module;
        const modulePermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);

        // Handle module checkbox change
        moduleCheckbox.addEventListener('change', function() {
            modulePermissions.forEach(permCheckbox => {
                permCheckbox.checked = this.checked;
            });
        });

        // Handle individual permission checkbox change
        modulePermissions.forEach(permCheckbox => {
            permCheckbox.addEventListener('change', function() {
                updateModuleCheckbox(module);
            });
        });
    });

    // Update module checkbox state based on individual permissions
    function updateModuleCheckbox(module) {
        const moduleCheckbox = document.querySelector(`.module-select-all[data-module="${module}"]`);
        const modulePermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);

        if (!moduleCheckbox || modulePermissions.length === 0) return;

        const checkedCount = Array.from(modulePermissions).filter(cb => cb.checked).length;
        const totalCount = modulePermissions.length;

        if (checkedCount === 0) {
            moduleCheckbox.checked = false;
            moduleCheckbox.indeterminate = false;
        } else if (checkedCount === totalCount) {
            moduleCheckbox.checked = true;
            moduleCheckbox.indeterminate = false;
        } else {
            moduleCheckbox.checked = false;
            moduleCheckbox.indeterminate = true;
        }
    }

    // Initialize all module checkbox states
    function initializeModuleCheckboxes() {
        const modules = new Set();
        permissionCheckboxes.forEach(cb => {
            if (cb.dataset.module) {
                modules.add(cb.dataset.module);
            }
        });
        modules.forEach(module => updateModuleCheckbox(module));
    }

    initializeModuleCheckboxes();

    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja excluir este perfil?')) {
                e.preventDefault();
            }
        });
    });
});
