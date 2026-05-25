<?php
define('SUPABASE_URL', 'https://sfjpiyevasnmvddgtofz.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNmanBpeWV2YXNubXZkZGd0b2Z6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzkyODAxNTIsImV4cCI6MjA5NDg1NjE1Mn0.ySYf_WZYJr_A7oXhH5wGkri5qW0OctsINK82716t0Ac');

function supabaseRequest($endpoint, $method = 'GET', $body = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    $headers = ['apikey: ' . SUPABASE_KEY, 'Authorization: Bearer ' . SUPABASE_KEY, 'Content-Type: application/json', 'Prefer: return=representation'];
    $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => $body ? json_encode($body) : null, 'ignore_errors' => true]]);
    $response = file_get_contents($url, false, $context);
    return json_decode($response, true);
}

class DbResult {
    public $num_rows = 0;
    public $insert_id = 0;
    private $data = [];
    private $position = 0;

    public function __construct($sql, $result = null) {
        $sql = trim($sql);
        if ($result !== null) {
            $this->data = is_array($result) ? $result : [];
            $this->num_rows = count($this->data);
        } elseif (stripos($sql, 'SELECT') === 0 || stripos($sql, 'SHOW') === 0 || stripos($sql, 'INSERT') === 0 || stripos($sql, 'UPDATE') === 0 || stripos($sql, 'DELETE') === 0) {
            $this->executeQuery($sql);
        }
    }

    private function executeQuery($sql) {
        $sql = trim($sql);
        $type = strtoupper(substr($sql, 0, 6));

        if ($type === 'SELECT' || $type === 'SHOW') {
            $this->parseSelect($sql);
        } elseif ($type === 'INSERT') {
            $this->handleInsert($sql);
        } elseif ($type === 'UPDATE') {
            $this->handleUpdate($sql);
        } elseif ($type === 'DELETE') {
            $this->handleDelete($sql);
        }
    }

    private function parseSelect($sql) {
        preg_match('/FROM\s+(\w+)/i', $sql, $matches);
        $table = $matches[1] ?? '';
        $params = 'select=*&order=id.desc&limit=50';

        if (preg_match('/WHERE\s+(.+?)(ORDER|LIMIT|$)/i', $sql, $matches)) {
            $where = trim($matches[1]);
            if (preg_match('/status\s*=\s*[\'"]?(\w+)[\'"]?/i', $where, $m)) { $params .= '&status=eq.' . $m[1]; }
            if (preg_match('/id\s*=\s*(\d+)/i', $where, $m)) { $params .= '&id=eq.' . $m[1]; }
            if (preg_match('/email\s*=\s*[\'"]?([^\']+)[\'"]?/i', $where, $m)) { $params .= '&email=eq.' . urlencode($m[1]); }
            if (preg_match('/role\s*=\s*[\'"]?(\w+)[\'"]?/i', $where, $m)) { $params .= '&role=eq.' . $m[1]; }
            if (preg_match('/title\s+LIKE\s+[\'"]%(.+?)%[\'"]/i', $where, $m)) { $params .= '&title=ilike.' . urlencode($m[1]); }
        }
        if (preg_match('/LIMIT\s+(\d+)/i', $sql, $m)) { $params = 'select=*&order=id.desc&limit=' . (int)$m[1]; }

        $result = supabaseRequest($table . '?' . $params);
        $this->data = is_array($result) ? $result : [];
        $this->num_rows = count($this->data);
    }

private function handleInsert($sql) {
        $sql = trim($sql);
        // Match: INSERT INTO table (col1, col2) VALUES (val1, val2)
        if (preg_match('/INSERT INTO\s+(\w+)\s*\(([^)]+)\)\s+VALUES\s*\((.+)\)/is', $sql, $matches)) {
            $table = $matches[1];
            $columns = array_map('trim', explode(',', $matches[2]));
            $valuesStr = $matches[3];
            
            // Parse values properly - handle quoted strings with commas inside
            $values = [];
            $current = '';
            $inQuote = false;
            $quoteChar = '';
            
            for ($i = 0; $i < strlen($valuesStr); $i++) {
                $char = $valuesStr[$i];
                
                if (($char === '"' || $char === "'") && !$inQuote) {
                    $inQuote = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar && $inQuote) {
                    // Check if next char escapes the quote
                    if ($i + 1 < strlen($valuesStr) && $valuesStr[$i + 1] === $quoteChar) {
                        $current .= $quoteChar;
                        $i++;
                    } else {
                        $inQuote = false;
                    }
                } elseif ($char === ',' && !$inQuote) {
                    $values[] = trim($current);
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
            if (trim($current) !== '') {
                $values[] = trim($current);
            }
            
            $data = [];
            foreach ($columns as $i => $col) {
                $val = isset($values[$i]) ? trim($values[$i], " '\"") : '';
                $data[$col] = $val;
            }
            
            $result = supabaseRequest($table, 'POST', $data);
            $this->insert_id = isset($result['id']) ? $result['id'] : 1;
            $this->num_rows = 1;
        }
    }
            error_log("Data to insert: " . print_r($data, true));
            $result = supabaseRequest($table, 'POST', $data);
            error_log("Supabase result: " . print_r($result, true));
            $this->insert_id = isset($result['id']) ? $result['id'] : 1;
            $this->num_rows = 1;
        }
    }

    private function handleUpdate($sql) {
        preg_match('/UPDATE\s+(\w+)\s+SET\s+(.+?)\s+WHERE\s+(.+)/i', $sql, $matches);
        if ($matches) {
            $table = $matches[1];
            $setPart = $matches[2];
            $wherePart = $matches[3];

            $id = null;
            if (preg_match('/id\s*=\s*[\'"]?(\d+)[\'"]?/i', $wherePart, $m)) { $id = $m[1]; }
            if (preg_match('/email\s*=\s*[\'"]?([^\'"]+)[\'"]?/i', $wherePart, $m)) { $id = $m[1]; }

            preg_match_all('/(\w+)\s*=\s*[\'"]?([^\'\",]+)[\'"]?/i', $setPart, $setMatches, PREG_SET_ORDER);
            $data = [];
            foreach ($setMatches as $pair) { $data[$pair[1]] = trim($pair[2], " '\""); }

            if ($id) {
                $result = supabaseRequest($table . '?id=eq.' . $id, 'PATCH', $data);
                $this->num_rows = 1;
            }
        }
    }

    private function handleDelete($sql) {
        preg_match('/DELETE FROM\s+(\w+)\s+WHERE\s+(.+)/i', $sql, $matches);
        if ($matches) {
            $table = $matches[1];
            $where = $matches[2];
            if (preg_match('/id\s*=\s*[\'"]?(\d+)[\'"]?/i', $where, $m)) {
                $result = supabaseRequest($table . '?id=eq.' . $m[1], 'DELETE');
                $this->num_rows = 1;
            }
        }
    }

    public function fetch_assoc() {
        if ($this->position >= $this->num_rows) { return null; }
        return $this->data[$this->position++];
    }
}

function db_connect($host, $user, $pass, $db) { return true; }
function db_query($conn, $sql) { return new DbResult($sql); }
function db_num_rows($result) { return $result->num_rows; }
function db_fetch_assoc($result) { return $result->fetch_assoc(); }
function db_real_escape_string($conn, $str) { return str_replace(["'", "\"", "\\"], ["''", "\\\"", "\\\\"], $str); }
function db_insert_id($conn) { return 0; }

$conn = true;
?>