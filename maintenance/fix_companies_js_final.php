<?php
// Fix all JavaScript syntax errors in companies/index.php
$clean_js = '
    <script>
    // Wait for jQuery to be loaded
    (function() {
        function initCompaniesPage() {
            loadCompanies();
            
            // Search functionality
            $("#searchCompaniesInput").on("input", function() {
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(function() {
                    loadCompanies();
                }, 300);
            });
            
            // Filter functionality
            $("#filterCompanyType").on("change", function() {
                loadCompanies();
            });
            
            // Company form submission
            $("#companyForm").on("submit", function(e) {
                e.preventDefault();
                submitCompanyForm();
            });
            
            // Delete confirmation
            $("#confirmDeleteBtn").on("click", function() {
                var companyId = $(this).data("company-id");
                $.ajax({
                    url: "index.php?page=companies&action=delete&id=" + companyId,
                    type: "POST",
                    dataType: "json",
                    success: function(response) {
                        if (response.status == "success") {
                            showNotification(response.message, "success");
                            $("#deleteModal").modal("hide");
                            loadCompanies();
                        } else {
                            showNotification("Gagal menghapus perusahaan", "error");
                        }
                    },
                    error: function() {
                        showNotification("Terjadi kesalahan", "error");
                    }
                });
            });
            
            // Reset form when modal is hidden
            $("#companyModal").on("hidden.bs.modal", function() {
                $("#companyForm")[0].reset();
                $("#company_id").val("");
                $("#companyModalTitle").text("Tambah Perusahaan Baru");
                $("#companyForm .is-invalid").removeClass("is-invalid");
                $("#companyForm .invalid-feedback").remove();
            });
        }
        
        function loadCompanies(page) {
            page = page || 1;
            var search = $("#searchCompaniesInput").val();
            var filterType = $("#filterCompanyType").val();
            
            var params = new URLSearchParams({
                page_num: page,
                q: search,
                type: filterType
            });
            
            $.ajax({
                url: "index.php?page=companies&" + params.toString(),
                type: "GET",
                dataType: "json",
                beforeSend: function() {
                    $("#companiesTableBody").html("<tr><td colspan=\"8\" class=\"text-center\"><i class=\"bi bi-spinner fa-spin\"></i> Loading...</td></tr>");
                },
                success: function(response) {
                    if (response.status == "success") {
                        renderCompaniesTable(response.data.companies);
                        renderPagination(response.data.pagination);
                    } else {
                        $("#companiesTableBody").html("<tr><td colspan=\"8\" class=\"text-center text-danger\">Gagal memuat data</td></tr>");
                    }
                },
                error: function() {
                    $("#companiesTableBody").html("<tr><td colspan=\"8\" class=\"text-center text-danger\">Terjadi kesalahan</td></tr>");
                }
            });
        }
        
        function renderCompaniesTable(companies) {
            var html = "";
            
            if (companies.length === 0) {
                html = "<tr><td colspan=\"8\" class=\"text-center text-muted\">Tidak ada data perusahaan</td></tr>";
            } else {
                companies.forEach(function(company) {
                    var statusBadge = company.is_active ? 
                        "<span class=\"badge bg-success\">Aktif</span>" : 
                        "<span class=\"badge bg-danger\">Non-aktif</span>";
                    
                    var levelBadge = "";
                    var level = company.scalability_level;
                    if (level !== null && level !== undefined && level !== "") {
                        var levelNum = parseInt(level);
                        switch(levelNum) {
                            case 1: levelBadge = "<span class=\"badge bg-primary\">Level 1</span>"; break;
                            case 2: levelBadge = "<span class=\"badge bg-info\">Level 2</span>"; break;
                            case 3: levelBadge = "<span class=\"badge bg-warning\">Level 3</span>"; break;
                            case 4: levelBadge = "<span class=\"badge bg-secondary\">Level 4</span>"; break;
                            case 5: levelBadge = "<span class=\"badge bg-dark\">Level 5</span>"; break;
                            case 6: levelBadge = "<span class=\"badge bg-danger\">Level 6</span>"; break;
                            default: levelBadge = "<span class=\"badge bg-light text-dark\">Level " + level + "</span>";
                        }
                    } else {
                        levelBadge = "<span class=\"badge bg-light text-dark\">-</span>";
                    }
                    
                    html += "<tr>" +
                        "<td>" +
                            "<div class=\"d-flex align-items-center\">" +
                                "<div class=\"me-2\">" +
                                    "<i class=\"bi bi-building text-primary\"></i>" +
                                "</div>" +
                                "<div>" +
                                    "<div class=\"fw-bold\">" + company.company_name + "</div>" +
                                    "<small class=\"text-muted\">" + (company.business_category ? company.business_category : "-") + "</small>" +
                                "</div>" +
                            "</div>" +
                        "</td>" +
                        "<td><code>" + company.company_code + "</code></td>" +
                        "<td>" + company.company_type + "</td>" +
                        "<td>" + company.owner_name + "</td>" +
                        "<td>" + (company.phone ? company.phone : "-") + "</td>" +
                        "<td>" + levelBadge + "</td>" +
                        "<td>" + statusBadge + "</td>" +
                        "<td>" +
                            "<div class=\"btn-group btn-group-sm\" role=\"group\">" +
                                "<button class=\"btn btn-outline-primary btn-sm\" onclick=\"editCompany(" + company.id_company + ")\" title=\"Edit\">" +
                                    "<i class=\"bi bi-pencil\"></i>" +
                                "</button>" +
                                "<button class=\"btn btn-outline-info btn-sm\" onclick=\"viewCompanyDetails(" + company.id_company + ")\" title=\"Detail\">" +
                                    "<i class=\"bi bi-eye\"></i>" +
                                "</button>" +
                                "<button class=\"btn btn-outline-warning btn-sm\" onclick=\"toggleCompanyStatus(" + company.id_company + ")\" title=\"Toggle Status\">" +
                                    "<i class=\"bi bi-toggle-on\"></i>" +
                                "</button>" +
                                "<button class=\"btn btn-outline-danger btn-sm\" onclick=\"deleteCompany(" + company.id_company + ", \'" + company.company_name + "\')\" title=\"Hapus\">" +
                                    "<i class=\"bi bi-trash\"></i>" +
                                "</button>" +
                            "</div>" +
                        "</td>" +
                    "</tr>";
                });
            }
            
            $("#companiesTableBody").html(html);
        }
        
        function renderPagination(pagination) {
            if (pagination.total_pages <= 1) return;
            
            var html = "<nav><ul class=\"pagination justify-content-center\">";
            
            if (pagination.current_page > 1) {
                html += "<li class=\"page-item\"><a class=\"page-link\" href=\"#\" onclick=\"loadCompanies(" + (pagination.current_page - 1) + ")\">Previous</a></li>";
            } else {
                html += "<li class=\"page-item disabled\"><span class=\"page-link\">Previous</span></li>";
            }
            
            var start = Math.max(1, pagination.current_page - 2);
            var end = Math.min(pagination.total_pages, pagination.current_page + 2);
            
            for (var i = start; i <= end; i++) {
                var active = i === pagination.current_page ? "active" : "";
                html += "<li class=\"page-item " + active + "\"><a class=\"page-link\" href=\"#\" onclick=\"loadCompanies(" + i + ")\">" + i + "</a></li>";
            }
            
            if (pagination.current_page < pagination.total_pages) {
                html += "<li class=\"page-item\"><a class=\"page-link\" href=\"#\" onclick=\"loadCompanies(" + (pagination.current_page + 1) + ")\">Next</a></li>";
            } else {
                html += "<li class=\"page-item disabled\"><span class=\"page-link\">Next</span></li>";
            }
            
            html += "</ul></nav>";
            $("#companiesPagination").html(html);
        }
        
        function submitCompanyForm() {
            var formData = $("#companyForm").serialize();
            var companyId = $("#company_id").val();
            
            // Determine if this is create or update
            var url = companyId ? "index.php?page=companies&action=update" : "index.php?page=companies&action=create";
            
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                beforeSend: function() {
                    $("#saveCompanyBtn").prop("disabled", true).html("<i class=\"bi bi-spinner fa-spin me-1\"></i> Menyimpan...");
                },
                success: function(response) {
                    if (response.status == "success") {
                        showNotification(response.message, "success");
                        $("#companyModal").modal("hide");
                        $("#companyForm")[0].reset();
                        loadCompanies();
                    } else {
                        var message = response.message ? response.message : "Terjadi kesalahan";
                        showNotification(message, "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan", "error");
                },
                complete: function() {
                    $("#saveCompanyBtn").prop("disabled", false).html("<i class=\"bi bi-save me-1\"></i> Simpan");
                }
            });
        }
        
        function editCompany(companyId) {
            $.ajax({
                url: "index.php?page=companies&action=get&id=" + companyId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        var company = response.data.company;
                        
                        $("#companyModalTitle").text("Edit Perusahaan");
                        $("#company_id").val(company.id_company);
                        $("#company_name").val(company.company_name);
                        $("#company_code").val(company.company_code);
                        $("#company_type").val(company.company_type);
                        $("#scalability_level").val(company.scalability_level);
                        $("#owner_name").val(company.owner_name);
                        $("#business_category").val(company.business_category);
                        $("#email").val(company.email);
                        $("#phone").val(company.phone);
                        
                        $("#companyModal").modal("show");
                    } else {
                        showNotification("Terjadi kesalahan", "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan", "error");
                }
            });
        }
        
        function deleteCompany(companyId, companyName) {
            $("#deleteCompanyName").text(companyName);
            $("#confirmDeleteBtn").data("company-id", companyId);
            $("#deleteModal").modal("show");
        }
        
        function refreshCompanies() {
            loadCompanies();
            showNotification("Data berhasil dimuat ulang", "info");
        }
        
        function toggleCompanyStatus(companyId) {
            $.ajax({
                url: "index.php?page=companies&action=toggle-status&id=" + companyId,
                type: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        showNotification(response.message, "success");
                        loadCompanies();
                    } else {
                        var message = response.message ? response.message : "Gagal mengubah status";
                        showNotification("Terjadi kesalahan", "error");
                    }
                },
                error: function() {
                    showNotification("Terjadi kesalahan", "error");
                }
            });
        }
        
        function viewCompanyDetails(companyId) {
            $("#companyDetailLoading").show();
            $("#companyDetailError").hide();
            $("#companyDetailContent").hide();
            $("#companyDetailModal").modal("show");
            
            $.ajax({
                url: "index.php?page=companies&action=details&id=" + companyId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status == "success") {
                        populateCompanyDetails(response.data);
                        $("#companyDetailLoading").hide();
                        $("#companyDetailContent").show();
                    } else {
                        $("#companyDetailLoading").hide();
                        $("#companyDetailError").show();
                        $("#companyDetailErrorText").text(response.message || "Gagal memuat data perusahaan");
                    }
                },
                error: function(xhr, status, error) {
                    $("#companyDetailLoading").hide();
                    $("#companyDetailError").show();
                    $("#companyDetailErrorText").text("Terjadi kesalahan: " + error);
                }
            });
        }
        
        function populateCompanyDetails(data) {
            var company = data.company;
            var statistics = data.statistics || {};
            var branches = data.branches || {};
            
            $("#detailCompanyName").text(company.company_name || "-");
            $("#detailCompanyCode").text(company.company_code || "-");
            $("#detailCompanyType").text(company.company_type || "-").removeClass().addClass("badge bg-primary");
            $("#detailOwnerName").text(company.owner_name || "-");
            $("#detailBusinessCategory").text(company.business_category || "-");
            
            var levelBadge = "";
            var level = company.scalability_level;
            if (level !== null && level !== undefined && level !== "") {
                var levelNum = parseInt(level);
                switch(levelNum) {
                    case 1: levelBadge = "bg-primary"; break;
                    case 2: levelBadge = "bg-info"; break;
                    case 3: levelBadge = "bg-warning"; break;
                    case 4: levelBadge = "bg-secondary"; break;
                    case 5: levelBadge = "bg-dark"; break;
                    case 6: levelBadge = "bg-danger"; break;
                    default: levelBadge = "bg-light text-dark";
                }
            } else {
                levelBadge = "bg-light text-dark";
            }
            $("#detailScalabilityLevel").text("Level " + (level || "-")).removeClass().addClass("badge " + levelBadge);
            
            var statusBadge = company.is_active ? "bg-success" : "bg-danger";
            var statusText = company.is_active ? "Aktif" : "Non-aktif";
            $("#detailStatus").text(statusText).removeClass().addClass("badge " + statusBadge);
            
            $("#detailBranchCount").text(statistics.total_branches || branches.branches_count || 0);
            $("#detailMemberCount").text(statistics.total_members || 0);
            $("#detailProductCount").text(statistics.total_products || 0);
            
            var email = company.email || "-";
            if (email !== "-") {
                $("#detailEmail").attr("href", "mailto:" + email).text(email);
            } else {
                $("#detailEmail").attr("href", "#").text(email);
            }
            $("#detailPhone").text(company.phone || "-");
            
            $("#detailTaxId").text(company.tax_id || "-");
            $("#detailBusinessLicense").text(company.business_license || "-");
            
            var addressHtml = "";
            if (company.full_address) {
                addressHtml = company.full_address;
            } else if (company.address) {
                addressHtml = company.address;
            } else {
                addressHtml = "Alamat tidak tersedia";
            }
            $("#detailAddress").html(addressHtml);
            
            if (statistics.total_branches > 0 || branches.branches_count > 0) {
                $("#detailBranchesList").html(
                    "<div class=\"alert alert-info\">" +
                    "<i class=\"bi bi-info-circle me-2\"></i>" +
                    "Perusahaan ini memiliki " + (statistics.total_branches || branches.branches_count || 0) + " cabang" +
                    "</div>"
                );
            } else {
                $("#detailBranchesList").html("<div class=\"text-muted\">Belum ada cabang</div>");
            }
            
            $("#editFromDetailBtn").off("click").on("click", function() {
                $("#companyDetailModal").modal("hide");
                editCompany(company.id_company);
            });
        }
        
        function showNotification(message, type) {
            // Simple alert for now - can be replaced with toast
            alert(message);
        }
        
        // Make functions globally accessible
        window.loadCompanies = loadCompanies;
        window.editCompany = editCompany;
        window.deleteCompany = deleteCompany;
        window.refreshCompanies = refreshCompanies;
        window.toggleCompanyStatus = toggleCompanyStatus;
        window.viewCompanyDetails = viewCompanyDetails;
        
        // Initialize when jQuery is ready
        if (typeof $ !== "undefined") {
            $(document).ready(function() {
                initCompaniesPage();
            });
        } else {
            var checkJQuery = setInterval(function() {
                if (typeof $ !== "undefined") {
                    clearInterval(checkJQuery);
                    $(document).ready(function() {
                        initCompaniesPage();
                    });
                }
            }, 100);
        }
    })();
    </script>';

// Read the current file
$content = file_get_contents('app/views/companies/index.php');

// Extract HTML part (everything before the broken script)
$html_part = substr($content, 0, strpos($content, '<script>'));

// Extract HTML part after script (everything after </script>)
$after_script = substr($content, strpos($content, '</script>') + 9);

// Combine with clean JavaScript
$new_content = $html_part . $clean_js . $after_script;

file_put_contents('app/views/companies/index.php', $new_content);
echo "JavaScript syntax errors fixed completely!\n";
?>
