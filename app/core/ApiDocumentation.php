<?php

/**
 * API Documentation Generator
 * 
 * Generates OpenAPI 3.0 specification for the application
 * Provides interactive API documentation
 */

class ApiDocumentation {
    
    private $baseUrl;
    private $version = '1.0.0';
    private $title = 'Perdagangan System API';
    private $description = 'Complete API documentation for Perdagangan System';
    
    public function __construct() {
        $this->baseUrl = BASE_URL;
    }
    
    /**
     * Generate complete OpenAPI specification
     */
    public function generateOpenAPISpec() {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $this->title,
                'description' => $this->description,
                'version' => $this->version,
                'contact' => [
                    'name' => 'API Support',
                    'email' => 'support@perdagangan.com'
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT'
                ]
            ],
            'servers' => [
                [
                    'url' => $this->baseUrl,
                    'description' => 'Production server'
                ],
                [
                    'url' => 'http://localhost/dagang',
                    'description' => 'Development server'
                ]
            ],
            'paths' => $this->getPaths(),
            'components' => $this->getComponents(),
            'security' => [
                [
                    'bearerAuth' => []
                ]
            ]
        ];
        
        return $spec;
    }
    
    /**
     * Get all API paths
     */
    private function getPaths() {
        return [
            // Authentication endpoints
            '/index.php?page=auth&action=login' => [
                'post' => [
                    'summary' => 'User login',
                    'description' => 'Authenticate user and return session token',
                    'tags' => ['Authentication'],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/LoginRequest'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/LoginResponse'
                                    ]
                                ]
                            ]
                        ],
                        '401' => [
                            '$ref' => '#/components/responses/Unauthorized'
                        ],
                        '422' => [
                            '$ref' => '#/components/responses/ValidationError'
                        ]
                    ]
                ]
            ],
            
            '/index.php?page=auth&action=logout' => [
                'post' => [
                    'summary' => 'User logout',
                    'description' => 'Logout user and invalidate session',
                    'tags' => ['Authentication'],
                    'responses' => [
                        '200' => [
                            'description' => 'Logout successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'status' => ['type' => 'string'],
                                            'message' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // Company endpoints
            '/index.php?page=companies' => [
                'get' => [
                    'summary' => 'Get all companies',
                    'description' => 'Retrieve list of all companies with pagination',
                    'tags' => ['Companies'],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'description' => 'Page number',
                            'schema' => [
                                'type' => 'integer',
                                'default' => 1
                            ]
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'description' => 'Number of items per page',
                            'schema' => [
                                'type' => 'integer',
                                'default' => 10
                            ]
                        ],
                        [
                            'name' => 'search',
                            'in' => 'query',
                            'description' => 'Search term',
                            'schema' => [
                                'type' => 'string'
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Companies retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/CompaniesResponse'
                                    ]
                                ]
                            ]
                        ],
                        '401' => [
                            '$ref' => '#/components/responses/Unauthorized'
                        ]
                    ]
                ],
                'post' => [
                    'summary' => 'Create new company',
                    'description' => 'Create a new company record',
                    'tags' => ['Companies'],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/CompanyCreateRequest'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Company created successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/CompanyResponse'
                                    ]
                                ]
                            ]
                        ],
                        '400' => [
                            '$ref' => '#/components/responses/ValidationError'
                        ],
                        '401' => [
                            '$ref' => '#/components/responses/Unauthorized'
                        ]
                    ]
                ]
            ],
            
            '/index.php?page=companies&action=get&id={id}' => [
                'get' => [
                    'summary' => 'Get company by ID',
                    'description' => 'Retrieve specific company details',
                    'tags' => ['Companies'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'Company ID',
                            'schema' => [
                                'type' => 'integer'
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Company retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/CompanyResponse'
                                    ]
                                ]
                            ]
                        ],
                        '404' => [
                            '$ref' => '#/components/responses/NotFound'
                        ],
                        '401' => [
                            '$ref' => '#/components/responses/Unauthorized'
                        ]
                    ]
                ]
            ],
            
            '/index.php?page=companies&action=update&id={id}' => [
                'put' => [
                    'summary' => 'Update company',
                    'description' => 'Update existing company information',
                    'tags' => ['Companies'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'Company ID',
                            'schema' => [
                                'type' => 'integer'
                            ]
                        ]
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/CompanyUpdateRequest'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Company updated successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/CompanyResponse'
                                    ]
                                ]
                            ]
                        ],
                        '400' => [
                            '$ref' => '#/components/responses/ValidationError'
                        ],
                        '404' => [
                            '$ref' => '#/components/responses/NotFound'
                        ],
                        '401' => [
                            '$ref' => '#/components/responses/Unauthorized'
                        ]
                    ]
                ]
            ],
            
            '/index.php?page=companies&action=delete&id={id}' => [
                'delete' => [
                    'summary' => 'Delete company',
                    'description' => 'Soft delete a company',
                    'tags' => ['Companies'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'Company ID',
                            'schema' => [
                                'type' => 'integer'
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Company deleted successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'status' => ['type' => 'string'],
                                            'message' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        '404' => [
                            '$ref' => '#/components/responses/NotFound'
                        ],
                        '401' => [
                            '$ref' => '#/components/responses/Unauthorized'
                        ]
                    ]
                ]
            ],
            
            // Branch endpoints
            '/index.php?page=branches' => [
                'get' => [
                    'summary' => 'Get all branches',
                    'description' => 'Retrieve list of all branches',
                    'tags' => ['Branches'],
                    'parameters' => [
                        [
                            'name' => 'company_id',
                            'in' => 'query',
                            'description' => 'Filter by company ID',
                            'schema' => [
                                'type' => 'integer'
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Branches retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/BranchesResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // Address endpoints
            '/index.php?page=address&action=getProvinces' => [
                'get' => [
                    'summary' => 'Get all provinces',
                    'description' => 'Retrieve list of all Indonesian provinces',
                    'tags' => ['Address'],
                    'responses' => [
                        '200' => [
                            'description' => 'Provinces retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ProvincesResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            '/index.php?page=address&action=getRegencies&province_id={province_id}' => [
                'get' => [
                    'summary' => 'Get regencies by province',
                    'description' => 'Retrieve regencies for a specific province',
                    'tags' => ['Address'],
                    'parameters' => [
                        [
                            'name' => 'province_id',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'Province ID',
                            'schema' => [
                                'type' => 'integer'
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Regencies retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/RegenciesResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // Settings endpoints
            '/index.php?page=settings&action=getSettings' => [
                'get' => [
                    'summary' => 'Get system settings',
                    'description' => 'Retrieve all system configuration settings',
                    'tags' => ['Settings'],
                    'responses' => [
                        '200' => [
                            'description' => 'Settings retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/SettingsResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // Audit endpoints
            '/index.php?page=audit&action=getAuditLogs' => [
                'get' => [
                    'summary' => 'Get audit logs',
                    'description' => 'Retrieve system audit logs with filtering',
                    'tags' => ['Audit'],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'description' => 'Page number',
                            'schema' => [
                                'type' => 'integer',
                                'default' => 1
                            ]
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'description' => 'Number of items per page',
                            'schema' => [
                                'type' => 'integer',
                                'default' => 50
                            ]
                        ],
                        [
                            'name' => 'category',
                            'in' => 'query',
                            'description' => 'Filter by category',
                            'schema' => [
                                'type' => 'string',
                                'enum' => ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'SECURITY']
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Audit logs retrieved successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/AuditLogsResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get OpenAPI components
     */
    private function getComponents() {
        return [
            'schemas' => [
                'LoginRequest' => [
                    'type' => 'object',
                    'required' => ['email', 'password'],
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'description' => 'User email address'
                        ],
                        'password' => [
                            'type' => 'string',
                            'format' => 'password',
                            'description' => 'User password'
                        ]
                    ]
                ],
                
                'LoginResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => [
                            'type' => 'string',
                            'example' => 'success'
                        ],
                        'message' => [
                            'type' => 'string',
                            'example' => 'Login successful'
                        ],
                        'data' => [
                            'type' => 'object',
                            'properties' => [
                                'user_id' => ['type' => 'integer'],
                                'user_name' => ['type' => 'string'],
                                'email' => ['type' => 'string'],
                                'role' => ['type' => 'string'],
                                'session_id' => ['type' => 'string']
                            ]
                        ]
                    ]
                ],
                
                'CompanyCreateRequest' => [
                    'type' => 'object',
                    'required' => ['company_name', 'company_type', 'scalability_level', 'owner_name'],
                    'properties' => [
                        'company_name' => [
                            'type' => 'string',
                            'minLength' => 3,
                            'maxLength' => 200,
                            'description' => 'Company name'
                        ],
                        'company_code' => [
                            'type' => 'string',
                            'minLength' => 2,
                            'maxLength' => 50,
                            'description' => 'Unique company code'
                        ],
                        'company_type' => [
                            'type' => 'string',
                            'enum' => ['individual', 'personal', 'warung', 'kios', 'toko_kelontong', 'minimarket', 'pengusaha_menengah', 'distributor', 'koperasi', 'perusahaan_besar', 'franchise', 'pusat'],
                            'description' => 'Type of company'
                        ],
                        'scalability_level' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 6,
                            'description' => 'Business scalability level (1-6)'
                        ],
                        'owner_name' => [
                            'type' => 'string',
                            'minLength' => 3,
                            'maxLength' => 200,
                            'description' => 'Owner name'
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'description' => 'Company email'
                        ],
                        'phone' => [
                            'type' => 'string',
                            'minLength' => 10,
                            'maxLength' => 20,
                            'description' => 'Company phone number'
                        ],
                        'address_detail' => [
                            'type' => 'string',
                            'maxLength' => 255,
                            'description' => 'Street address'
                        ],
                        'province_id' => [
                            'type' => 'integer',
                            'description' => 'Province ID'
                        ],
                        'regency_id' => [
                            'type' => 'integer',
                            'description' => 'Regency ID'
                        ],
                        'district_id' => [
                            'type' => 'integer',
                            'description' => 'District ID'
                        ],
                        'village_id' => [
                            'type' => 'integer',
                            'description' => 'Village ID'
                        ],
                        'postal_code' => [
                            'type' => 'string',
                            'maxLength' => 10,
                            'description' => 'Postal code'
                        ]
                    ]
                ],
                
                'CompanyUpdateRequest' => [
                    'type' => 'object',
                    'properties' => [
                        'company_name' => [
                            'type' => 'string',
                            'minLength' => 3,
                            'maxLength' => 200
                        ],
                        'company_code' => [
                            'type' => 'string',
                            'minLength' => 2,
                            'maxLength' => 50
                        ],
                        'company_type' => [
                            'type' => 'string',
                            'enum' => ['individual', 'personal', 'warung', 'kios', 'toko_kelontong', 'minimarket', 'pengusaha_menengah', 'distributor', 'koperasi', 'perusahaan_besar', 'franchise', 'pusat']
                        ],
                        'scalability_level' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 6
                        ],
                        'owner_name' => [
                            'type' => 'string',
                            'minLength' => 3,
                            'maxLength' => 200
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email'
                        ],
                        'phone' => [
                            'type' => 'string',
                            'minLength' => 10,
                            'maxLength' => 20
                        ],
                        'address_detail' => [
                            'type' => 'string',
                            'maxLength' => 255
                        ],
                        'province_id' => [
                            'type' => 'integer'
                        ],
                        'regency_id' => [
                            'type' => 'integer'
                        ],
                        'district_id' => [
                            'type' => 'integer'
                        ],
                        'village_id' => [
                            'type' => 'integer'
                        ],
                        'postal_code' => [
                            'type' => 'string',
                            'maxLength' => 10
                        ]
                    ]
                ],
                
                'CompanyResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => [
                            'type' => 'string',
                            'example' => 'success'
                        ],
                        'message' => [
                            'type' => 'string',
                            'example' => 'Company retrieved successfully'
                        ],
                        'data' => [
                            'type' => 'object',
                            'properties' => [
                                'id_company' => ['type' => 'integer'],
                                'company_name' => ['type' => 'string'],
                                'company_code' => ['type' => 'string'],
                                'company_type' => ['type' => 'string'],
                                'scalability_level' => ['type' => 'integer'],
                                'owner_name' => ['type' => 'string'],
                                'email' => ['type' => 'string'],
                                'phone' => ['type' => 'string'],
                                'address_detail' => ['type' => 'string'],
                                'province_name' => ['type' => 'string'],
                                'regency_name' => ['type' => 'string'],
                                'district_name' => ['type' => 'string'],
                                'village_name' => ['type' => 'string'],
                                'postal_code' => ['type' => 'string'],
                                'is_active' => ['type' => 'boolean'],
                                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                                'updated_at' => ['type' => 'string', 'format' => 'date-time']
                            ]
                        ]
                    ]
                ],
                
                'CompaniesResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string'],
                        'message' => ['type' => 'string'],
                        'data' => [
                            'type' => 'object',
                            'properties' => [
                                'companies' => [
                                    'type' => 'array',
                                    'items' => [
                                        '$ref' => '#/components/schemas/Company'
                                    ]
                                ],
                                'pagination' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'current_page' => ['type' => 'integer'],
                                        'total_pages' => ['type' => 'integer'],
                                        'total_items' => ['type' => 'integer'],
                                        'items_per_page' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                
                'Company' => [
                    'type' => 'object',
                    'properties' => [
                        'id_company' => ['type' => 'integer'],
                        'company_name' => ['type' => 'string'],
                        'company_code' => ['type' => 'string'],
                        'company_type' => ['type' => 'string'],
                        'scalability_level' => ['type' => 'integer'],
                        'owner_name' => ['type' => 'string'],
                        'email' => ['type' => 'string'],
                        'phone' => ['type' => 'string'],
                        'is_active' => ['type' => 'boolean'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time']
                    ]
                ],
                
                'BranchesResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string'],
                        'data' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/Branch'
                            ]
                        ]
                    ]
                ],
                
                'Branch' => [
                    'type' => 'object',
                    'properties' => [
                        'id_branch' => ['type' => 'integer'],
                        'branch_name' => ['type' => 'string'],
                        'branch_code' => ['type' => 'string'],
                        'company_id' => ['type' => 'integer'],
                        'company_name' => ['type' => 'string'],
                        'branch_type' => ['type' => 'string'],
                        'owner_name' => ['type' => 'string'],
                        'phone' => ['type' => 'string'],
                        'email' => ['type' => 'string'],
                        'is_active' => ['type' => 'boolean']
                    ]
                ],
                
                'ProvincesResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string'],
                        'data' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/Province'
                            ]
                        ]
                    ]
                ],
                
                'Province' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string']
                    ]
                ],
                
                'RegenciesResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string'],
                        'data' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/Regency'
                            ]
                        ]
                    ]
                ],
                
                'Regency' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'province_id' => ['type' => 'integer']
                    ]
                ],
                
                'SettingsResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string'],
                        'data' => [
                            'type' => 'object',
                            'additionalProperties' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ],
                
                'AuditLogsResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string'],
                        'data' => [
                            'type' => 'object',
                            'properties' => [
                                'logs' => [
                                    'type' => 'array',
                                    'items' => [
                                        '$ref' => '#/components/schemas/AuditLog'
                                    ]
                                ],
                                'pagination' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'current_page' => ['type' => 'integer'],
                                        'total_pages' => ['type' => 'integer'],
                                        'total_items' => ['type' => 'integer'],
                                        'items_per_page' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                
                'AuditLog' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'user_id' => ['type' => 'integer'],
                        'user_name' => ['type' => 'string'],
                        'action' => ['type' => 'string'],
                        'category' => ['type' => 'string'],
                        'entity_type' => ['type' => 'string'],
                        'entity_id' => ['type' => 'integer'],
                        'old_values' => ['type' => 'object'],
                        'new_values' => ['type' => 'object'],
                        'ip_address' => ['type' => 'string'],
                        'user_agent' => ['type' => 'string'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time']
                    ]
                ]
            ],
            
            'responses' => [
                'Unauthorized' => [
                    'description' => 'Unauthorized access',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'example' => 'error'],
                                    'message' => ['type' => 'string', 'example' => 'Unauthorized access'],
                                    'code' => ['type' => 'integer', 'example' => 401]
                                ]
                            ]
                        ]
                    ]
                ],
                
                'NotFound' => [
                    'description' => 'Resource not found',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'example' => 'error'],
                                    'message' => ['type' => 'string', 'example' => 'Resource not found'],
                                    'code' => ['type' => 'integer', 'example' => 404]
                                ]
                            ]
                        ]
                    ]
                ],
                
                'ValidationError' => [
                    'description' => 'Validation error',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'example' => 'error'],
                                    'message' => ['type' => 'string', 'example' => 'Validation failed'],
                                    'errors' => [
                                        'type' => 'object',
                                        'additionalProperties' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'string']
                                        ]
                                    ],
                                    'code' => ['type' => 'integer', 'example' => 422]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'JWT token for authentication'
                ]
            ]
        ];
    }
    
    /**
     * Export OpenAPI specification as JSON
     */
    public function exportAsJSON() {
        $spec = $this->generateOpenAPISpec();
        return json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Export OpenAPI specification as YAML
     */
    public function exportAsYAML() {
        $spec = $this->generateOpenAPISpec();
        
        if (!function_exists('yaml_emit')) {
            // Fallback to JSON if YAML extension not available
            return $this->exportAsJSON();
        }
        
        // Define constants if they don't exist
        if (!defined('YAML_UTF8_ENCODING')) {
            define('YAML_UTF8_ENCODING', 1);
        }
        if (!defined('YAML_LN_BREAK')) {
            define('YAML_LN_BREAK', 0);
        }
        
        return yaml_emit($spec, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    }
    
    /**
     * Generate code examples for different languages
     */
    public function generateCodeExamples() {
        return [
            'curl' => [
                'description' => 'cURL example',
                'code' => 'curl -X GET "' . $this->baseUrl . '/index.php?page=companies" -H "Authorization: Bearer {token}"'
            ],
            'javascript' => [
                'description' => 'JavaScript fetch example',
                'code' => 'fetch("' . $this->baseUrl . '/index.php?page=companies", {
    headers: {
        "Authorization": "Bearer {token}",
        "Content-Type": "application/json"
    }
})
.then(response => response.json())
.then(data => console.log(data));'
            ],
            'php' => [
                'description' => 'PHP cURL example',
                'code' => '$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "' . $this->baseUrl . '/index.php?page=companies");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {token}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);'
            ],
            'python' => [
                'description' => 'Python requests example',
                'code' => 'import requests

headers = {
    "Authorization": "Bearer {token}",
    "Content-Type": "application/json"
}

response = requests.get("' . $this->baseUrl . '/index.php?page=companies", headers=headers)
data = response.json()
print(data)'
            ]
        ];
    }
    
    /**
     * Get API statistics
     */
    public function getAPIStats() {
        $spec = $this->generateOpenAPISpec();
        
        $stats = [
            'total_endpoints' => 0,
            'total_schemas' => 0,
            'endpoints_by_tag' => [],
            'authentication_required' => 0,
            'public_endpoints' => 0
        ];
        
        // Count endpoints
        foreach ($spec['paths'] as $path => $methods) {
            foreach ($methods as $method => $details) {
                $stats['total_endpoints']++;
                
                // Count by tags
                if (isset($details['tags'])) {
                    foreach ($details['tags'] as $tag) {
                        if (!isset($stats['endpoints_by_tag'][$tag])) {
                            $stats['endpoints_by_tag'][$tag] = 0;
                        }
                        $stats['endpoints_by_tag'][$tag]++;
                    }
                }
                
                // Check authentication
                if (isset($details['security']) && !empty($details['security'])) {
                    $stats['authentication_required']++;
                } else {
                    $stats['public_endpoints']++;
                }
            }
        }
        
        // Count schemas
        if (isset($spec['components']['schemas'])) {
            $stats['total_schemas'] = count($spec['components']['schemas']);
        }
        
        return $stats;
    }
}
