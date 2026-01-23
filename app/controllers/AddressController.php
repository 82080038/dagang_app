<?php
/**
 * Centralized Address Controller
 * Handle AJAX requests for centralized address management
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Address.php';

class AddressController extends Controller {
    private $addressModel;
    
    public function __construct() {
        parent::__construct();
        $this->addressModel = new Address();
    }
    
    /**
     * Create new address
     */
    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Validate required fields
            $required = ['street_address', 'province_id', 'regency_id', 'district_id', 'village_id'];
            $errors = $this->validateRequired($required, $data);
            
            if (!empty($errors)) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
                return;
            }
            
            try {
                $addressId = $this->addressModel->createAddress($data);
                
                // Link to entity if provided
                $entityType = $_POST['entity_type'] ?? null;
                $entityId = $_POST['entity_id'] ?? null;
                $usageType = $_POST['usage_type'] ?? 'primary';
                
                if ($entityType && $entityId) {
                    $this->addressModel->linkAddressToEntity($addressId, $entityType, $entityId, $usageType);
                }
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Alamat berhasil dibuat',
                    'data' => [
                        'address_id' => $addressId,
                        'address' => $this->addressModel->getAddressWithDetails($addressId)
                    ]
                ]);
            } catch (Exception $e) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Gagal membuat alamat: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Update existing address
     */
    public function update() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $addressId = $_POST['address_id'] ?? null;
            
            if (!$addressId) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Address ID diperlukan'
                ]);
                return;
            }
            
            $data = $_POST;
            unset($data['address_id']); // Remove from data array
            
            try {
                $this->addressModel->updateAddress($addressId, $data);
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Alamat berhasil diperbarui',
                    'data' => [
                        'address_id' => $addressId,
                        'address' => $this->addressModel->getAddressWithDetails($addressId)
                    ]
                ]);
            } catch (Exception $e) {
                $this->json([
                    'status' => 'error',
                    'message' => 'Gagal memperbarui alamat: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Get address details
     */
    public function get() {
        $this->isAjaxRequest();
        
        $addressId = $_GET['id'] ?? null;
        
        if (!$addressId) {
            $this->json([
                'status' => 'error',
                'message' => 'Address ID diperlukan'
            ]);
            return;
        }
        
        try {
            $address = $this->addressModel->getAddressWithDetails($addressId);
            
            if ($address) {
                $this->json([
                    'status' => 'success',
                    'data' => [
                        'address' => $address
                    ]
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'Alamat tidak ditemukan'
                ]);
            }
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat detail alamat'
            ]);
        }
    }
    
    /**
     * Search addresses
     */
    public function search() {
        $this->isAjaxRequest();
        
        $keyword = $_GET['q'] ?? '';
        $limit = min($_GET['limit'] ?? 20, 50);
        
        try {
            $addresses = $this->addressModel->searchAddresses($keyword, $limit);
            
            $this->json([
                'status' => 'success',
                'data' => $addresses,
                'keyword' => $keyword,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal mencari alamat'
            ]);
        }
    }
    
    /**
     * Get entity addresses
     */
    public function getEntityAddresses() {
        $this->isAjaxRequest();
        
        $entityType = $_GET['entity_type'] ?? null;
        $entityId = $_GET['entity_id'] ?? null;
        
        if (!$entityType || !$entityId) {
            $this->json([
                'status' => 'error',
                'message' => 'Entity type dan ID diperlukan'
            ]);
            return;
        }
        
        try {
            $addresses = $this->addressModel->getEntityAddresses($entityType, $entityId);
            
            $this->json([
                'status' => 'success',
                'data' => $addresses
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat alamat entitas'
            ]);
        }
    }
    
    /**
     * Setup address tables and sample data
     */
    public function setup() {
        $this->requireAuth();
        
        try {
            // Create local address tables if needed
            $this->addressModel->createLocalAddressTables();
            
            // Insert sample data
            $this->addressModel->insertSampleData();
            
            $this->json([
                'status' => 'success',
                'message' => 'Tabel alamat terpusat berhasil dibuat dan data sample berhasil diinsert'
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal setup tabel alamat terpusat: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Setup centralized address system
     */
    public function setupCentralized() {
        $this->requireAuth();
        
        try {
            // Run the centralized migration
            $migrationFile = __DIR__ . '/../../database_migrations/create_centralized_addresses.sql';
            
            if (file_exists($migrationFile)) {
                $sql = file_get_contents($migrationFile);
                $db = Database::getInstance();
                
                // Execute migration statements
                $statements = array_filter(explode(';', $sql));
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $db->query($statement);
                    }
                }
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Sistem alamat terpusat berhasil diimplementasi'
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'File migration tidak ditemukan'
                ]);
            }
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal setup sistem alamat terpusat: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get provinces (AJAX)
     */
    public function getProvinces() {
        // Skip auth check for address data (public data)
        $this->isAjaxRequest();
        
        try {
            $provinces = $this->addressModel->getProvinces();
            
            $this->json([
                'status' => 'success',
                'data' => $provinces
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat data provinsi: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get regencies by province (AJAX)
     */
    public function getRegencies() {
        // Skip auth check for address data (public data)
        $this->isAjaxRequest();
        
        $provinceId = $_GET['province_id'] ?? null;
        
        if (!$provinceId) {
            $this->json([
                'status' => 'error',
                'message' => 'Province ID is required'
            ]);
            return;
        }
        
        try {
            $regencies = $this->addressModel->getRegencies($provinceId);
            
            $this->json([
                'status' => 'success',
                'data' => $regencies
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat data kabupaten/kota: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get districts by regency (AJAX)
     */
    public function getDistricts() {
        // Skip auth check for address data (public data)
        $this->isAjaxRequest();
        
        $regencyId = $_GET['regency_id'] ?? null;
        
        if (!$regencyId) {
            $this->json([
                'status' => 'error',
                'message' => 'Regency ID is required'
            ]);
            return;
        }
        
        try {
            $districts = $this->addressModel->getDistricts($regencyId);
            
            $this->json([
                'status' => 'success',
                'data' => $districts
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat data kecamatan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get villages by district (AJAX)
     */
    public function getVillages() {
        // Skip auth check for address data (public data)
        $this->isAjaxRequest();
        
        $districtId = $_GET['district_id'] ?? null;
        
        if (!$districtId) {
            $this->json([
                'status' => 'error',
                'message' => 'District ID is required'
            ]);
            return;
        }
        
        try {
            $villages = $this->addressModel->getVillages($districtId);
            
            $this->json([
                'status' => 'success',
                'data' => $villages
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat data desa/kelurahan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get postal code by village ID (AJAX)
     */
    public function getPostalCode() {
        // Skip auth check for postal code lookup (public data)
        $this->isAjaxRequest();
        
        $villageId = $_GET['village_id'] ?? null;
        
        if (!$villageId) {
            $this->json([
                'status' => 'error',
                'message' => 'Village ID is required'
            ]);
            return;
        }
        
        try {
            $postalCode = $this->addressModel->getPostalCode($villageId);
            
            if ($postalCode) {
                $this->json([
                    'status' => 'success',
                    'data' => [
                        'postal_code' => $postalCode
                    ]
                ]);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'Kode pos tidak ditemukan untuk desa ini'
                ]);
            }
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat kode pos: ' . $e->getMessage()
            ]);
        }
    }
}
?>
