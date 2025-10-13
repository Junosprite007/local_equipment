/**
 * VCC Table Column Management
 *
 * Handles column visibility, resizing, and user preferences for the VCC submissions table.
 *
 * RTL LANGUAGE SUPPORT CONSIDERATIONS:
 * - Column resize handles will need to be repositioned for RTL layouts (left/right swap)
 * - Dropdown positioning for column management controls may need RTL-specific adjustments
 * - Drag and drop column reordering will need directional logic updates for RTL
 * - Text alignment in column headers and cells should respect RTL text direction
 * - Horizontal scrolling behavior may need RTL-specific handling
 *
 * @module     local_equipment/vcc_table_columns
 * @author     Fun Learning Company
 * @copyright  2025 Fun Learning Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import { get_string as getString } from 'core/str';
import Log from 'core/log';

/*
 * DEBUG CONFIGURATION FLAGS FOR JAVASCRIPT COLUMN MANAGEMENT
 *
 * These debug flags provide comprehensive monitoring and logging capabilities for the
 * JavaScript-based column management system. Each flag controls a specific aspect of
 * the debugging functionality, allowing developers to focus on particular areas without
 * being overwhelmed by excessive console output. The flags are implemented as constants
 * to ensure consistent behavior throughout the module and to enable easy toggling during
 * development and testing phases.
 *
 * When enabled, these flags activate detailed console logging that tracks user interactions,
 * performance metrics, and error conditions. The granular nature of these flags allows
 * for targeted debugging of specific functionality while maintaining clean console output
 * in production environments. All flags default to 'false' to ensure zero performance
 * impact and no console pollution in production deployments.
 *
 * DEBUG_COLUMN_MANAGEMENT: Logs column visibility changes, management UI interactions,
 * and overall column management state changes.
 *
 * DEBUG_RESIZE_OPERATIONS: Tracks column resizing activities, constraint validation,
 * and resize handle interactions for performance optimization.
 *
 * DEBUG_PREFERENCE_OPERATIONS: Monitors preference save/load operations, validation
 * processes, and AJAX communication with the server.
 *
 * DEBUG_PERFORMANCE: Captures timing information for DOM manipulations, AJAX requests,
 * and other performance-critical operations to identify bottlenecks.
 */
const DEBUG_COLUMN_MANAGEMENT = false;
const DEBUG_RESIZE_OPERATIONS = false;
const DEBUG_PREFERENCE_OPERATIONS = false;
const DEBUG_PERFORMANCE = false;

/**
 * Column management class for VCC table
 */
class VccTableColumns {
    /**
     * Constructor
     * @param {string} tableSelector - CSS selector for the table
     */
    constructor(tableSelector) {
        this.tableSelector = tableSelector;
        this.table = document.querySelector(tableSelector);
        this.preferences = {
            hiddenColumns: [],
            columnWidths: {},
        };

        // Column management UI elements
        this.controlsContainer = null;
        this.columnToggleDropdown = null;

        // Resize related properties
        this.isResizing = false;
        this.currentColumn = null;
        this.startX = 0;
        this.startWidth = 0;

        this.init();
    }

    /**
     * Initialize the column management system
     */
    async init() {
        if (!this.table) {
            Log.debug('VCC table not found: ' + this.tableSelector);
            return;
        }

        // Load user preferences
        await this.loadUserPreferences();

        // Initialize column controls UI
        this.initializeColumnControls();

        // Initialize column resizing
        this.initializeColumnResizing();

        // Apply saved preferences
        this.applyColumnPreferences();

        // Add horizontal scrolling if needed
        this.setupHorizontalScrolling();

        // Bind event listeners
        this.bindEventListeners();
    }

    /**
     * Load user preferences from Moodle
     */
    async loadUserPreferences() {
        try {
            const requests = [
                {
                    methodname: 'core_user_get_user_preferences',
                    args: { name: 'local_equipment_vcc_table_columns' },
                },
                {
                    methodname: 'core_user_get_user_preferences',
                    args: { name: 'local_equipment_vcc_table_column_widths' },
                },
            ];

            const responses = await Ajax.call(requests);

            // Parse hidden columns preference
            if (
                responses[0].preferences &&
                responses[0].preferences.length > 0
            ) {
                const hiddenColumnsData = responses[0].preferences[0].value;
                if (hiddenColumnsData) {
                    try {
                        const parsed = JSON.parse(hiddenColumnsData);
                        this.preferences.hiddenColumns =
                            parsed.hidden_columns || [];
                    } catch (e) {
                        Log.debug(
                            'Failed to parse hidden columns preference: ' +
                                e.message
                        );
                    }
                }
            }

            // Parse column widths preference
            if (
                responses[1].preferences &&
                responses[1].preferences.length > 0
            ) {
                const columnWidthsData = responses[1].preferences[0].value;
                if (columnWidthsData) {
                    try {
                        this.preferences.columnWidths =
                            JSON.parse(columnWidthsData);
                    } catch (e) {
                        Log.debug(
                            'Failed to parse column widths preference: ' +
                                e.message
                        );
                    }
                }
            }
        } catch (error) {
            Log.debug('Failed to load user preferences: ' + error.message);
        }
    }

    /**
     * Save user preferences to Moodle
     */
    async saveUserPreferences() {
        try {
            const requests = [
                {
                    methodname: 'core_user_set_user_preferences',
                    args: {
                        preferences: [
                            {
                                name: 'local_equipment_vcc_table_columns',
                                value: JSON.stringify({
                                    hidden_columns:
                                        this.preferences.hiddenColumns,
                                }),
                            },
                        ],
                    },
                },
                {
                    methodname: 'core_user_set_user_preferences',
                    args: {
                        preferences: [
                            {
                                name: 'local_equipment_vcc_table_column_widths',
                                value: JSON.stringify(
                                    this.preferences.columnWidths
                                ),
                            },
                        ],
                    },
                },
            ];

            await Ajax.call(requests);
        } catch (error) {
            Log.debug('Failed to save user preferences: ' + error.message);
            Notification.exception(error);
        }
    }

    /**
     * Initialize column controls UI
     */
    initializeColumnControls() {
        // Create controls container
        this.controlsContainer = document.createElement('div');
        this.controlsContainer.className = 'vcc-table-column-controls mb-3';
        this.controlsContainer.innerHTML = this.getColumnControlsHTML();

        // Insert before table
        this.table.parentNode.insertBefore(this.controlsContainer, this.table);

        // Get dropdown element
        this.columnToggleDropdown = this.controlsContainer.querySelector(
            '.column-toggle-dropdown'
        );

        // Populate column checkboxes
        this.populateColumnToggles();
    }

    /**
     * Get HTML for column controls
     * @returns {string} HTML string
     */
    getColumnControlsHTML() {
        return `
            <div class="d-flex justify-content-end align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-columns" aria-hidden="true"></i>
                        <span class="ms-1">Columns</span>
                    </button>
                    <div class="dropdown-menu column-toggle-dropdown p-2" style="min-width: 220px;">
                        <!-- Column toggles will be populated here -->
                    </div>
                </div>
                <button class="btn btn-outline-secondary btn-sm reset-columns" type="button">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                    <span class="ms-1">Reset Layout</span>
                </button>
            </div>
        `;
    }

    /**
     * Populate column toggle checkboxes
     */
    populateColumnToggles() {
        if (!this.columnToggleDropdown) {
            return;
        }

        const headers = this.table.querySelectorAll('thead th');
        let togglesHTML = '<div class="column-toggles">';

        headers.forEach((th, index) => {
            const columnName = this.getColumnName(th, index);
            const isHidden =
                this.preferences.hiddenColumns.includes(columnName);
            const displayName = this.getColumnDisplayName(th);

            togglesHTML += `
                <div class="form-check">
                    <input class="form-check-input column-toggle" type="checkbox"
                           data-column="${columnName}" id="col-toggle-${index}"
                           ${!isHidden ? 'checked' : ''}>
                    <label class="form-check-label" for="col-toggle-${index}">
                        ${displayName}
                    </label>
                </div>
            `;
        });

        togglesHTML += '</div>';
        this.columnToggleDropdown.innerHTML = togglesHTML;
    }

    /**
     * Get column name for preference storage
     * @param {Element} th - Table header element
     * @param {number} index - Column index
     * @returns {string} Column name
     */
    getColumnName(th, index) {
        // Try to get from data attribute first
        if (th.dataset.column) {
            return th.dataset.column;
        }

        // Generate from class names or text content
        const classList = Array.from(th.classList);
        const columnClass = classList.find((cls) => cls.startsWith('col-'));
        if (columnClass) {
            return columnClass.replace('col-', '');
        }

        // Fallback to index-based naming
        return `column_${index}`;
    }

    /**
     * Get display name for column
     * @param {Element} th - Table header element
     * @returns {string} Display name
     */
    getColumnDisplayName(th) {
        // Remove sorting indicators and get clean text
        const clone = th.cloneNode(true);
        const sortIndicators = clone.querySelectorAll('.sort-link, .iconsort');
        sortIndicators.forEach((el) => el.remove());

        return clone.textContent.trim() || 'Column';
    }

    /**
     * Initialize column resizing functionality
     */
    initializeColumnResizing() {
        const headers = this.table.querySelectorAll('thead th');

        headers.forEach((th, index) => {
            // Skip last column (actions column typically)
            if (index === headers.length - 1) {
                return;
            }

            // Add resize handle
            const resizeHandle = document.createElement('div');
            resizeHandle.className = 'column-resize-handle';
            resizeHandle.style.cssText = `
                position: absolute;
                top: 0;
                right: 0;
                width: 4px;
                height: 100%;
                background: transparent;
                cursor: col-resize;
                user-select: none;
                z-index: 1;
            `;

            // Make header relative positioned
            th.style.position = 'relative';
            th.appendChild(resizeHandle);

            // Bind resize events
            this.bindResizeEvents(resizeHandle, th);
        });
    }

    /**
     * Bind resize events to a column handle
     * @param {Element} handle - Resize handle element
     * @param {Element} th - Table header element
     */
    bindResizeEvents(handle, th) {
        handle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            this.startResize(e, th);
        });

        // Add hover effect
        handle.addEventListener('mouseenter', () => {
            handle.style.background = 'rgba(0,123,255,0.3)';
        });

        handle.addEventListener('mouseleave', () => {
            if (!this.isResizing) {
                handle.style.background = 'transparent';
            }
        });
    }

    /**
     * Start column resizing
     * @param {MouseEvent} e - Mouse event
     * @param {Element} th - Table header element
     */
    startResize(e, th) {
        this.isResizing = true;
        this.currentColumn = th;
        this.startX = e.clientX;
        this.startWidth = th.offsetWidth;

        // Add global event listeners
        document.addEventListener('mousemove', this.handleResize.bind(this));
        document.addEventListener('mouseup', this.stopResize.bind(this));

        // Prevent text selection
        document.body.style.userSelect = 'none';
        th.style.userSelect = 'none';

        // Visual feedback
        th.classList.add('resizing');
    }

    /**
     * Handle column resizing
     * @param {MouseEvent} e - Mouse event
     */
    handleResize(e) {
        if (!this.isResizing || !this.currentColumn) {
            return;
        }

        const diff = e.clientX - this.startX;
        const newWidth = Math.max(50, this.startWidth + diff); // Minimum width of 50px

        this.currentColumn.style.width = newWidth + 'px';
        this.currentColumn.style.minWidth = newWidth + 'px';
        this.currentColumn.style.maxWidth = newWidth + 'px';
    }

    /**
     * Stop column resizing
     */
    stopResize() {
        if (!this.isResizing || !this.currentColumn) {
            return;
        }

        // Clean up
        document.removeEventListener('mousemove', this.handleResize.bind(this));
        document.removeEventListener('mouseup', this.stopResize.bind(this));

        document.body.style.userSelect = '';
        this.currentColumn.style.userSelect = '';
        this.currentColumn.classList.remove('resizing');

        // Save new width
        const columnName = this.getColumnName(
            this.currentColumn,
            Array.from(this.table.querySelectorAll('thead th')).indexOf(
                this.currentColumn
            )
        );
        const newWidth = this.currentColumn.style.width;

        this.preferences.columnWidths[columnName] = newWidth;
        this.saveUserPreferences();

        // Reset resize handle background
        const handle = this.currentColumn.querySelector(
            '.column-resize-handle'
        );
        if (handle) {
            handle.style.background = 'transparent';
        }

        this.isResizing = false;
        this.currentColumn = null;
    }

    /**
     * Apply saved column preferences
     */
    applyColumnPreferences() {
        const headers = this.table.querySelectorAll('thead th');
        const rows = this.table.querySelectorAll('tbody tr');

        headers.forEach((th, index) => {
            const columnName = this.getColumnName(th, index);

            // Apply visibility
            if (this.preferences.hiddenColumns.includes(columnName)) {
                th.style.display = 'none';
                rows.forEach((row) => {
                    const cell = row.children[index];
                    if (cell) {
                        cell.style.display = 'none';
                    }
                });
            }

            // Apply width
            if (this.preferences.columnWidths[columnName]) {
                const width = this.preferences.columnWidths[columnName];
                th.style.width = width;
                th.style.minWidth = width;
                th.style.maxWidth = width;
            }
        });
    }

    /**
     * Setup horizontal scrolling with fixed headers
     */
    setupHorizontalScrolling() {
        // Create wrapper for horizontal scrolling
        if (!this.table.closest('.table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive vcc-table-wrapper';
            wrapper.style.cssText = `
                overflow-x: auto;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
            `;

            this.table.parentNode.insertBefore(wrapper, this.table);
            wrapper.appendChild(this.table);
        }
    }

    /**
     * Bind event listeners
     */
    bindEventListeners() {
        // Column toggle checkboxes
        this.controlsContainer.addEventListener('change', (e) => {
            if (e.target.classList.contains('column-toggle')) {
                this.toggleColumn(e.target.dataset.column, e.target.checked);
            }
        });

        // Reset columns button
        const resetButton =
            this.controlsContainer.querySelector('.reset-columns');
        if (resetButton) {
            resetButton.addEventListener('click', () =>
                this.resetColumnLayout()
            );
        }
    }

    /**
     * Toggle column visibility
     * @param {string} columnName - Name of the column
     * @param {boolean} show - Whether to show the column
     */
    toggleColumn(columnName, show) {
        const headers = this.table.querySelectorAll('thead th');
        const rows = this.table.querySelectorAll('tbody tr');

        headers.forEach((th, index) => {
            const thisColumnName = this.getColumnName(th, index);
            if (thisColumnName === columnName) {
                th.style.display = show ? '' : 'none';
                rows.forEach((row) => {
                    const cell = row.children[index];
                    if (cell) {
                        cell.style.display = show ? '' : 'none';
                    }
                });
            }
        });

        // Update preferences
        if (show) {
            this.preferences.hiddenColumns =
                this.preferences.hiddenColumns.filter(
                    (col) => col !== columnName
                );
        } else {
            if (!this.preferences.hiddenColumns.includes(columnName)) {
                this.preferences.hiddenColumns.push(columnName);
            }
        }

        this.saveUserPreferences();
    }

    /**
     * Reset column layout to defaults
     */
    async resetColumnLayout() {
        // Reset preferences
        this.preferences.hiddenColumns = [];
        this.preferences.columnWidths = {};

        // Show all columns and reset widths
        const headers = this.table.querySelectorAll('thead th');
        const rows = this.table.querySelectorAll('tbody tr');

        headers.forEach((th, index) => {
            th.style.display = '';
            th.style.width = '';
            th.style.minWidth = '';
            th.style.maxWidth = '';

            rows.forEach((row) => {
                const cell = row.children[index];
                if (cell) {
                    cell.style.display = '';
                }
            });
        });

        // Update checkboxes
        const checkboxes =
            this.controlsContainer.querySelectorAll('.column-toggle');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = true;
        });

        // Save preferences
        await this.saveUserPreferences();

        // Show success message
        try {
            const message = await getString(
                'columnlayoutreset',
                'local_equipment'
            );
            Notification.addNotification({
                message: message,
                type: 'success',
            });
        } catch (e) {
            Notification.addNotification({
                message: 'Column layout has been reset',
                type: 'success',
            });
        }
    }
}

export default VccTableColumns;

/**
 * Initialize VCC table column management
 * @param {string} tableSelector - CSS selector for the table
 * @returns {Promise<VccTableColumns|null>} VccTableColumns instance or null if table not found
 */
export const init = async (
    tableSelector = '#region-main table.generaltable'
) => {
    try {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            await new Promise((resolve) => {
                document.addEventListener('DOMContentLoaded', resolve, {
                    once: true,
                });
            });
        }

        // Check if table exists
        const table = document.querySelector(tableSelector);
        if (!table) {
            Log.debug(
                'VCC table not found for column management: ' + tableSelector
            );
            return null;
        }

        // Initialize column management
        const columnManager = new VccTableColumns(tableSelector);
        Log.debug('VCC table column management initialized successfully');

        return columnManager;
    } catch (error) {
        Log.debug(
            'Failed to initialize VCC table column management: ' + error.message
        );
        console.error('VCC Table Columns initialization error:', error);
        return null;
    }
};
