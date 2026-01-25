<?php

/**
 * WebSocket Server
 * 
 * Handles WebSocket connections for real-time notifications and live updates
 * Supports multiple channels and user-specific communications
 */

class WebSocketServer {
    
    private $server;
    private $clients = [];
    private $channels = [];
    private $config;
    private $startTime;
    private $lastHeartbeat;
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'host' => '0.0.0.0',
            'port' => 8080,
            'max_connections' => 1000,
            'heartbeat_interval' => 30,
            'allowed_origins' => ['*'],
            'auth_required' => true
        ], $config);
    }
    
    /**
     * Start WebSocket server
     */
    public function start() {
        // Create WebSocket server
        $context = stream_context_create([
            'socket' => [
                'bindto' => $this->config['host'] . ':' . $this->config['port'],
                'backlog' => 128,
                'reuseport' => true,
            ],
        ]);
        
        $this->server = stream_socket_server(
            "tcp://{$this->config['host']}:{$this->config['port']}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );
        
        if (!$this->server) {
            throw new Exception("WebSocket Server Error: $errstr ($errno)");
        }
        
        echo "WebSocket Server started on {$this->config['host']}:{$this->config['port']}\n";
        
        // Set non-blocking mode
        stream_set_blocking($this->server, 0);
        
        return $this->server;
    }
    
    /**
     * Run the server (non-blocking)
     */
    public function run() {
        $this->startTime = time();
        
        echo "WebSocket Server running... Press Ctrl+C to stop\n";
        
        while (true) {
            $read = [$this->server];
            $write = [];
            $except = null;
            
            $changed = stream_select($read, $write, $except, 0, 1);
            
            if ($changed === false) {
                continue;
            }
            
            // Handle new connections
            if (in_array($this->server, $read)) {
                $client = @stream_socket_accept($this->server);
                
                if ($client) {
                    $this->handleNewConnection($client);
                }
            }
            
            // Handle existing clients
            foreach ($this->clients as $clientId => $clientData) {
                if (in_array($clientData['socket'], $read)) {
                    $data = @fread($clientData['socket'], 2048);
                    
                    if ($data === false || $data === '') {
                        $this->handleDisconnection($clientId);
                    } else {
                        $this->handleMessage($clientId, $data);
                    }
                }
            }
            
            // Send queued messages
            foreach ($this->clients as $clientId => $clientData) {
                if (!empty($clientData['outgoing'])) {
                    $written = @fwrite($clientData['socket'], $clientData['outgoing']);
                    
                    if ($written !== false) {
                        $clientData['outgoing'] = substr($clientData['outgoing'], $written);
                        if (empty($clientData['outgoing'])) {
                            unset($clientData['outgoing']);
                        }
                    }
                }
            }
            
            // Clean up disconnected clients
            $this->cleanupClients();
            
            // Send heartbeat every 30 seconds
            if (time() - $this->lastHeartbeat >= $this->config['heartbeat_interval']) {
                $this->sendHeartbeat();
                $this->lastHeartbeat = time();
            }
            
            // Small delay to prevent CPU overload
            usleep(10000); // 10ms
        }
    }
    
    /**
     * Handle new WebSocket connection
     */
    private function handleNewConnection($socket) {
        $clientId = uniqid();
        
        // Perform WebSocket handshake
        $headers = $this->performHandshake($socket);
        
        if ($headers) {
            $this->clients[$clientId] = [
                'socket' => $socket,
                'user_id' => null,
                'authenticated' => false,
                'channels' => [],
                'last_ping' => time(),
                'ip' => stream_socket_get_name($socket, true),
                'outgoing' => '',
                'handshake_complete' => false
            ];
            
            // Send welcome message
            $this->sendToClient($clientId, [
                'type' => 'welcome',
                'message' => 'Connected to WebSocket server',
                'client_id' => $clientId,
                'server_time' => date('Y-m-d H:i:s')
            ]);
            
            // Request authentication
            $this->sendToClient($clientId, [
                'type' => 'auth_required',
                'message' => 'Please authenticate with your session token'
            ]);
        }
    }
    
    /**
     * Perform WebSocket handshake
     */
    private function performHandshake($socket) {
        // Read request headers
        $headers = '';
        $line = '';
        
        while (($line = trim(fgets($socket))) !== '') {
            $headers .= $line . "\r\n";
        }
        
        // Parse headers
        $lines = explode("\r\n", $headers);
        $requestLine = $lines[0];
        $headers = array_slice($lines, 1);
        
        // Extract key-value pairs
        $requestHeaders = [];
        foreach ($headers as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(':', $header, 2);
                $requestHeaders[trim(strtolower($key))] = trim($value);
            }
        }
        
        // Check if this is a WebSocket request
        if (strpos($requestLine, 'GET') === false || strpos($requestLine, 'HTTP/1.1') === false) {
            return false;
        }
        
        // Check Origin
        $origin = $requestHeaders['sec-websocket-origin'] ?? '';
        if (!empty($origin) && !in_array('*', $this->config['allowed_origins']) && !in_array($origin, $this->config['allowed_origins'])) {
            return false;
        }
        
        // Generate WebSocket key
        $key = $requestHeaders['sec-websocket-key'] ?? '';
        if (empty($key)) {
            return false;
        }
        
        $acceptKey = base64_encode(
            pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))
        );
        
        // Create response headers
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $acceptKey\r\n";
        $response .= "\r\n";
        
        // Send response
        @fwrite($socket, $response);
        
        return true;
    }
    
    /**
     * Clean up disconnected clients
     */
    private function cleanupClients() {
        foreach ($this->clients as $clientId => $clientData) {
            if (!is_resource($clientData['socket'])) {
                $this->handleDisconnection($clientId);
            }
        }
    }
    
    /**
     * Handle incoming WebSocket messages
     */
    private function handleMessage($clientId, $data) {
        try {
            $message = json_decode($data, true);
            
            if (!$message || !isset($message['type'])) {
                $this->sendToClient($clientId, [
                    'type' => 'error',
                    'message' => 'Invalid message format'
                ]);
                return;
            }
            
            switch ($message['type']) {
                case 'auth':
                    $this->handleAuthentication($clientId, $message);
                    break;
                    
                case 'subscribe':
                    $this->handleSubscription($clientId, $message);
                    break;
                    
                case 'unsubscribe':
                    $this->handleUnsubscription($clientId, $message);
                    break;
                    
                case 'ping':
                    $this->handlePing($clientId);
                    break;
                    
                case 'broadcast':
                    $this->handleBroadcast($clientId, $message);
                    break;
                    
                default:
                    $this->sendToClient($clientId, [
                        'type' => 'error',
                        'message' => 'Unknown message type: ' . $message['type']
                    ]);
            }
        } catch (Exception $e) {
            error_log("WebSocket Message Error: " . $e->getMessage());
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Error processing message'
            ]);
        }
    }
    
    /**
     * Handle client authentication
     */
    private function handleAuthentication($clientId, $message) {
        $token = $message['token'] ?? null;
        
        if (!$token) {
            $this->sendToClient($clientId, [
                'type' => 'auth_error',
                'message' => 'Authentication token required'
            ]);
            return;
        }
        
        // Validate session token
        $userData = $this->validateSessionToken($token);
        
        if (!$userData) {
            $this->sendToClient($clientId, [
                'type' => 'auth_error',
                'message' => 'Invalid authentication token'
            ]);
            return;
        }
        
        // Authenticate client
        $this->clients[$clientId]['authenticated'] = true;
        $this->clients[$clientId]['user_id'] = $userData['user_id'];
        $this->clients[$clientId]['user_data'] = $userData;
        $this->clients[$clientId]['handshake_complete'] = true;
        
        // Auto-subscribe to user-specific channels
        $this->autoSubscribeUserChannels($clientId);
        
        $this->sendToClient($clientId, [
            'type' => 'auth_success',
            'message' => 'Authentication successful',
            'user_data' => [
                'user_id' => $userData['user_id'],
                'username' => $userData['username'],
                'role' => $userData['role']
            ]
        ]);
        
        // Notify other users about new connection
        $this->broadcastToChannel('system', [
            'type' => 'user_connected',
            'user_id' => $userData['user_id'],
            'username' => $userData['username'],
            'timestamp' => date('Y-m-d H:i:s')
        ], $clientId);
    }
    
    /**
     * Handle channel subscription
     */
    private function handleSubscription($clientId, $message) {
        if (!$this->isAuthenticated($clientId)) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Authentication required'
            ]);
            return;
        }
        
        $channel = $message['channel'] ?? null;
        
        if (!$channel) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Channel name required'
            ]);
            return;
        }
        
        // Subscribe to channel
        if (!in_array($channel, $this->clients[$clientId]['channels'])) {
            $this->clients[$clientId]['channels'][] = $channel;
            
            // Add to channel subscribers
            if (!isset($this->channels[$channel])) {
                $this->channels[$channel] = [];
            }
            $this->channels[$channel][$clientId] = $clientId;
            
            $this->sendToClient($clientId, [
                'type' => 'subscribed',
                'channel' => $channel,
                'message' => "Subscribed to channel: $channel"
            ]);
            
            // Send confirmation message to channel
            $this->broadcastToChannel($channel, [
                'type' => 'user_subscribed',
                'user_id' => $this->clients[$clientId]['user_id'],
                'channel' => $channel,
                'timestamp' => date('Y-m-d H:i:s')
            ], $clientId);
        }
    }
    
    /**
     * Handle channel unsubscription
     */
    private function handleUnsubscription($clientId, $message) {
        if (!$this->isAuthenticated($clientId)) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Authentication required'
            ]);
            return;
        }
        
        $channel = $message['channel'] ?? null;
        
        if (!$channel) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Channel name required'
            ]);
            return;
        }
        
        // Unsubscribe from channel
        if (($key = array_search($channel, $this->clients[$clientId]['channels'])) !== false) {
            unset($this->clients[$clientId]['channels'][$key]);
            
            // Remove from channel subscribers
            if (isset($this->channels[$channel][$clientId])) {
                unset($this->channels[$channel][$clientId]);
            }
            
            $this->sendToClient($clientId, [
                'type' => 'unsubscribed',
                'channel' => $channel,
                'message' => "Unsubscribed from channel: $channel"
            ]);
            
            // Send confirmation message to channel
            $this->broadcastToChannel($channel, [
                'type' => 'user_unsubscribed',
                'user_id' => $this->clients[$clientId]['user_id'],
                'channel' => $channel,
                'timestamp' => date('Y-m-d H:i:s')
            ], $clientId);
        }
    }
    
    /**
     * Handle ping message
     */
    private function handlePing($clientId) {
        $this->clients[$clientId]['last_ping'] = time();
        
        $this->sendToClient($clientId, [
            'type' => 'pong',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle broadcast message
     */
    private function handleBroadcast($clientId, $message) {
        if (!$this->isAuthenticated($clientId)) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Authentication required'
            ]);
            return;
        }
        
        $channel = $message['channel'] ?? null;
        $messageData = $message['message'] ?? null;
        
        if (!$channel || !$messageData) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Channel and message required for broadcast'
            ]);
            return;
        }
        
        // Check if user has permission to broadcast to this channel
        if (!$this->canBroadcastToChannel($clientId, $channel)) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'No permission to broadcast to channel: ' . $channel
            ]);
            return;
        }
        
        $this->broadcastToChannel($channel, [
            'type' => 'broadcast',
            'message' => $messageData,
            'from_user_id' => $this->clients[$clientId]['user_id'],
            'from_username' => $this->clients[$clientId]['user_data']['username'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle client disconnection
     */
    private function handleDisconnection($clientId) {
        if (!isset($this->clients[$clientId])) {
            return;
        }
        
        $clientData = $this->clients[$clientId];
        
        // Notify other users about disconnection
        if ($clientData['authenticated']) {
            $this->broadcastToChannel('system', [
                'type' => 'user_disconnected',
                'user_id' => $clientData['user_id'],
                'username' => $clientData['user_data']['username'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Remove from all channels
        foreach ($clientData['channels'] as $channel) {
            if (isset($this->channels[$channel][$clientId])) {
                unset($this->channels[$channel][$clientId]);
            }
        }
        
        // Close socket
        @fclose($clientData['socket']);
        
        // Remove from clients list
        unset($this->clients[$clientId]);
    }
    
    /**
     * Send heartbeat to all clients
     */
    private function sendHeartbeat() {
        $heartbeatMessage = [
            'type' => 'heartbeat',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_time' => time()
        ];
        
        foreach ($this->clients as $clientId => $clientData) {
            $this->sendToClient($clientId, $heartbeatMessage);
        }
    }
    
    /**
     * Send message to specific client
     */
    private function sendToClient($clientId, $data) {
        if (!isset($this->clients[$clientId])) {
            return false;
        }
        
        $message = json_encode($data);
        
        if (isset($this->clients[$clientId]['outgoing'])) {
            $this->clients[$clientId]['outgoing'] .= $message;
        } else {
            $this->clients[$clientId]['outgoing'] = $message;
        }
        
        return true;
    }
    
    /**
     * Broadcast message to channel
     */
    private function broadcastToChannel($channel, $data, $excludeClientId = null) {
        if (!isset($this->channels[$channel])) {
            return false;
        }
        
        $message = json_encode($data);
        
        foreach ($this->channels[$channel] as $subscriberId) {
            if ($subscriberId !== $excludeClientId && isset($this->clients[$subscriberId])) {
                if (isset($this->clients[$subscriberId]['outgoing'])) {
                    $this->clients[$subscriberId]['outgoing'] .= $message;
                } else {
                    $this->clients[$subscriberId]['outgoing'] = $message;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Broadcast message to all authenticated clients
     */
    private function broadcastToAll($data) {
        $message = json_encode($data);
        
        foreach ($this->clients as $clientId => $clientData) {
            if ($clientData['authenticated']) {
                if (isset($clientData['outgoing'])) {
                    $this->clients[$clientId]['outgoing'] .= $message;
                } else {
                    $this->clients[$clientId]['outgoing'] = $message;
                }
            }
        }
    }
    
    /**
     * Check if client is authenticated
     */
    private function isAuthenticated($clientId) {
        return isset($this->clients[$clientId]) && $this->clients[$clientId]['authenticated'];
    }
    
    /**
     * Check if user can broadcast to channel
     */
    private function canBroadcastToChannel($clientId, $channel) {
        if (!$this->isAuthenticated($clientId)) {
            return false;
        }
        
        $userRole = $this->clients[$clientId]['user_data']['role'] ?? 'user';
        
        // Admin and super admin can broadcast to any channel
        if (in_array($userRole, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Users can broadcast to their own channels
        if (strpos($channel, 'user_' . $this->clients[$clientId]['user_id']) === 0) {
            return true;
        }
        
        // Company/branch owners can broadcast to their channels
        if (strpos($channel, 'company_' . ($this->clients[$clientId]['user_data']['company_id'] ?? 0)) === 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Auto-subscribe user to their personal channels
     */
    private function autoSubscribeUserChannels($clientId) {
        $userId = $this->clients[$clientId]['user_id'];
        $userRole = $this->clients[$clientId]['user_data']['role'] ?? 'user';
        $companyId = $this->clients[$clientId]['user_data']['company_id'] ?? 0;
        $branchId = $this->clients[$clientId]['user_data']['branch_id'] ?? 0;
        
        // Subscribe to user-specific channel
        $this->handleSubscription($clientId, [
            'channel' => 'user_' . $userId
        ]);
        
        // Subscribe to role-based channels
        if (in_array($userRole, ['admin', 'super_admin'])) {
            $this->handleSubscription($clientId, ['channel' => 'admin']);
        }
        
        // Subscribe to company channel
        if ($companyId > 0) {
            $this->handleSubscription($clientId, ['channel' => 'company_' . $companyId]);
        }
        
        // Subscribe to branch channel
        if ($branchId > 0) {
            $this->handleSubscription($clientId, ['channel' => 'branch_' . $branchId]);
        }
    }
    
    /**
     * Validate session token
     */
    private function validateSessionToken($token) {
        // This would typically validate against your session storage
        // For now, return mock user data for testing
        session_start();
        
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return [
                'user_id' => $_SESSION['user_id'] ?? 1,
                'username' => $_SESSION['username'] ?? 'test_user',
                'role' => $_SESSION['user_role'] ?? 'user',
                'company_id' => $_SESSION['company_id'] ?? 1,
                'branch_id' => $_SESSION['branch_id'] ?? 1
            ];
        }
        
        return null;
    }
    
    /**
     * Get server statistics
     */
    public function getStats() {
        return [
            'connected_clients' => count($this->clients),
            'authenticated_clients' => count(array_filter($this->clients, function($client) {
                return $client['authenticated'];
            })),
            'channels' => count($this->channels),
            'server_time' => date('Y-m-d H:i:s'),
            'uptime' => time() - ($this->startTime ?? time())
        ];
    }
    
    /**
     * Get connected clients
     */
    public function getClients() {
        return $this->clients;
    }
    
    /**
     * Get channels
     */
    public function getChannels() {
        return $this->channels;
    }
}
