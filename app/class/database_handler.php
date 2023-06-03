<?php

class DatabaseHandler {
    private $db;

    public function __construct($dbPath) {
        $this->connect($dbPath);
    }
      
    private function connect($dbPath) {
        try {
          $this->db = new PDO("sqlite:" . $dbPath);
          $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
          throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function getTableNames() {
        return $this->execute("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTableData($tableName, $limit = 51, $offset = 0)
    {
        $this->validateTableName($tableName);
        $stmt = $this->db->prepare("SELECT * FROM $tableName LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    private function validateTableName($tableName) {
        if (!in_array($tableName, $this->getTableNames())) {
            throw new Exception("Table $tableName does not exist.");
        }
    }

    private function sortData($tableData) {
        usort($tableData, function($a, $b) {
            $aDateTime = new DateTime($a['timestamp']);
            $bDateTime = new DateTime($b['timestamp']);

            return $bDateTime->getTimestamp() - $aDateTime->getTimestamp();
        });

        return $tableData;
    }

    public function hasMoreData($tableName, $offset) {
        $this->validateTableName($tableName);
        $stmt = $this->db->prepare("SELECT 1 FROM $tableName LIMIT 1 OFFSET :offset");
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return !empty($stmt->fetch(PDO::FETCH_ASSOC));
    }
    
    public function getChartData($tableName, $dateRange = 7, $granularity = 'day') {
        $this->validateTableName($tableName);
        $sql = $this->buildSQL($tableName, $dateRange, $granularity);

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->prepareChartData($result);
    }




    
    private function buildSQL($tableName, $dateRange, $granularity) {
        $dateFormats = [
            'hour' => '%Y-%m-%d %H:00:00',
            '4hours' => '%Y-%m-%d %H:00:00',
            '8hours' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            '2days' => '%Y-%m-%d',
            '3days' => '%Y-%m-%d',
            'week' => '%Y-%W',
            '2weeks' => '%Y-%W',
            'month' => '%Y-%m',
            '3months' => '%Y-%m',
            '6months' => '%Y-%m',
            '9months' => '%Y-%m',
            'year' => '%Y',
            '3years' => '%Y',
            '5years' => '%Y',
        ];
        if (!isset($dateFormats[$granularity])) {
            throw new Exception("Invalid granularity: $granularity");
        }
        $groupByClause = 'GROUP BY';
        $groupByFormat = $dateFormats[$granularity];
        if ($granularity === 'hour') {
            $groupByClause .= " strftime('%Y-%m-%d %H:00:00', timestamp)";
            $groupByFormat = '';
        } elseif ($granularity === '4hours') {
            $groupByClause .= " strftime('%Y-%m-%d %H:00:00', datetime(timestamp, '-' || (strftime('%H', timestamp) % 4) || ' hours'))";
            $groupByFormat = '';
        } elseif ($granularity === '8hours') {
            $groupByClause .= " strftime('%Y-%m-%d %H:00:00', datetime(timestamp, '-' || (strftime('%H', timestamp) % 8) || ' hours'))";
            $groupByFormat = '';
        } elseif (strpos($granularity, 'month') !== false) {
            $groupByClause .= " strftime('%Y-%m', timestamp)";
        } elseif (strpos($granularity, 'year') !== false) {
            $groupByClause .= " strftime('%Y', timestamp)";
        } else {
            $groupByClause .= " strftime('$groupByFormat', timestamp)";
        }
        $endDate = date('Y-m-d', strtotime('tomorrow'));
        $startDate = date('Y-m-d', strtotime("-$dateRange days", strtotime($endDate)));
        return "SELECT strftime('{$dateFormats[$granularity]}', timestamp) as date, AVG(ttfb) as avgTtfb, AVG(total) as avgTotal 
        FROM $tableName 
        WHERE timestamp >= '$startDate' AND timestamp < '$endDate' 
        $groupByClause 
        ORDER BY date ASC";
    }
    
    
    
    
    
    private function prepareChartData($result) {
        $chartData = [
            'labels' => [],
            'ttfbData' => [],
            'totalData' => []
        ];
    
        foreach ($result as $row) {
            $chartData['labels'][] = $row['date'];
            $chartData['ttfbData'][] = $row['avgTtfb'];
            $chartData['totalData'][] = $row['avgTotal'];
        }
    
        return $chartData;
    }
    

    private function execute($sql) {
        try {
            return $this->db->query($sql);
        } catch (PDOException $e) {
            throw new Exception("Failed to execute query: " . $e->getMessage());
        }
    }
}
