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
 * Debug utilities for local_equipment plugin
 *
 * @module     local_equipment/debug-utils
 * @copyright  2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable no-console */

import Log from 'core/log';

/**
 * Check if we're in developer debug mode
 * @returns {boolean} True if in developer debug mode
 */
const isDeveloperMode = () => {
    return (
        window.M?.cfg?.developerdebug ||
        document.body.classList.contains('debug') ||
        localStorage.getItem('moodle_debug') === 'true'
    );
};

/**
 * Enhanced debugging function for AJAX responses
 * @param {string} context Context description for the debug session
 * @param {Object} data Response data to debug
 * @param {Object} extra Additional context data to include
 * @param {string} level Debug level: 'basic', 'verbose', or 'full'
 */
export const debugAjaxResponse = (
    context,
    data,
    extra = {},
    level = 'full'
) => {
    const timestamp = new Date().toISOString();

    console.group(`🐛 AJAX Debug: ${context} - ${timestamp}`);

    // Summary table
    const summary = {
        'Response Type': typeof data,
        'Is Object': typeof data === 'object' && data !== null,
        'Keys Count': data ? Object.keys(data).length : 0,
        'Has Success': data && 'success' in data,
        'Success Value': data ? data.success : 'N/A',
        'Success Type': data ? typeof data.success : 'N/A',
        'Has Message': data && 'message' in data,
        'Has Error': data && 'error' in data,
        'Has Exception': data && 'exception' in data,
        ...extra,
    };

    console.table(summary);

    if (level === 'verbose' || level === 'full') {
        // Response data breakdown
        if (data && typeof data === 'object') {
            console.group('📄 Response Data');
            console.table(data);
            console.groupEnd();

            // Property types analysis
            console.group('🏷️ Property Types');
            const types = {};
            Object.keys(data).forEach((key) => {
                types[key] = {
                    type: typeof data[key],
                    hasValue: data[key] !== null && data[key] !== undefined,
                    length:
                        data[key] && data[key].length
                            ? data[key].length
                            : 'N/A',
                };
            });
            console.table(types);
            console.groupEnd();
        }
    }

    if (level === 'full') {
        // Raw JSON for copying
        console.group('📋 Copy-able JSON');
        console.log(JSON.stringify(data, null, 2));
        console.groupEnd();

        // Stack trace if available
        if (Error.captureStackTrace || new Error().stack) {
            console.group('📍 Stack Trace');
            console.trace('Debug point location');
            console.groupEnd();
        }
    }

    console.groupEnd();

    // Also log to Moodle's Log system for persistence
    Log.debug(`AJAX Debug - ${context}`, {
        summary: summary,
        data: data,
        extra: extra,
    });
};

/**
 * Debug object properties in clean table format
 * @param {string} title Debug section title
 * @param {Object} obj Object to debug
 * @param {Array} columns Specific columns to show (optional)
 */
export const debugObjectTable = (title, obj, columns = null) => {
    console.group(`📊 ${title}`);

    if (obj && typeof obj === 'object') {
        if (columns && Array.isArray(columns)) {
            console.table(obj, columns);
        } else {
            console.table(obj);
        }
    } else {
        console.log('Object is not debuggable:', typeof obj, obj);
    }

    console.groupEnd();
};

/**
 * Create a grouped console log with expandable sections
 * @param {string} title Main group title
 * @param {Object} sections Object containing section titles as keys and content as values
 * @param {boolean} collapsed Whether groups should start collapsed
 */
export const debugGroup = (title, sections, collapsed = false) => {
    const groupMethod = collapsed ? console.groupCollapsed : console.group;

    groupMethod(`🔍 ${title}`);

    Object.keys(sections).forEach((sectionTitle) => {
        const content = sections[sectionTitle];

        console.group(sectionTitle);

        if (typeof content === 'object' && content !== null) {
            console.table(content);
        } else {
            console.log(content);
        }

        console.groupEnd();
    });

    console.groupEnd();
};

/**
 * Enhanced error logging with context
 * @param {string} errorType Type/category of error
 * @param {string} message Error message
 * @param {Object} context Additional context data
 * @param {Error} originalError Original error object (optional)
 */
export const debugError = (
    errorType,
    message,
    context = {},
    originalError = null
) => {
    console.group(`❌ Error: ${errorType}`);

    // Error summary
    console.table({
        'Error Type': errorType,
        Message: message,
        Timestamp: new Date().toISOString(),
        'User Agent': navigator.userAgent.substring(0, 50) + '...',
        URL: window.location.href,
        'Has Context': Object.keys(context).length > 0,
        'Has Original Error': originalError !== null,
    });

    // Context data
    if (Object.keys(context).length > 0) {
        console.group('🔍 Context Data');
        console.table(context);
        console.groupEnd();
    }

    // Original error details
    if (originalError) {
        console.group('⚠️ Original Error');
        console.error(originalError);
        if (originalError.stack) {
            console.log('Stack:', originalError.stack);
        }
        console.groupEnd();
    }

    console.groupEnd();

    // Also log to Moodle's system
    Log.error(`${errorType}: ${message}`, {
        context: context,
        originalError: originalError
            ? {
                  name: originalError.name,
                  message: originalError.message,
                  stack: originalError.stack,
              }
            : null,
    });
};

/**
 * Debug network request/response cycle
 * @param {string} endpoint API endpoint or URL
 * @param {Object} requestData Request data sent
 * @param {Object} responseData Response data received
 * @param {number} duration Request duration in milliseconds
 * @param {boolean} success Whether the request was successful
 */
export const debugNetworkRequest = (
    endpoint,
    requestData,
    responseData,
    duration,
    success
) => {
    const icon = success ? '✅' : '❌';
    const status = success ? 'SUCCESS' : 'FAILED';

    console.group(`${icon} Network Request: ${endpoint} - ${status}`);

    // Request summary
    console.table({
        Endpoint: endpoint,
        Method: 'POST', // Most Moodle AJAX calls are POST
        'Duration (ms)': duration,
        Success: success,
        'Request Size': JSON.stringify(requestData).length + ' bytes',
        'Response Size': JSON.stringify(responseData).length + ' bytes',
        Timestamp: new Date().toISOString(),
    });

    // Request data
    console.group('📤 Request Data');
    console.table(requestData);
    console.groupEnd();

    // Response data
    console.group('📥 Response Data');
    console.table(responseData);
    console.groupEnd();

    console.groupEnd();
};

/**
 * Performance timing debug helper
 * @param {string} operation Operation name
 * @param {Function} callback Function to time
 * @returns {Promise|any} Result of the callback
 */
export const debugTiming = async (operation, callback) => {
    const startTime = performance.now();

    console.time(`⏱️ ${operation}`);

    try {
        const result = await callback();
        const endTime = performance.now();
        const duration = endTime - startTime;

        console.timeEnd(`⏱️ ${operation}`);

        console.log(`✅ ${operation} completed in ${duration.toFixed(2)}ms`);

        return result;
    } catch (error) {
        const endTime = performance.now();
        const duration = endTime - startTime;

        console.timeEnd(`⏱️ ${operation}`);
        console.error(
            `❌ ${operation} failed after ${duration.toFixed(2)}ms:`,
            error
        );

        throw error;
    }
};

/**
 * Debug browser and environment information
 */
export const debugEnvironment = () => {
    console.group('🌐 Environment Debug Information');

    // Browser info
    console.table({
        'User Agent': navigator.userAgent,
        Platform: navigator.platform,
        Language: navigator.language,
        Online: navigator.onLine,
        'Cookies Enabled': navigator.cookieEnabled,
        'Do Not Track': navigator.doNotTrack,
        'Screen Resolution': `${screen.width}x${screen.height}`,
        'Color Depth': screen.colorDepth,
        'Timezone Offset': new Date().getTimezoneOffset(),
    });

    // Moodle-specific info
    if (window.M) {
        console.group('🎓 Moodle Environment');
        console.table({
            'Moodle Version': window.M.cfg.version || 'Unknown',
            'WWW Root': window.M.cfg.wwwroot,
            Theme: window.M.cfg.theme,
            'Developer Debug': window.M.cfg.developerdebug,
            'Session Name': window.M.cfg.sesskey ? 'Present' : 'Missing',
            'User ID': window.M.cfg.userid || 'Not logged in',
        });
        console.groupEnd();
    }

    // Performance info
    if (window.performance) {
        console.group('⚡ Performance Info');
        const navigation = performance.getEntriesByType('navigation')[0];
        if (navigation) {
            console.table({
                'DOM Content Loaded': `${navigation.domContentLoadedEventEnd.toFixed(
                    2
                )}ms`,
                'Load Complete': `${navigation.loadEventEnd.toFixed(2)}ms`,
                'DNS Lookup': `${(
                    navigation.domainLookupEnd - navigation.domainLookupStart
                ).toFixed(2)}ms`,
                'Connection Time': `${(
                    navigation.connectEnd - navigation.connectStart
                ).toFixed(2)}ms`,
                'Response Time': `${(
                    navigation.responseEnd - navigation.responseStart
                ).toFixed(2)}ms`,
            });
        }
        console.groupEnd();
    }

    console.groupEnd();
};

/**
 * Conditional debug logging based on environment
 */
export const conditionalDebug = {
    /**
     * Log only in development mode
     * @param {...any} args Arguments to log
     */
    dev: (...args) => {
        if (isDeveloperMode()) {
            console.log('🛠️ [DEV]', ...args);
        }
    },

    /**
     * Log only in production mode
     * @param {...any} args Arguments to log
     */
    prod: (...args) => {
        if (!isDeveloperMode()) {
            console.log('🏭 [PROD]', ...args);
        }
    },

    /**
     * Always log, but with environment indicator
     * @param {...any} args Arguments to log
     */
    always: (...args) => {
        const env = isDeveloperMode() ? 'DEV' : 'PROD';
        console.log(`🌍 [${env}]`, ...args);
    },
};
