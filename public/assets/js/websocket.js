/**
 * WebSocket Client Module
 * 
 * Handles WebSocket connections for real-time notifications and live updates
 * Supports auto-reconnection, channel subscriptions, and message handling
 */

class WebSocketModule {
    
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 5000;
        this.channels = [];
        this.messageQueue = [];
        this.isConnected = false;
        this.config = {
            host: window.location.hostname,
            port: 8080,
            protocol: 'ws',
            autoReconnect: true
        };
        
        this.init();
    }
    
    /**
     * Initialize WebSocket connection
     */
    init() {
        this.connect();
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.disconnect();
            } else {
                this.connect();
            }
        });
        
        // Handle page unload
        window.addEventListener('beforeunload', () => {
            this.disconnect();
        });
    }
    
    /**
     * Connect to WebSocket server
     */
    connect() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            return;
        }
        
        try {
            const wsUrl = `${this.config.protocol}://${this.config.host}:${this.config.port}`;
            console.log('Connecting to WebSocket:', wsUrl);
            
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = (event) => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                
                // Process queued messages
                this.processMessageQueue();
                
                // Send authentication
                this.authenticate();
                
                // Show connection status
                this.showConnectionStatus('connected', 'Connected to WebSocket server');
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const message = JSON.parse(event.data);
                    this.handleMessage(message);
                } catch (error) {
                    console.error('WebSocket message parse error:', error);
                }
            };
            
            this.ws.onclose = (event) => {
                console.log('WebSocket disconnected:', event.code, event.reason);
                this.isConnected = false;
                this.ws = null;
                
                // Show connection status
                this.showConnectionStatus('disconnected', 'Disconnected from WebSocket server');
                
                // Auto-reconnect if enabled
                if (this.config.autoReconnect && this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.scheduleReconnect();
                }
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showConnectionStatus('error', 'WebSocket connection error');
            };
            
        } catch (error) {
            console.error('WebSocket connection error:', error);
            this.showConnectionStatus('error', 'Failed to connect to WebSocket server');
        }
    }
    
    /**
     * Disconnect from WebSocket server
     */
    disconnect() {
        if (this.ws) {
            this.ws.close(1000, 'Client disconnecting');
            this.ws = null;
        }
        this.isConnected = false;
    }
    
    /**
     * Schedule reconnection
     */
    scheduleReconnect() {
        this.reconnectAttempts++;
        const delay = this.reconnectInterval * Math.pow(2, this.reconnectAttempts - 1);
        
        console.log(`Scheduling reconnect attempt ${this.reconnectAttempts} in ${delay}ms`);
        this.showConnectionStatus('reconnecting', `Reconnecting... (attempt ${this.reconnectAttempts})`);
        
        setTimeout(() => {
            this.connect();
        }, delay);
    }
    
    /**
     * Authenticate with WebSocket server
     */
    authenticate() {
        const token = this.getAuthToken();
        
        if (token) {
            this.send({
                type: 'auth',
                token: token
            });
        } else {
            console.warn('No authentication token available');
        }
    }
    
    /**
     * Get authentication token
     */
    getAuthToken() {
        // Try to get CSRF token from meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Try to get from session storage
        return sessionStorage.getItem('csrf_token') || localStorage.getItem('csrf_token');
    }
    
    /**
     * Send message to WebSocket server
     */
    send(message) {
        if (!this.isConnected || !this.ws) {
            console.warn('WebSocket not connected, queuing message');
            this.messageQueue.push(message);
            return false;
        }
        
        try {
            this.ws.send(JSON.stringify(message));
            return true;
        } catch (error) {
            console.error('WebSocket send error:', error);
            return false;
        }
    }
    
    /**
     * Process queued messages
     */
    processMessageQueue() {
        while (this.messageQueue.length > 0) {
            const message = this.messageQueue.shift();
            this.send(message);
        }
    }
    
    /**
     * Subscribe to channel
     */
    subscribe(channel) {
        if (!this.channels.includes(channel)) {
            this.channels.push(channel);
            
            if (this.isConnected) {
                this.send({
                    type: 'subscribe',
                    channel: channel
                });
            }
        }
    }
    
    /**
     * Unsubscribe from channel
     */
    unsubscribe(channel) {
        const index = this.channels.indexOf(channel);
        if (index > -1) {
            this.channels.splice(index, 1);
            
            if (this.isConnected) {
                this.send({
                    type: 'unsubscribe',
                    channel: channel
                });
            }
        }
    }
    
    /**
     * Handle incoming WebSocket messages
     */
    handleMessage(message) {
        console.log('WebSocket message received:', message);
        
        switch (message.type) {
            case 'welcome':
                this.handleWelcome(message);
                break;
                
            case 'auth_required':
                this.handleAuthRequired(message);
                break;
                
            case 'auth_success':
                this.handleAuthSuccess(message);
                break;
                
            case 'auth_error':
                this.handleAuthError(message);
                break;
                
            case 'subscribed':
                this.handleSubscribed(message);
                break;
                
            case 'unsubscribed':
                this.handleUnsubscribed(message);
                break;
                
            case 'broadcast':
                this.handleBroadcast(message);
                break;
                
            case 'file_upload':
                this.handleFileUpload(message);
                break;
                
            case 'file_download':
                this.handleFileDownload(message);
                break;
                
            case 'dashboard_update':
                this.handleDashboardUpdate(message);
                break;
                
            case 'user_notification':
                this.handleUserNotification(message);
                break;
                
            case 'system_alert':
                this.handleSystemAlert(message);
                break;
                
            case 'heartbeat':
            case 'pong':
                // Handle heartbeat/pong
                break;
                
            case 'error':
                this.handleError(message);
                break;
                
            default:
                console.log('Unknown message type:', message.type, message);
        }
    }
    
    /**
     * Handle welcome message
     */
    handleWelcome(message) {
        console.log('Welcome message:', message.message);
        this.showNotification('Connected to real-time server', 'success');
    }
    
    /**
     * Handle authentication required
     */
    handleAuthRequired(message) {
        console.log('Authentication required');
        this.authenticate();
    }
    
    /**
     * Handle authentication success
     */
    handleAuthSuccess(message) {
        console.log('Authentication successful');
        this.showNotification('Authenticated successfully', 'success');
        
        // Subscribe to default channels
        this.subscribeToDefaultChannels();
    }
    
    /**
     * Handle authentication error
     */
    handleAuthError(message) {
        console.error('Authentication error:', message.message);
        this.showNotification('Authentication failed: ' + message.message, 'error');
    }
    
    /**
     * Handle subscription confirmation
     */
    handleSubscribed(message) {
        console.log('Subscribed to channel:', message.channel);
        this.showNotification('Subscribed to ' + message.channel, 'info');
    }
    
    /**
     * Handle unsubscription confirmation
     */
    handleUnsubscribed(message) {
        console.log('Unsubscribed from channel:', message.channel);
        this.showNotification('Unsubscribed from ' + message.channel, 'info');
    }
    
    /**
     * Handle broadcast message
     */
    handleBroadcast(message) {
        console.log('Broadcast message:', message);
        
        // Show notification for broadcast messages
        if (message.from_username) {
            this.showNotification(
                `${message.from_username}: ${message.message}`,
                'info',
                5000
            );
        }
    }
    
    /**
     * Handle file upload notification
     */
    handleFileUpload(message) {
        console.log('File upload notification:', message);
        
        // Update file list if on files page
        if (window.location.pathname.includes('/files')) {
            this.refreshFileList();
        }
        
        // Show notification
        this.showNotification(
            `File uploaded: ${message.filename}`,
            'success'
        );
    }
    
    /**
     * Handle file download notification
     */
    handleFileDownload(message) {
        console.log('File download notification:', message);
        
        // Show notification
        this.showNotification(
            `File downloaded: ${message.filename}`,
            'info'
        );
    }
    
    /**
     * Handle dashboard update
     */
    handleDashboardUpdate(message) {
        console.log('Dashboard update:', message);
        
        // Update dashboard if on dashboard page
        if (window.location.pathname.includes('/dashboard')) {
            this.updateDashboardStats(message.data);
        }
    }
    
    /**
     * Handle user notification
     */
    handleUserNotification(message) {
        console.log('User notification:', message);
        
        // Show notification
        this.showNotification(
            message.title || 'Notification',
            message.message || '',
            message.type || 'info',
            message.duration || 5000
        );
    }
    
    /**
     * Handle system alert
     */
    handleSystemAlert(message) {
        console.log('System alert:', message);
        
        // Show alert notification
        this.showNotification(
            'System Alert',
            message.message || '',
            'warning',
            10000
        );
    }
    
    /**
     * Handle error message
     */
    handleError(message) {
        console.error('WebSocket error:', message.message);
        this.showNotification('Error: ' + message.message, 'error');
    }
    
    /**
     * Subscribe to default channels
     */
    subscribeToDefaultChannels() {
        // Subscribe to system channel
        this.subscribe('system');
        
        // Subscribe to user-specific channel if user info available
        const userId = this.getCurrentUserId();
        if (userId) {
            this.subscribe('user_' + userId);
        }
        
        // Subscribe to company channel if company info available
        const companyId = this.getCurrentCompanyId();
        if (companyId) {
            this.subscribe('company_' + companyId);
        }
        
        // Subscribe to branch channel if branch info available
        const branchId = this.getCurrentBranchId();
        if (branchId) {
            this.subscribe('branch_' + branchId);
        }
    }
    
    /**
     * Get current user ID
     */
    getCurrentUserId() {
        // Try to get from session data
        const userIdElement = document.querySelector('[data-user-id]');
        if (userIdElement) {
            return userIdElement.getAttribute('data-user-id');
        }
        
        // Try to get from global variable (if available)
        if (window.currentUserId) {
            return window.currentUserId;
        }
        
        return null;
    }
    
    /**
     * Get current company ID
     */
    getCurrentCompanyId() {
        const companyIdElement = document.querySelector('[data-company-id]');
        if (companyIdElement) {
            return companyIdElement.getAttribute('data-company-id');
        }
        
        if (window.currentCompanyId) {
            return window.currentCompanyId;
        }
        
        return null;
    }
    
    /**
     * Get current branch ID
     */
    getCurrentBranchId() {
        const branchIdElement = document.querySelector('[data-branch-id]');
        if (branchIdElement) {
            return branchIdElement.getAttribute('data-branch-id');
        }
        
        if (window.currentBranchId) {
            return window.currentBranchId;
        }
        
        return null;
    }
    
    /**
     * Show connection status
     */
    showConnectionStatus(status, message) {
        // Update connection status indicator
        const statusElement = document.getElementById('ws-status');
        if (statusElement) {
            statusElement.className = `ws-status ws-${status}`;
            statusElement.title = message;
        }
        
        // Update connection status text
        const statusTextElement = document.getElementById('ws-status-text');
        if (statusTextElement) {
            statusTextElement.textContent = message;
        }
    }
    
    /**
     * Show notification
     */
    showNotification(message, type = 'info', duration = 5000) {
        // Try to use existing notification system
        if (window.Toast && window.Toast.show) {
            window.Toast.show(message, type);
        } else if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            // Fallback to browser notification
            if (Notification.permission === 'granted') {
                new Notification('Perdagangan System', {
                    body: message,
                    icon: '/assets/images/logo.png'
                });
            } else {
                // Console fallback
                console.log(`[${type.toUpperCase()}] ${message}`);
            }
        }
    }
    
    /**
     * Refresh file list
     */
    refreshFileList() {
        // Trigger file list refresh if function exists
        if (window.FileModule && window.FileModule.refreshFileList) {
            window.FileModule.refreshFileList();
        } else if (window.location.reload) {
            window.location.reload();
        }
    }
    
    /**
     * Update dashboard statistics
     */
    updateDashboardStats(data) {
        // Update dashboard stats if function exists
        if (window.DashboardModule && window.DashboardModule.updateStats) {
            window.DashboardModule.updateStats(data);
        } else if (window.updateDashboardStats) {
            window.updateDashboardStats(data);
        }
    }
    
    /**
     * Send ping to server
     */
    ping() {
        if (this.isConnected) {
            this.send({ type: 'ping' });
        }
    }
    
    /**
     * Get connection status
     */
    getConnectionStatus() {
        return {
            connected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts,
            channels: this.channels,
            ws: this.ws ? this.ws.readyState : null
        };
    }
}

// Initialize WebSocket module when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if WebSocket is enabled
    const wsEnabled = document.querySelector('meta[name="websocket-enabled"]');
    if (wsEnabled && wsEnabled.getAttribute('content') === 'true') {
        window.WebSocketModule = new WebSocketModule();
        console.log('WebSocket module initialized');
    } else {
        console.log('WebSocket module disabled');
    }
});

// Export for global access
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WebSocketModule;
}
