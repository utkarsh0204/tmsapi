<?php
ini_set('memory_limit', '256M');

class CSVDbImporter
{

    private const CHUNK_SIZE = 2000;

    private PDO $pdo;
    private string $tableName;
    private string $csvFile;
    private array $sampleDataType;
    private array $attrMap;
    private array $columnIndexMap;

    public function __construct($host, $dbname, $username, $password, $tableName, $csvFile)
    {
        $this->tableName = $tableName;
        $this->csvFile = $csvFile;

        try {
            $this->pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("DB Connection failed: " . $e->getMessage());
        }
    }

    public function createTableFromHeader()
    {
        if (!file_exists($this->csvFile)) {
            throw new Exception("File not found: " . $this->csvFile);
        }
        $fileReader = fopen($this->csvFile, 'r');
        if (!$fileReader) {
            throw new Exception("Unable to open CSV file");
        }
        $headers = fgetcsv($fileReader, 0, ',');
        fclose($fileReader);
        echo "Header Count: " . count($headers) . "\n";
        $this->createAttributesTable();
        $this->createIntTable();
        $this->createFloatTable();
        $this->createVarcharTable();
        $this->getAttributes();
        $this->analyzeSampleData(100);
    }


    private function inferDataType($value)
    {
        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                return 'float';
            } else {
                return 'int';
            }
        }
        return 'varchar';
    }

    private function analyzeSampleData($sampleSize = 100)
    {
        $fileReader = fopen($this->csvFile, 'r');
        if (!$fileReader) {
            throw new Exception("Unable to open CSV file");
        }
        $headers = fgetcsv($fileReader, 0, ',');
        $dataTypes = array_fill(0, count($headers), []);
        $rowCount = 0;

        while (($row = fgetcsv($fileReader, 0, ',')) !== FALSE && $rowCount < $sampleSize) {
            foreach ($row as $index => $value) {
                $dataType = $this->inferDataType($value);
                if (!in_array($dataType, $dataTypes[$index])) {
                    $dataTypes[$index][] = $dataType;
                }
            }
            $rowCount++;
        }
        fclose($fileReader);

        foreach ($headers as $index => $header) {
            $sanitizedName = $this->sanitizeName($header, $index);
            $this->columnIndexMap[$index] = $sanitizedName;
            if (in_array('varchar', $dataTypes[$index])) {
                $this->sampleDataType[$sanitizedName] = 'varchar';
            } elseif (in_array('float', $dataTypes[$index])) {
                $this->sampleDataType[$sanitizedName] = 'float';
            } else {
                $this->sampleDataType[$sanitizedName] = 'int';
            }
        }
    }
    private function getAttributes()
    {
        $query = "SELECT id,name FROM `attributes` WHERE `tablename` = :tablename";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':tablename' => $this->tableName]);
        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->attrMap = [];
        foreach ($attributes as $attr) {
            $this->attrMap[$attr['name']] = $attr['id'];
        }
        return $this->attrMap;
    }

    private function createAttributesTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `attributes` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `tablename` varchar(128) NOT NULL COMMENT 'TABLE NAME',	
                    `name` varchar(128) NOT NULL COMMENT 'Column Name',
                    `data_type` ENUM('int','float','varchar') DEFAULT NULL COMMENT 'Data Type Of Table',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_unq_tablename_name` (`tablename`,`name`)
                    )";
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute();
        echo "Attributes Table Creation Result:" . ($result ? "Success" : "Already Created") . "\n";
    }

    private function createIntTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "_int` (
                    `id` bigint NOT NULL AUTO_INCREMENT,
                    `attribute_id` int NOT NULL COMMENT 'attribute id of column',
                    `value` text DEFAULT NULL COMMENT 'Value Of Attribute	',
                    PRIMARY KEY (`id`),
                    KEY `idx_" . $this->tableName . "_int` (`attribute_id`)
                    )";
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute();
        echo "Int Table Creation Result:" . ($result ? "Success" : "Already Created") . "\n";
    }

    private function createFloatTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "_float` (
                    `id` bigint NOT NULL AUTO_INCREMENT,
                    `attribute_id` int NOT NULL COMMENT 'attribute id of column',
                    `value` text DEFAULT NULL COMMENT 'Value Of Attribute	',
                    PRIMARY KEY (`id`),
                    KEY `idx_" . $this->tableName . "_float` (`attribute_id`)
                    )";
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute();
        echo "Float Table Creation Result:" . ($result ? "Success" : "Already Created") . "\n";
    }

    private function createVarcharTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "_varchar` (
                    `id` bigint NOT NULL AUTO_INCREMENT,
                    `attribute_id` int NOT NULL COMMENT 'attribute id of column',
                    `value` text DEFAULT NULL COMMENT 'Value Of Attribute	',
                    PRIMARY KEY (`id`),
                    KEY `idx_" . $this->tableName . "_varchar` (`attribute_id`)
                    )";
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute();
        echo "Varchar Table Creation Result:" . ($result ? "Success" : "Already Created") . "\n";
    }

    private function sanitizeName($header, $index)
    {
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($header));
        if (!preg_match('/^[a-zA-Z_]/', $sanitizedName)) {
            $sanitizedName = 'column_' . $sanitizedName;
        }
        if (empty($sanitizedName)) {
            $sanitizedName = 'column_' . ($index + 1);
        }
        return substr($sanitizedName, 0, 64);
    }

    public function saveData()
    {
        $fileReader = fopen($this->csvFile, 'r');
        if (!$fileReader) {
            throw new Exception("Unable to open CSV file");
        }
        $headers = fgetcsv($fileReader, 0, ',');

        echo "Starting data import...\n";

        try {
            echo "Creating Attributes...\n";
            $this->pdo->beginTransaction();
            foreach ($headers as $index => $header) {
                $columnName = $this->sanitizeName($header, $index);
                if (!isset($this->attrMap[$columnName])) {
                    $dataType = $this->sampleDataType[$columnName] ?? 'varchar';
                    $query = "INSERT INTO `attributes` (tablename,name,data_type) VALUES (:tablename,:name,:data_type)";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([
                        ':tablename' => $this->tableName,
                        ':name' => $columnName,
                        ':data_type' => $dataType
                    ]);
                    $this->attrMap[$columnName] = $this->pdo->lastInsertId();
                }
            }
            $intData = [];
            $floatData = [];
            $varcharData = [];
            $rowCount = 0;
            $chunkSize = self::CHUNK_SIZE;
            $insertIntSQL = "INSERT INTO `" . $this->tableName . "_int` (attribute_id,value) VALUES ";
            $insertFloatSQL = "INSERT INTO `" . $this->tableName . "_float` (attribute_id,value) VALUES ";
            $insertVarcharSQL = "INSERT INTO `" . $this->tableName . "_varchar` (attribute_id,value) VALUES ";
            $placeHolders = "(?,?),";
            echo "Importing data in chunks of " . $chunkSize . "...\n";
            while (($row = fgetcsv($fileReader, 0, ',')) !== FALSE) {
                foreach ($row as $index => $value) {
                    $attributeId = $this->attrMap[$this->columnIndexMap[$index]] ?? null;
                    $dataType = $this->sampleDataType[$this->columnIndexMap[$index]] ?? 'varchar';
                    if ($dataType === 'int') {
                        $intData[] = $attributeId;
                        $intData[] = is_numeric($value) ? (int)$value : null;
                    } elseif ($dataType === 'float') {
                        $floatData[] = $attributeId;
                        $floatData[] = is_numeric($value) ? (float)$value : null;
                    } else {
                        $varcharData[] = $attributeId;
                        $varcharData[] = $value;
                    }

                    if (count($intData) / 2 % $chunkSize == 0) {
                        $this->insertChunk($insertIntSQL, $placeHolders, $intData);
                        $intData = [];
                    }
                    if (count($floatData) / 2 % $chunkSize == 0) {
                        $this->insertChunk($insertFloatSQL, $placeHolders, $floatData);
                        $floatData = [];
                    }
                    if (count($varcharData) % $chunkSize == 0) {
                        $this->insertChunk($insertVarcharSQL, $placeHolders, $varcharData);
                        $varcharData = [];
                    }
                }
                $rowCount++;
            }
            if (count($intData) > 0) {
                $this->insertChunk($insertIntSQL, $placeHolders, $intData);
            }
            if (count($floatData) > 0) {
                $this->insertChunk($insertFloatSQL, $placeHolders, $floatData);
            }
            if (count($varcharData) > 0) {
                $this->insertChunk($insertVarcharSQL, $placeHolders, $varcharData);
            }
            $this->pdo->commit();
            echo "Import completed successfully! Total rows imported:" . $rowCount . "\n";
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw new Exception("Import failed: " . $e->getMessage());
        }

        fclose($fileReader);
        return $rowCount;
    }

    private function insertChunk($insertSQl, $placeholderStr, $chunk)
    {
        if (empty($chunk)) {
            return;
        }
        $finalPlaceholders = rtrim(str_repeat($placeholderStr, count($chunk) / 2), ',');
        $insertSQL = $insertSQl . $finalPlaceholders;
        $stmt = $this->pdo->prepare($insertSQL);
        $stmt->execute($chunk);
    }
}

try {
    // Database configuration
    $host = 'hostname';
    $dbName = 'databasename';
    $userName = 'dbuser';
    $password = 'dbpassword';
    $tableName = 'csv_import';
    $csvFile = __DIR__ . '/sample_users_10000x1000.csv'; // Path to your CSV file

    echo "Process Stared" . date("Y-m-d H:i:s") . "\n";

    $importer = new CSVDbImporter($host, $dbName, $userName, $password, $tableName, $csvFile);
    $importer->createTableFromHeader();
    $importer->saveData();
    echo "Process Completed" . date("Y-m-d H:i:s") . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
