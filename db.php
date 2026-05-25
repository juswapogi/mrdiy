<?php
// Supabase configuration
define('SUPABASE_URL', 'https://sfjpiyevasnmvddgtofz.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNmanBpeWV2YXNubXZkZGd0b2Z6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzkyODAxNTIsImV4cCI6MjA5NDg1NjE1Mn0.ySYf_WZYJr_A7oXhH5wGkri5qW0OctsINK82716t0Ac');

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

class DbResult {
    public $num_rows = 0;
    public $insert_id = 0;
    private $data = [];
    private $position = 0;

    public function __construct($sql) {
        $sql = trim($sql);
        if (stripos($sql, 'SELECT') === 0) {
            $this->parseSelect($sql);
        } elseif (stripos($sql, 'INSERT') === 0) {
            $this->handleInsert($sql);
        }
    }

    private function parseSelect($sql) {
        preg_match('/FROM\s+(\w+)/i', $sql, $matches);
        $table = $matches[1] ?? '';
        $params = 'select=*&order=id.desc&limit=50';
        if (preg_match('/WHERE\s+(.+?)(ORDER|LIMIT|$)/i', $sql, $matches)) {
            $where = trim($matches[1]);
            if (preg_match('/status\s*=\s*[\'"]?(\w+)[\'"]?/i', $where, $m)) {
                $params .= '&status=eq.' . $m[1];
            }
            if (preg_match('/id\s*=\s*(\d+)/i', $where, $m)) {
                $params .= '&id=eq.' . $m[1];
            }
            if (preg_match('/email\s*=\s*[\'"]?([^水]+)[\'"]?/i', $where, $m)) {
                $params .= '&email=eq.' . urlencode($m[1]);
            }
        }
        if (preg_match('/LIMIT\s+(\d+)/i', $sql, $m)) {
            $params = 'select=*&order=id.desc&limit=' . (int)$m[1];
        }
        $result = supabaseRequest($table . '?' . $params);
        $this->data = is_array($result) ? $result : [];
        $this->num_rows = count($this->data);
    }

    private function handleInsert($sql) {
        preg_match('/INSERT INTO\s+(\w+)\s*\((.+?)\)\s*VALUES\s*\((.+?)\)/i', $sql, $matches);
        if ($matches) {
            $table = $matches[1];
            $columns = explode(',', $matches[2]);
            $values = explode(',', $matches[3]);
            $data = [];
            foreach ($columns as $i => $col) {
                $val = trim($values[$i]);
                $val = trim($val, " '\"");
                $data[trim($col)] = $val;
            }
            $result = supabaseRequest($table, 'POST', $data);
            $this->insert_id = isset($result['id']) ? $result['id'] : 1;
        }
    }

    public function fetch_assoc() {
        if ($this->position >= $this->num_rows) {
            return null;
        }
        return $this->data[$this->position++];
    }
}

function db_query($conn, $sql) {
    return new DbResult($sql);
}

function db_num_rows($result) {
    return $result->num_rows;
}

function db_fetch_assoc($result) {
    return $result->fetch_assoc();
}

function db_real_escape_string($conn, $str) {
    return str_replace("'", "''", $str);
}

$conn = null;
?>
