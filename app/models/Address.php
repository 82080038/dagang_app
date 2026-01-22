<?php
/**
 * Centralized Address Model
 * Handle centralized address management with references to alamat_db
 */

require_once __DIR__ . '/../core/Model.php';

class Address extends Model {
    protected $table = 'addresses';
    protected $primaryKey = 'id_address';
    protected $fillable = [
        'street_address',
        'province_id',
        'regency_id', 
        'district_id',
        'village_id',
        'postal_code',
        'latitude',
        'longitude',
        'is_active'
    ];
    
    /**
     * Get all provinces from alamat_db
     */
    public function getProvinces() {
        try {
            $sql = "SELECT id, name FROM alamat_db.provinces ORDER BY name";
            return $this->query($sql);
        } catch (Exception $e) {
            // Fallback to local provinces table if exists
            try {
                $sql = "SELECT id_province as id, name FROM provinces ORDER BY name";
                return $this->query($sql);
            } catch (Exception $e2) {
                return [];
            }
        }
    }
    
    /**
     * Get regencies by province ID from alamat_db
     */
    public function getRegencies($provinceId) {
        try {
            $sql = "SELECT id, name FROM alamat_db.regencies WHERE province_id = :province_id ORDER BY name";
            return $this->query($sql, ['province_id' => $provinceId]);
        } catch (Exception $e) {
            try {
                $sql = "SELECT id_regency as id, name FROM regencies WHERE province_id = :province_id ORDER BY name";
                return $this->query($sql, ['province_id' => $provinceId]);
            } catch (Exception $e2) {
                return [];
            }
        }
    }
    
    /**
     * Get districts by regency ID from alamat_db
     */
    public function getDistricts($regencyId) {
        try {
            $sql = "SELECT id, name FROM alamat_db.districts WHERE regency_id = :regency_id ORDER BY name";
            return $this->query($sql, ['regency_id' => $regencyId]);
        } catch (Exception $e) {
            try {
                $sql = "SELECT id_district as id, name FROM districts WHERE regency_id = :regency_id ORDER BY name";
                return $this->query($sql, ['regency_id' => $regencyId]);
            } catch (Exception $e2) {
                return [];
            }
        }
    }
    
    /**
     * Get villages by district ID from alamat_db
     */
    public function getVillages($districtId) {
        try {
            $sql = "SELECT id, name, postal_code FROM alamat_db.villages WHERE district_id = :district_id ORDER BY name";
            return $this->query($sql, ['district_id' => $districtId]);
        } catch (Exception $e) {
            try {
                $sql = "SELECT id_village as id, name, postal_code FROM villages WHERE district_id = :district_id ORDER BY name";
                return $this->query($sql, ['district_id' => $districtId]);
            } catch (Exception $e2) {
                return [];
            }
        }
    }
    
    /**
     * Get postal code by village ID
     */
    public function getPostalCode($villageId) {
        try {
            $sql = "SELECT postal_code FROM alamat_db.villages WHERE id = :village_id LIMIT 1";
            $result = $this->queryOne($sql, ['village_id' => $villageId]);
            return $result['postal_code'] ?? null;
        } catch (Exception $e) {
            try {
                $sql = "SELECT postal_code FROM villages WHERE id_village = :village_id LIMIT 1";
                $result = $this->queryOne($sql, ['village_id' => $villageId]);
                return $result['postal_code'] ?? null;
            } catch (Exception $e2) {
                return null;
            }
        }
    }
    
    /**
     * Create new address
     */
    public function createAddress($data) {
        if ((isset($data['village_id']) && $data['village_id']) && empty($data['postal_code'])) {
            $derived = $this->getPostalCode($data['village_id']);
            if ($derived) { $data['postal_code'] = $derived; }
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $this->create($data);
    }
    
    /**
     * Update address
     */
    public function updateAddress($id, $data) {
        if ((isset($data['village_id']) && $data['village_id']) && empty($data['postal_code'])) {
            $derived = $this->getPostalCode($data['village_id']);
            if ($derived) { $data['postal_code'] = $derived; }
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }
    
    /**
     * Get address by ID with full details
     */
    public function getAddressWithDetails($id) {
        $sql = "
            SELECT 
                a.*,
                p.name as province_name,
                r.name as regency_name,
                d.name as district_name,
                v.name as village_name,
                v.postal_code as village_postal_code,
                CONCAT(
                    a.street_address, ', ',
                    v.name, ', ',
                    d.name, ', ',
                    r.name, ', ',
                    p.name,
                    IF(v.postal_code IS NOT NULL, CONCAT(' ', v.postal_code), '')
                ) as full_address
            FROM addresses a
            LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
            LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
            LEFT JOIN alamat_db.districts d ON a.district_id = d.id
            LEFT JOIN alamat_db.villages v ON a.village_id = v.id
            WHERE a.id_address = :id
        ";
        
        return $this->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Link address to entity
     */
    public function linkAddressToEntity($addressId, $entityType, $entityId, $usageType = 'primary') {
        $sql = "
            INSERT INTO address_usage (address_id, entity_type, entity_id, usage_type, is_active)
            VALUES (:address_id, :entity_type, :entity_id, :usage_type, 1)
            ON DUPLICATE KEY UPDATE
                usage_type = VALUES(usage_type),
                is_active = 1,
                updated_at = CURRENT_TIMESTAMP
        ";
        
        return $this->query($sql, [
            'address_id' => $addressId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'usage_type' => $usageType
        ]);
    }
    
    /**
     * Get entity addresses
     */
    public function getEntityAddresses($entityType, $entityId) {
        $sql = "
            SELECT 
                a.id_address,
                a.street_address,
                a.postal_code,
                p.name as province_name,
                r.name as regency_name,
                d.name as district_name,
                v.name as village_name,
                e.usage_type,
                CONCAT(
                    a.street_address, ', ',
                    v.name, ', ',
                    d.name, ', ',
                    r.name, ', ',
                    p.name,
                    IF(a.postal_code IS NOT NULL, CONCAT(' ', a.postal_code), '')
                ) as full_address
            FROM address_usage e
            JOIN addresses a ON e.address_id = a.id_address
            LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
            LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
            LEFT JOIN alamat_db.districts d ON a.district_id = d.id
            LEFT JOIN alamat_db.villages v ON a.village_id = v.id
            WHERE e.entity_type = :entity_type 
            AND e.entity_id = :entity_id 
            AND e.is_active = 1 
            AND a.is_active = 1
        ";
        
        return $this->query($sql, [
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);
    }
    
    /**
     * Search addresses
     */
    public function searchAddresses($keyword, $limit = 50) {
        $sql = "
            SELECT 
                a.id_address,
                a.street_address,
                a.postal_code,
                p.name as province_name,
                r.name as regency_name,
                d.name as district_name,
                v.name as village_name,
                CONCAT(
                    a.street_address, ', ',
                    v.name, ', ',
                    d.name, ', ',
                    r.name, ', ',
                    p.name
                ) as display_address
            FROM addresses a
            LEFT JOIN alamat_db.provinces p ON a.province_id = p.id
            LEFT JOIN alamat_db.regencies r ON a.regency_id = r.id
            LEFT JOIN alamat_db.districts d ON a.district_id = d.id
            LEFT JOIN alamat_db.villages v ON a.village_id = v.id
            WHERE a.is_active = 1
            AND (
                a.street_address LIKE :keyword
                OR v.name LIKE :keyword
                OR d.name LIKE :keyword
                OR r.name LIKE :keyword
                OR p.name LIKE :keyword
                OR a.postal_code LIKE :keyword
            )
            ORDER BY 
                CASE 
                    WHEN a.street_address LIKE :keyword THEN 1
                    WHEN v.name LIKE :keyword THEN 2
                    WHEN d.name LIKE :keyword THEN 3
                    WHEN r.name LIKE :keyword THEN 4
                    WHEN p.name LIKE :keyword THEN 5
                    ELSE 6
                END,
                v.name, r.name, p.name
            LIMIT :limit
        ";
        
        $searchKeyword = '%' . $keyword . '%';
        return $this->query($sql, [
            'keyword' => $searchKeyword,
            'limit' => $limit
        ]);
    }
    
    /**
     * Create local address tables if alamat_db is not available
     */
    public function createLocalAddressTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS provinces (
                id_province INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS regencies (
                id_regency INT PRIMARY KEY AUTO_INCREMENT,
                province_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (province_id) REFERENCES provinces(id_province)
            )",
            
            "CREATE TABLE IF NOT EXISTS districts (
                id_district INT PRIMARY KEY AUTO_INCREMENT,
                regency_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (regency_id) REFERENCES regencies(id_regency)
            )",
            
            "CREATE TABLE IF NOT EXISTS villages (
                id_village INT PRIMARY KEY AUTO_INCREMENT,
                district_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (district_id) REFERENCES districts(id_district)
            )"
        ];
        
        foreach ($tables as $sql) {
            $this->query($sql);
        }
    }
    
    /**
     * Insert sample address data for testing
     */
    public function insertSampleData() {
        // Check if data exists
        $result = $this->query("SELECT COUNT(*) as count FROM provinces");
        $check = $result[0]['count'] ?? 0;
        
        if ($check == 0) {
            // Sample provinces
            $provinces = [
                ['name' => 'DKI Jakarta'],
                ['name' => 'Jawa Barat'],
                ['name' => 'Jawa Tengah'],
                ['name' => 'Jawa Timur'],
                ['name' => 'Bali']
            ];
            
            foreach ($provinces as $province) {
                $this->query("INSERT INTO provinces (name) VALUES (:name)", $province);
            }
            
            // Sample regencies for DKI Jakarta
            $regencies = [
                ['province_id' => 1, 'name' => 'Jakarta Pusat'],
                ['province_id' => 1, 'name' => 'Jakarta Utara'],
                ['province_id' => 1, 'name' => 'Jakarta Barat'],
                ['province_id' => 1, 'name' => 'Jakarta Selatan'],
                ['province_id' => 1, 'name' => 'Jakarta Timur']
            ];
            
            foreach ($regencies as $regency) {
                $this->query("INSERT INTO regencies (province_id, name) VALUES (:province_id, :name)", $regency);
            }
            
            // Sample districts for Jakarta Pusat
            $districts = [
                ['regency_id' => 1, 'name' => 'Menteng'],
                ['regency_id' => 1, 'name' => 'Tanah Abang'],
                ['regency_id' => 1, 'name' => 'Cempaka Putih']
            ];
            
            foreach ($districts as $district) {
                $this->query("INSERT INTO districts (regency_id, name) VALUES (:regency_id, :name)", $district);
            }
            
            // Sample villages for Menteng
            $villages = [
                ['district_id' => 1, 'name' => 'Menteng'],
                ['district_id' => 1, 'name' => 'Menteng Atas']
            ];
            
            foreach ($villages as $village) {
                $this->query("INSERT INTO villages (district_id, name) VALUES (:district_id, :name)", $village);
            }
        }
    }
}
?>
