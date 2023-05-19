<?php

class InstantApi
{
    private $db;
    private $base_url;

    public function __construct($config)
    {
        $db_host = $config['db_host'];
        $db_name = $config['db_name'];
        $db_user = $config['db_user'];
        $db_pass = $config['db_pass'];
        $this->base_url = $config['base_url'];

        try {
            $this->db = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8", $db_user, $db_pass);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function handleRequest()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        header('Content-Type: text/plain');

        switch (true) {
            case (strcasecmp($uri, $this->base_url) === 0 && $method === 'GET'):
                $this->showTables();
                break;
        
            case (preg_match('/^' . preg_quote($this->base_url, '/') . '\/([a-zA-Z0-9-_]+)(\?.+)?$/', $uri, $matches) ? true : false):
                $table_name = $matches[1];
                if ($method === 'GET') {
                    if (isset($matches[2])) {
                        parse_str(parse_url($matches[2], PHP_URL_QUERY), $query);
                        $limit = isset($query['limit']) ? intval($query['limit']) : 10;
                        $skip = isset($query['skip']) ? intval($query['skip']) : 0;
                        $this->getRecords($table_name, $limit, $skip);
                    } else {
                        $this->getRecords($table_name);
                    }
                } elseif ($method === 'POST') {
                    $this->addRecord($table_name);
                }
                break;
        
            case (preg_match('/^' . preg_quote($this->base_url, '/') . '\/([a-zA-Z0-9-_]+)\/([0-9]+)$/', $uri, $matches) ? true : false):
                $table_name = $matches[1];
                $id = $matches[2];
                if ($method === 'GET') {
                    $this->getRecord($table_name, $id);
                } elseif ($method === 'PUT') {
                    $this->updateRecord($table_name, $id);
                } elseif ($method === 'DELETE') {
                    $this->deleteRecord($table_name, $id);
                }
                break;
        
            default:
                header('HTTP/1.1 404 Not Found');
                echo json_encode(array('error' => 'Endpoint no encontrado'));
                break;
        }
        
        
    }

    private function showTables()
    {
        $stmt = $this->db->query("SHOW TABLES");
        $api = $stmt->fetchAll(PDO::FETCH_COLUMN);
        header('Content-Type: application/json');
        echo json_encode($api);
    }

    private function getRecords($table_name)
    {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $skip = isset($_GET['skip']) ? intval($_GET['skip']) : 0;
    
        $stmt = $this->db->prepare("SELECT * FROM $table_name LIMIT ?, ?");
        $stmt->bindValue(1, $skip, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
    
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($fields);
    }

    private function addRecord($table_name)
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        if (!empty($data) && is_array($data)) {
            $columns = array_keys($data);
            $values = array_values($data);
            $columns_str = implode(',', $columns);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));

            $stmt = $this->db->prepare("INSERT INTO $table_name ($columns_str) VALUES ($placeholders)");
            $stmt->execute($values);
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'record agregado con éxito'));
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo "Datos inválidos";
        }
    }

    private function getRecord($table_name, $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM $table_name WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            header('Content-Type: application/json');
            echo json_encode($record);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo "record no encontrado";
        }
    }

    private function updateRecord($table_name, $id)
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        if (!empty($data) && is_array($data)) {
            $set = '';
            $values = array();
            foreach ($data as $column => $value) {
                $set .= "$column = ?,";
                $values[] = $value;
            }
            $set = rtrim($set, ',');
            $values[] = $id;

            $stmt = $this->db->prepare("UPDATE $table_name SET $set WHERE id = ?");
            $stmt->execute($values);
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'record actualizado con éxito'));
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo "Datos inválidos";
        }
    }

    private function deleteRecord($table_name, $id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM $table_name WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                $stmt = $this->db->prepare("DELETE FROM $table_name WHERE id = ?");
                $stmt->execute([$id]);
                header('Content-Type: application/json');
                echo json_encode(array('mensaje' => 'record eliminado con éxito'));
            } else {
                header('HTTP/1.1 404 Not Found');
                echo "record no encontrado";
            }
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo "Error: " . $e->getMessage();
        }
    }
}

$config = [
    'db_host' => '',
    'db_name' => '',
    'db_user' => '',
    'db_pass' => '',
    'base_url' => '/app/api'
];

$api = new InstantApi($config);
$api->handleRequest();

?>