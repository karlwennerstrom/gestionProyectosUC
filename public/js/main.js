/**
 * ===================================================================
 * SISTEMA MULTI-ÁREA UC - JAVASCRIPT PRINCIPAL
 * ================================================================= 
 */

// Configuración global
const App = {
    config: window.APP_CONFIG || {},
    
    // Estado de la aplicación
    state: {
        isLoading: false,
        notifications: [],
        user: null
    },

    // Inicialización
    init() {
        this.state.user = this.config.user;
        this.bindGlobalEvents();
        this.checkSystemStatus();
        this.initializeComponents();
        
        console.log('🏛️ Sistema Multi-Área UC iniciado correctamente');
        if (this.config.environment === 'development') {
            console.log('Config:', this.config);
        }
    },

    // Eventos globales
    bindGlobalEvents() {
        // Verificar estado del sistema
        document.addEventListener('DOMContentLoaded', () => {
            this.updateStatusIndicator();
        });

        // Manejar formularios AJAX
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });

        // Confirmaciones de eliminación
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('confirm-delete')) {
                e.preventDefault();
                this.confirmDelete(e.target);
            }
        });

        // Tooltips
        this.initTooltips();
    },

    // Verificar estado del sistema
    async checkSystemStatus() {
        try {
            const response = await fetch('/status');
            const data = await response.json();
            
            const indicator = document.querySelector('.status-indicator');
            if (indicator) {
                if (data.system === 'operational') {
                    indicator.style.background = '#10b981'; // Verde
                } else if (data.system === 'degraded') {
                    indicator.style.background = '#f59e0b'; // Amarillo
                } else {
                    indicator.style.background = '#ef4444'; // Rojo
                }
            }
        } catch (error) {
            console.warn('No se pudo verificar el estado del sistema:', error);
            const indicator = document.querySelector('.status-indicator');
            if (indicator) {
                indicator.style.background = '#6b7280'; // Gris
            }
        }
    },

    // Actualizar indicador de estado
    updateStatusIndicator() {
        setTimeout(() => this.checkSystemStatus(), 1000);
        // Actualizar cada 30 segundos
        setInterval(() => this.checkSystemStatus(), 30000);
    },

    // Inicializar componentes
    initializeComponents() {
        this.initLoadingStates();
        this.initFormValidation();
        this.initDataTables();
        this.initModals();
    },

    // Estados de carga
    initLoadingStates() {
        // Agregar loading a botones cuando se hace clic
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn') && !e.target.disabled) {
                const btn = e.target;
                const originalText = btn.textContent;
                
                // Solo agregar loading si es un enlace o form submit
                if (btn.tagName === 'A' || btn.type === 'submit') {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="loading"></span> Cargando...';
                    
                    // Restaurar después de 5 segundos máximo
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }, 5000);
                }
            }
        });
    },

    // Validación de formularios
    initFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });

            // Validación en tiempo real
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            });
        });
    },

    // Validar formulario
    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    },

    // Validar campo individual
    validateField(field) {
        const value = field.value.trim();
        const fieldGroup = field.closest('.form-group');
        let errorElement = fieldGroup?.querySelector('.form-error');

        // Remover errores previos
        if (errorElement) {
            errorElement.remove();
        }
        field.classList.remove('error');

        // Validaciones
        let errorMessage = '';

        if (field.required && !value) {
            errorMessage = 'Este campo es obligatorio';
        } else if (field.type === 'email' && value && !this.isValidEmail(value)) {
            errorMessage = 'Ingresa un email válido';
        } else if (field.minLength && value.length < field.minLength) {
            errorMessage = `Mínimo ${field.minLength} caracteres`;
        }

        // Mostrar error si existe
        if (errorMessage) {
            field.classList.add('error');
            if (fieldGroup) {
                const error = document.createElement('div');
                error.className = 'form-error';
                error.textContent = errorMessage;
                fieldGroup.appendChild(error);
            }
            return false;
        }

        return true;
    },

    // Validar email
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Manejar formularios AJAX
    async handleAjaxForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Estado de carga
        this.setLoading(submitBtn, true);
        
        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Operación exitosa', 'success');
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            } else {
                this.showNotification(result.message || 'Error en la operación', 'error');
            }
        } catch (error) {
            console.error('Error en formulario AJAX:', error);
            this.showNotification('Error de conexión', 'error');
        } finally {
            this.setLoading(submitBtn, false);
        }
    },

    // Estados de carga para elementos
    setLoading(element, isLoading) {
        if (!element) return;

        if (isLoading) {
            element.disabled = true;
            element.dataset.originalText = element.textContent;
            element.innerHTML = '<span class="loading"></span> Cargando...';
        } else {
            element.disabled = false;
            element.textContent = element.dataset.originalText || 'Enviar';
        }
    },

    // Confirmación de eliminación
    confirmDelete(element) {
        const message = element.dataset.confirmMessage || '¿Estás seguro de que quieres eliminar este elemento?';
        
        if (confirm(message)) {
            if (element.tagName === 'A') {
                window.location.href = element.href;
            } else if (element.tagName === 'BUTTON') {
                element.closest('form')?.submit();
            }
        }
    },

    // Notificaciones
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1000';
        notification.style.minWidth = '300px';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        notification.style.transition = 'all 0.3s ease';

        document.body.appendChild(notification);

        // Animar entrada
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });

        // Auto-remover
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    },

    // Inicializar tooltips
    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });

            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    },

    // Mostrar tooltip
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: var(--gray-800);
            color: white;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        `;

        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
        tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;

        requestAnimationFrame(() => {
            tooltip.style.opacity = '1';
        });

        this._currentTooltip = tooltip;
    },

    // Ocultar tooltip
    hideTooltip() {
        if (this._currentTooltip) {
            this._currentTooltip.style.opacity = '0';
            setTimeout(() => {
                if (this._currentTooltip) {
                    this._currentTooltip.remove();
                    this._currentTooltip = null;
                }
            }, 200);
        }
    },

    // Inicializar tablas de datos
    initDataTables() {
        const tables = document.querySelectorAll('.data-table');
        
        tables.forEach(table => {
            this.makeTableSortable(table);
            this.makeTableSearchable(table);
        });
    },

    // Hacer tabla ordenable
    makeTableSortable(table) {
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(table, header);
            });
        });
    },

    // Ordenar tabla
    sortTable(table, header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const isAscending = !header.classList.contains('sort-asc');

        rows.sort((a, b) => {
            const aText = a.children[columnIndex]?.textContent.trim() || '';
            const bText = b.children[columnIndex]?.textContent.trim() || '';
            
            return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });

        // Actualizar clases de ordenamiento
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

        // Reordenar filas
        rows.forEach(row => tbody.appendChild(row));
    },

    // Hacer tabla buscable
    makeTableSearchable(table) {
        const searchInput = table.parentNode.querySelector('.table-search');
        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            this.filterTable(table, e.target.value);
        });
    },

    // Filtrar tabla
    filterTable(table, searchTerm) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const term = searchTerm.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    },

    // Inicializar modales
    initModals() {
        // Cerrar modales con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Cerrar modales al hacer clic en el overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeModal(e.target.closest('.modal'));
            }
        });
    },

    // Abrir modal
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    },

    // Cerrar modal
    closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    },

    // Cerrar todos los modales
    closeAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => this.closeModal(modal));
    },

    // Utilidades
    utils: {
        // Formatear fecha
        formatDate(date, locale = 'es-CL') {
            return new Date(date).toLocaleDateString(locale);
        },

        // Formatear números
        formatNumber(number, locale = 'es-CL') {
            return new Intl.NumberFormat(locale).format(number);
        },

        // Formatear moneda
        formatCurrency(amount, currency = 'CLP', locale = 'es-CL') {
            return new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        // Debounce
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Throttle
        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }
};

// Auto-inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}

// Exponer App globalmente para desarrollo
if (window.APP_CONFIG?.environment === 'development') {
    window.App = App;
}