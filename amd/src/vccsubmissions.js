// This file is part of FLIP Plugins for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Enhanced VCC Submissions table functionality with AJAX pagination and state management
 *
 * @module     local_equipment/vccsubmissions
 * @copyright  2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str_manager';
import Templates from 'core/templates';

// Debug flags for VCC submissions operations - set to true to enable debug logging
const DEBUG_AJAX_PAGINATION = false;
const DEBUG_TABLE_INTERACTIONS = false;
const DEBUG_FILTER_OPERATIONS = false;
const DEBUG_TEMPLATE_RENDERING = false;

/**
 * VCC Submissions table enhanced functionality
 */
class VCCSubmissions {

    /**
     * Constructor
     */
    constructor() {
        this.tableContainer = null;
        this.currentPage = 1;
        this.pageSize = 25;
        this.totalRecords = 0;
        this.totalPages = 0;
        this.currentFilters = {};
        this.isLoading = false;
        this.sesskey = M.cfg.sesskey;

        // Browser compatibility flags
        this.browserSupport = {
            intersectionObserver: 'IntersectionObserver' in window,
            resizeObserver: 'ResizeObserver' in window,
            fetch: 'fetch' in window,
            promise: 'Promise' in window
        };

        this.init();
    }

    /**
     * Initialize the VCC submissions functionality
     */
    init() {
        this.checkBrowserCompatibility();
        this.initializeElements();
        this.bindEvents();
        this.loadUserPreferences();
        this.initializeState();
    }

    /**
     * Check browser compatibility and show warnings if needed
     */
    checkBrowserCompatibility() {
        const requiredFeatures = ['fetch', 'promise'];
        const missingFeatures = requiredFeatures.filter(feature => !this.browserSupport[feature]);

        if (missingFeatures.length > 0) {
            this.showNotification(
                'Browser compatibility warning: Some features may not work properly.',
                'warning'
            );
            console.warn('Missing browser features:', missingFeatures);
        }

        // Graceful degradation for ResizeObserver
        if (!this.browserSupport.resizeObserver) {
            console.info('ResizeObserver not supported - column resizing will use fallback method');
        }
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        this.tableContainer = document.querySelector('.vcc-submissions-table-container');
        this.paginationContainer = document.querySelector('.vcc-pagination-container');
        this.loadingIndicator = document.querySelector('.vcc-loading-indicator');
        this.filterForm = document.querySelector('.vcc-filter-form');

        if (!this.tableContainer) {
            console.warn('VCC submissions table container not found');
            return;
        }

        // Create loading indicator if it doesn't exist
        if (!this.loadingIndicator) {
            this.createLoadingIndicator();
        }
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // Pagination events
        document.addEventListener('click', (e) => {
            if (e.target.matches('.vcc-pagination-link')) {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page, 10);
                if (page && page !== this.currentPage) {
                    this.loadPage(page);
                }
            }

            if (e.target.matches('.vcc-page-size-selector')) {
                e.preventDefault();
                const newSize = parseInt(e.target.value, 10);
                if (newSize && newSize !== this.pageSize) {
                    this.changePageSize(newSize);
                }
            }
        });

        // Filter form submission
        if (this.filterForm) {
            this.filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }

        // Browser history navigation
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        });

        // Auto-save table state on beforeunload
        window.addEventListener('beforeunload', () => {
            this.saveTableState();
        });
    }

    /**
     * Load user preferences from Moodle user preferences API
     */
    async loadUserPreferences() {
        try {
            const preferences = await this.getUserPreferences();

            if (preferences.pageSize) {
                this.pageSize = parseInt(preferences.pageSize, 10);
            }

            if (preferences.currentPage) {
                this.currentPage = parseInt(preferences.currentPage, 10);
            }

            if (preferences.filters) {
                this.currentFilters = JSON.parse(preferences.filters);
            }

        } catch (error) {
            console.warn('Could not load user preferences:', error);
        }
    }

    /**
     * Initialize table state from URL parameters
     */
    initializeState() {
        const urlParams = new URLSearchParams(window.location.search);

        // Override with URL parameters if present
        if (urlParams.has('page')) {
            this.currentPage = parseInt(urlParams.get('page'), 10) || 1;
        }

        if (urlParams.has('perpage')) {
            this.pageSize = parseInt(urlParams.get('perpage'), 10) || 25;
        }

        // Load filters from URL
        ['search', 'partnership', 'datestart', 'dateend'].forEach(param => {
            if (urlParams.has(param)) {
                this.currentFilters[param] = urlParams.get(param);
            }
        });
    }

    /**
     * Load a specific page of data
     * @param {number} page - Page number to load
     * @param {boolean} updateHistory - Whether to update browser history
     */
    async loadPage(page = 1, updateHistory = true) {
        if (this.isLoading) {
            return;
        }

        this.isLoading = true;
        this.currentPage = page;
        this.showLoading(true);

        try {
            const data = await this.fetchTableData();
            await this.renderTableData(data);

            if (updateHistory) {
                this.updateURL();
                this.updateBrowserHistory();
            }

            this.saveUserPreferences();

        } catch (error) {
            console.error('Error loading page:', error);
            this.showErrorState(error.message);
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }

    /**
     * Change page size and reload first page
     * @param {number} newSize - New page size
     */
    async changePageSize(newSize) {
        this.pageSize = newSize;
        this.currentPage = 1; // Reset to first page
        await this.loadPage(1);
    }

    /**
     * Apply current filters and reload data
     */
    async applyFilters() {
        if (this.filterForm) {
            const formData = new FormData(this.filterForm);
            this.currentFilters = {};

            for (const [key, value] of formData.entries()) {
                if (value && value.trim()) {
                    this.currentFilters[key] = value.trim();
                }
            }
        }

        this.currentPage = 1; // Reset to first page when applying filters
        await this.loadPage(1);
    }

    /**
     * Fetch table data from server via AJAX
     * @returns {Promise<Object>} Table data response
     */
    async fetchTableData() {
        const request = {
            methodname: 'local_equipment_get_table_data',
            args: {
                page: this.currentPage,
                perpage: this.pageSize,
                filters: this.currentFilters,
                sesskey: this.sesskey
            }
        };

        try {
            const response = await Ajax.call([request])[0];

            if (!response || response.error) {
                throw new Error(response?.error || 'Unknown server error');
            }

            this.totalRecords = response.totalrecords || 0;
            this.totalPages = Math.ceil(this.totalRecords / this.pageSize);

            return response;

        } catch (error) {
            console.error('AJAX Error:', error);
            throw new Error('Failed to load table data: ' + error.message);
        }
    }

    /**
     * Render table data using Mustache templates
     * @param {Object} data - Table data from server
     */
    async renderTableData(data) {
        try {
            // Render main table content
            if (data.tablehtml) {
                // Direct HTML from server (fallback)
                this.tableContainer.innerHTML = data.tablehtml;
            } else if (data.rows) {
                // Use Mustache template for proper theme override support
                const tableHtml = await Templates.render('local_equipment/vcc_table_content', {
                    rows: data.rows,
                    columns: data.columns || []
                });
                this.tableContainer.innerHTML = tableHtml;
            }

            // Render pagination
            if (this.paginationContainer && this.totalPages > 1) {
                const paginationData = this.buildPaginationData();
                const paginationHtml = await Templates.render('local_equipment/vcc_pagination_ajax', paginationData);
                this.paginationContainer.innerHTML = paginationHtml;
            } else if (this.paginationContainer) {
                this.paginationContainer.innerHTML = '';
            }

            // Update page information
            this.updatePageInfo();

        } catch (error) {
            console.error('Error rendering table data:', error);
            throw new Error('Failed to render table data');
        }
    }

    /**
     * Build pagination data for template
     * @returns {Object} Pagination data
     */
    buildPaginationData() {
        const pages = [];
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(this.totalPages, this.currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            pages.push({
                number: i,
                current: i === this.currentPage,
                url: this.buildPageURL(i)
            });
        }

        return {
            currentpage: this.currentPage,
            totalpages: this.totalPages,
            totalrecords: this.totalRecords,
            pagesize: this.pageSize,
            pages: pages,
            hasprevious: this.currentPage > 1,
            hasnext: this.currentPage < this.totalPages,
            previouspage: this.currentPage - 1,
            nextpage: this.currentPage + 1,
            previousurl: this.buildPageURL(this.currentPage - 1),
            nexturl: this.buildPageURL(this.currentPage + 1)
        };
    }

    /**
     * Build URL for a specific page
     * @param {number} page - Page number
     * @returns {string} URL for the page
     */
    buildPageURL(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        url.searchParams.set('perpage', this.pageSize);

        // Add current filters to URL
        Object.keys(this.currentFilters).forEach(key => {
            if (this.currentFilters[key]) {
                url.searchParams.set(key, this.currentFilters[key]);
            }
        });

        return url.toString();
    }

    /**
     * Update browser URL without reloading page
     */
    updateURL() {
        const newUrl = this.buildPageURL(this.currentPage);
        window.history.replaceState({page: this.currentPage}, '', newUrl);
    }

    /**
     * Update browser history for back/forward navigation
     */
    updateBrowserHistory() {
        const state = {
            page: this.currentPage,
            pageSize: this.pageSize,
            filters: this.currentFilters
        };

        window.history.pushState(state, '', this.buildPageURL(this.currentPage));
    }

    /**
     * Create loading indicator element
     */
    createLoadingIndicator() {
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'vcc-loading-indicator';
        this.loadingIndicator.style.display = 'none';

        if (this.tableContainer) {
            this.tableContainer.parentNode.insertBefore(this.loadingIndicator, this.tableContainer);
        }
    }

    /**
     * Show/hide loading state
     * @param {boolean} show - Whether to show loading state
     */
    async showLoading(show) {
        if (show) {
            try {
                const loadingHtml = await Templates.render('local_equipment/vcc_loading_state', {});
                if (this.loadingIndicator) {
                    this.loadingIndicator.innerHTML = loadingHtml;
                    this.loadingIndicator.style.display = 'block';
                }

                if (this.tableContainer) {
                    this.tableContainer.style.opacity = '0.6';
                    this.tableContainer.style.pointerEvents = 'none';
                }
            } catch (error) {
                console.warn('Could not render loading template:', error);
                // Fallback to simple loading indicator
                if (this.loadingIndicator) {
                    this.loadingIndicator.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
                    this.loadingIndicator.style.display = 'block';
                }
            }
        } else {
            if (this.loadingIndicator) {
                this.loadingIndicator.style.display = 'none';
            }

            if (this.tableContainer) {
                this.tableContainer.style.opacity = '1';
                this.tableContainer.style.pointerEvents = 'auto';
            }
        }
    }

    /**
     * Show error state
     * @param {string} message - Error message
     */
    async showErrorState(message) {
        try {
            const errorHtml = await Templates.render('local_equipment/vcc_error_state', {
                error: message
            });

            if (this.tableContainer) {
                this.tableContainer.innerHTML = errorHtml;
            }
        } catch (error) {
            console.warn('Could not render error template:', error);
            // Fallback to simple error display
            if (this.tableContainer) {
                this.tableContainer.innerHTML = `<div class="alert alert-danger">${message}</div>`;
            }
        }
    }

    /**
     * Update page information display
     */
    updatePageInfo() {
        const pageInfo = document.querySelector('.vcc-page-info');
        if (pageInfo) {
            const start = ((this.currentPage - 1) * this.pageSize) + 1;
            const end = Math.min(this.currentPage * this.pageSize, this.totalRecords);

            pageInfo.textContent = `Showing ${start}-${end} of ${this.totalRecords} submissions`;
        }
    }

    /**
     * Save current table state
     */
    saveTableState() {
        const state = {
            page: this.currentPage,
            pageSize: this.pageSize,
            filters: this.currentFilters,
            timestamp: Date.now()
        };

        try {
            sessionStorage.setItem('vcc_submissions_state', JSON.stringify(state));
        } catch (error) {
            console.warn('Could not save table state:', error);
        }
    }

    /**
     * Get user preferences from Moodle
     * @returns {Promise<Object>} User preferences
     */
    async getUserPreferences() {
        // This would typically call a Moodle web service
        // For now, return default preferences
        return {
            pageSize: 25,
            currentPage: 1,
            filters: '{}'
        };
    }

    /**
     * Save user preferences to Moodle
     */
    async saveUserPreferences() {
        try {
            const request = {
                methodname: 'local_equipment_save_table_preferences',
                args: {
                    preferences: {
                        pageSize: this.pageSize,
                        currentPage: this.currentPage,
                        filters: JSON.stringify(this.currentFilters)
                    },
                    sesskey: this.sesskey
                }
            };

            await Ajax.call([request])[0];
        } catch (error) {
            console.warn('Could not save user preferences:', error);
        }
    }

    /**
     * Show notification to user
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     */
    showNotification(message, type = 'info') {
        Notification.addNotification({
            message: message,
            type: type
        });
    }
}

/**
 * Initialize VCC Submissions functionality
 * @returns {VCCSubmissions} VCC Submissions instance
 */
export const init = () => {
    return new VCCSubmissions();
};