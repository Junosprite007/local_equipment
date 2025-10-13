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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Enhanced VCC table functionality with Bootstrap 5 components
 *
 * @module     local_equipment/vcc_table_enhanced
 * @copyright  2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * Initialize enhanced table functionality
     * @param {string} tableSelector - CSS selector for the table
     */
    const init = (tableSelector) => {
        const $table = $(tableSelector);
        
        if (!$table.length) {
            return;
        }

        // Initialize pagesize selector
        initPagesizeSelector();
        
        // Initialize enhanced status indicators
        initStatusIndicators($table);
        
        // Initialize loading states
        initLoadingStates($table);
        
        // Initialize enhanced hover effects
        initHoverEffects($table);
    };

    /**
     * Initialize pagesize selector functionality
     */
    const initPagesizeSelector = () => {
        const $selector = $('#pagesize-selector');
        
        if (!$selector.length) {
            return;
        }

        $selector.on('change', function() {
            const baseUrl = $(this).data('base-url');
            const pagesize = $(this).val();
            
            // Show loading indicator
            showLoadingOverlay();
            
            // Navigate to new URL with updated pagesize
            window.location.href = baseUrl + '&pagesize=' + pagesize;
        });
    };

    /**
     * Initialize enhanced status indicators
     * @param {jQuery} $table - Table element
     */
    const initStatusIndicators = ($table) => {
        // Add pulse animation to verified status indicators
        $table.find('.status-indicator.status-verified').each(function() {
            const $indicator = $(this);
            
            // Add enhanced tooltip functionality
            if ($indicator.attr('title')) {
                $indicator.tooltip({
                    placement: 'top',
                    trigger: 'hover focus'
                });
            }
        });

        // Add warning animation to unverified status
        $table.find('.status-indicator.status-unverified, .status-indicator.status-error').each(function() {
            const $indicator = $(this);
            
            // Add enhanced tooltip functionality
            if ($indicator.attr('title')) {
                $indicator.tooltip({
                    placement: 'top',
                    trigger: 'hover focus',
                    customClass: 'tooltip-warning'
                });
            }
        });

        // Enhance badge styling
        $table.find('.badge').each(function() {
            const $badge = $(this);
            
            // Add hover effect for interactive badges
            if ($badge.closest('a').length || $badge.attr('data-toggle')) {
                $badge.addClass('badge-interactive');
            }
        });
    };

    /**
     * Initialize loading states
     * @param {jQuery} $table - Table element
     */
    const initLoadingStates = ($table) => {
        // Show skeleton loading for initial load if table is empty
        if ($table.find('tbody tr').length === 0) {
            showSkeletonLoading($table);
        }

        // Add loading state for table actions
        $table.find('.btn[data-action]').on('click', function() {
            const $btn = $(this);
            const originalText = $btn.text();
            
            // Show loading state
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + originalText);
        });
    };

    /**
     * Initialize enhanced hover effects
     * @param {jQuery} $table - Table element
     */
    const initHoverEffects = ($table) => {
        // Enhanced row hover effects
        $table.find('tbody tr').hover(
            function() {
                $(this).addClass('table-row-highlight');
            },
            function() {
                $(this).removeClass('table-row-highlight');
            }
        );

        // Column header hover effects for sortable columns
        $table.find('thead th.sortable').hover(
            function() {
                $(this).addClass('table-header-hover');
            },
            function() {
                $(this).removeClass('table-header-hover');
            }
        );
    };

    /**
     * Show loading overlay
     */
    const showLoadingOverlay = () => {
        const $overlay = $('<div class="vcc-table-loading">' +
            '<div class="loading-spinner">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '<div class="loading-text">Loading...</div>' +
            '</div>' +
            '</div>');
        
        $('body').append($overlay);
    };

    /**
     * Show skeleton loading in table
     * @param {jQuery} $table - Table element
     */
    const showSkeletonLoading = ($table) => {
        const $tbody = $table.find('tbody');
        const columnCount = $table.find('thead th').length;
        
        // Create skeleton rows
        for (let i = 0; i < 5; i++) {
            const $row = $('<tr class="skeleton-row">');
            
            for (let j = 0; j < columnCount; j++) {
                $row.append('<td><div class="skeleton-content">Loading...</div></td>');
            }
            
            $tbody.append($row);
        }
    };

    /**
     * Show empty state
     * @param {jQuery} $container - Container element
     * @param {string} message - Empty state message
     */
    const showEmptyState = ($container, message = 'No data available') => {
        const $emptyState = $('<div class="vcc-table-empty">' +
            '<div class="empty-icon">' +
            '<i class="fa fa-inbox" aria-hidden="true"></i>' +
            '</div>' +
            '<div class="empty-title">No Results Found</div>' +
            '<div class="empty-description">' + message + '</div>' +
            '</div>');
        
        $container.append($emptyState);
    };

    /**
     * Show error state
     * @param {jQuery} $container - Container element
     * @param {string} message - Error message
     */
    const showErrorState = ($container, message = 'An error occurred') => {
        const $errorState = $('<div class="vcc-table-error">' +
            '<div class="error-icon">' +
            '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>' +
            '</div>' +
            '<div class="error-title">Error Loading Data</div>' +
            '<div class="error-message">' + message + '</div>' +
            '<div class="error-actions">' +
            '<button type="button" class="btn btn-primary" onclick="location.reload()">Retry</button>' +
            '</div>' +
            '</div>');
        
        $container.append($errorState);
    };

    return {
        init: init,
        showLoadingOverlay: showLoadingOverlay,
        showEmptyState: showEmptyState,
        showErrorState: showErrorState
    };
});