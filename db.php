<?php
// Supabase configuration
define('SUPABASE_URL', 'https://sfjpiyevasnmvddgtofz.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNmanBpeWV2YXNubXZkZGd0b2Z6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzkyODAxNTIsImV4cCI6MjA5NDg1NjE1Mn0.ySYf_WZYJr_A7oXhH5wGkri5qW0OctsINK82716t0Ac');

// Helper function to call Supabase REST API
function supabaseRequest($endpoint, $method = 'GET', $body = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body ? json_encode($body) : null,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    return json_decode($response, true);
}

// For backward compatibility - returns mysqli-like result
function mysqli_query($conn, $sql) {
    // Parse the SQL and convert to Supabase REST API calls
    return new MysqliResult($sql);
}

function mysqli_fetch_assoc($result) {
    return $result->fetch();
}

function mysqli_num_rows($result) {
    return $result->num_rows();
}

function mysqli_real_escape_string($conn, $str) {
    return str_replace("'", "''", $str);
}

// Wrapper class to mimic mysqli result
class MysqliResult {
    public $num_rows = 0;
    private $data = [];
    private $position = 0;
    
    public function __construct($sql) {
        $sql = trim($sql);
        
        if (stripos($sql, 'SELECT') === 0) {
            // Parse SELECT query
            $this->parseSelect($sql);
        }
    }
    
    private function parseSelect($sql) {
        // Extract table name
        preg_match('/FROM\s+(\w+)/i', $sql, $matches);
        $table = $matches[1] ?? '';
        
        // Extract WHERE conditions
        $where = '';
        if (preg_match('/WHERE\s+(.+?)(ORDER|LIMIT|$)/i', $sql, $matches)) {
            $where = trim($matches[1]);
        }
        
        // Extract ORDER BY
        $order = 'id.desc';
        if (preg_match('/ORDER BY\s+(\w+)(?:\s+(ASC|DESC))?/i', $sql, $matches)) {
            $order = $matches[1] . ($matches[2] ?? '.desc');
        }
        
        // Extract LIMIT
        $limit = 20;
        if (preg_match('/LIMIT\s+(\d+)/i', $sql, $matches)) {
            $limit = (int)$matches[1];
        }
        
        // Build query params
        $params = 'select=*&order=' . $order . '&limit=' . $limit;
        
        // Apply filters
        if ($where) {
            if (preg_match('/status\s*=\s*[\'"]?(\w+)[\'"]?/i', $where, $m)) {
                $params .= '&status=eq.' . $m[1];
            }
            if (preg_match('/id\s*=\s*(\d+)/i', $where, $m)) {
                $params .= '&id=eq.' . $m[1];
            }
            if (preg_match('/title\s+LIKE\s+[\'"]%(.+?)%[\'"]/i', $where, $m)) {
                $params .= '&title=ilike.' . $m[1];
            }
            if (preg_match('/description\s+LIKE\s+[\'"]%(.+?)%[\'"]/i', $where, $m)) {
                $params .= '&description=ilike.' . $m[1];
            }
            if (preg_match('/location\s+LIKE\s+[\'"]%(.+?)%[\'"]/i', $where, $m)) {
                $params .= '&location=ilike.' . $m[1];
            }
        }
        
        $result = supabaseRequest($table . '?' . $params);
        $this->data = is_array($result) ? $result : [];
        $this->num_rows = count($this->data);
    }
    
    public function fetch() {
        if ($this->position >= $this->num_rows) {
            return null;
        }
        return $this->data[$this->position++];
    }
}

// Get connection (not really used for Supabase)
$conn = null;
?>
