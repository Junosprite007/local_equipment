/**
 * QR Code Print Queue Notification module.
 *
 * This module creates and manages a floating notification showing the current
 * number of items in the QR code print queue.
 *
 * @module     local_equipment/queue-notification
 * @copyright  2025 Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No imports needed - using vanilla JS

/**
 * Initialize the queue notification.
 */
export const init = () => {
    let notificationElement = null;
    let updateInterval = null;

    /**
     * Create the floating notification element.
     * @returns {HTMLElement} The notification element
     */
    const createNotificationElement = () => {
        const notification = document.createElement('div');
        notification.id = 'qr-queue-notification';
        notification.className = 'qr-queue-notification';
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: #007bff;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            z-index: 1050;
            display: none;
            transition: all 0.3s ease;
            user-select: none;
        `;

        // Add hover effects
        notification.addEventListener('mouseenter', () => {
            notification.style.transform = 'translateY(-2px)';
            notification.style.boxShadow = '0 6px 16px rgba(0, 0, 0, 0.2)';
        });

        notification.addEventListener('mouseleave', () => {
            notification.style.transform = 'translateY(0)';
            notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        });

        // Click handler to navigate to print queue
        notification.addEventListener('click', () => {
            const url = new URL(
                M.cfg.wwwroot + '/local/equipment/inventory/generate_qr.php'
            );
            url.searchParams.set('action', 'print_queue');
            window.open(url.toString(), '_blank');
        });

        document.body.appendChild(notification);
        return notification;
    };

    /**
     * Update the notification with current queue count.
     * @param {number} count The current queue count
     */
    const updateNotification = (count) => {
        if (!notificationElement) {
            notificationElement = createNotificationElement();
        }

        if (count > 0) {
            const icon = '🖨️';
            const text =
                count === 1 ? '1 QR code queued' : `${count} QR codes queued`;
            notificationElement.innerHTML = `${icon} ${text}`;
            notificationElement.style.display = 'block';

            // Add a subtle pulse animation for new items
            notificationElement.style.animation = 'none';
            setTimeout(() => {
                notificationElement.style.animation = 'pulse 1s ease-in-out';
            }, 10);
        } else {
            notificationElement.style.display = 'none';
        }
    };

    /**
     * Fetch the current queue count from the server.
     */
    const fetchQueueCount = () => {
        fetch(
            M.cfg.wwwroot +
                '/local/equipment/classes/external/get_queue_count.php',
            {
                method: 'GET',
                credentials: 'same-origin',
            }
        )
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    updateNotification(data.count);
                }
                // Silently fail for queue count errors - non-critical feature
            })
            .catch(() => {
                // Silently fail for network errors - non-critical feature
            });
    };

    /**
     * Start monitoring the queue count.
     */
    const startMonitoring = () => {
        // Fetch immediately
        fetchQueueCount();

        // Set up periodic updates every 30 seconds
        updateInterval = setInterval(fetchQueueCount, 30000);
    };

    /**
     * Stop monitoring the queue count.
     */
    const stopMonitoring = () => {
        if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = null;
        }
    };

    /**
     * Refresh the queue count immediately.
     */
    const refresh = () => {
        fetchQueueCount();
    };

    // Add CSS animation for pulse effect
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .qr-queue-notification:hover {
            background: #0056b3 !important;
        }

        @media (max-width: 768px) {
            .qr-queue-notification {
                bottom: 10px !important;
                left: 10px !important;
                padding: 10px 12px !important;
                font-size: 13px !important;
            }
        }
    `;
    document.head.appendChild(style);

    // Start monitoring when the page loads
    startMonitoring();

    // Clean up when page unloads
    window.addEventListener('beforeunload', stopMonitoring);

    // Expose methods for external use
    window.QRQueueNotification = {
        refresh: refresh,
        updateCount: updateNotification,
    };

    return {
        refresh: refresh,
        updateCount: updateNotification,
        stop: stopMonitoring,
    };
};
