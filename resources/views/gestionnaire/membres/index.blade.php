@extends('layouts.gestionnaire')

@section('title', 'إدارة المربين')

@section('breadcrumb')
    <li class="breadcrumb-item active">إدارة المربين</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total'] }}</div>
                        <div>إجمالي المربين</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['actifs'] }}</div>
                        <div>المربين النشطين</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['inactifs'] }}</div>
                        <div>المربين غير النشطين</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['supprimes'] }}</div>
                        <div>المربين المحذوفين</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    قائمة المربين - {{ $cooperative->nom_cooperative }}
                </h5>
                <div>
                    <a href="{{ route('gestionnaire.membres.create') }}" class="btn btn-light">
                        <i class="fas fa-plus"></i>
                        إضافة مربي جديد
                    </a>
                    <button class="btn btn-outline-light" onclick="exportData()">
                        <i class="fas fa-download"></i>
                        تصدير
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <form method="GET" action="{{ route('gestionnaire.membres.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="اسم، بريد إلكتروني، رقم بطاقة...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select class="form-select" name="statut">
                                <option value="">جميع الحالات</option>
                                <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>نشط</option>
                                <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>غير نشط</option>
                                <option value="suppression" {{ request('statut') == 'suppression' ? 'selected' : '' }}>محذوف</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                    بحث
                                </button>
                                <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Members Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الاسم الكامل</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>رقم البطاقة الوطنية</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($membres as $membre)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            {{ substr($membre->nom_complet, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $membre->nom_complet }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $membre->email }}</td>
                                <td>{{ $membre->telephone }}</td>
                                <td>
                                    <code>{{ $membre->numero_carte_nationale }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $membre->statut_color }}">
                                        {{ $membre->statut_label }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $membre->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('gestionnaire.membres.show', $membre->id_membre) }}" 
                                           class="btn btn-sm btn-outline-primary" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('gestionnaire.membres.edit', $membre->id_membre) }}" 
                                           class="btn btn-sm btn-outline-warning" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="changeStatus({{ $membre->id_membre }}, '{{ $membre->statut }}')" 
                                                title="تغيير الحالة">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p>لا توجد مربين مسجلين</p>
                                        <a href="{{ route('gestionnaire.membres.create') }}" class="btn btn-primary">
                                            إضافة مربي جديد
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($membres->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $membres->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تغيير حالة المربي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الحالة الجديدة</label>
                        <select class="form-select" name="statut" id="statusSelect" required>
                            <option value="">اختر الحالة</option>
                            <option value="actif">نشط</option>
                            <option value="inactif">غير نشط</option>
                            <option value="suppression">محذوف</option>
                        </select>
                    </div>
                    <div class="mb-3" id="reasonDiv" style="display: none;">
                        <label class="form-label">سبب الحذف</label>
                        <textarea class="form-control" name="raison_suppression" rows="3" 
                                  placeholder="اذكر سبب حذف المربي..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 16px;
        font-weight: bold;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endpush

@push('scripts')
<script>
    let currentMembreId = null;

    function changeStatus(membreId, currentStatus) {
        currentMembreId = membreId;
        
        // Reset form
        $('#statusForm')[0].reset();
        $('#reasonDiv').hide();
        
        // Disable current status option
        $('#statusSelect option').prop('disabled', false);
        $('#statusSelect option[value="' + currentStatus + '"]').prop('disabled', true);
        
        $('#statusModal').modal('show');
    }

    // Show/hide reason textarea
    $('#statusSelect').change(function() {
        if ($(this).val() === 'suppression') {
            $('#reasonDiv').show();
            $('textarea[name="raison_suppression"]').prop('required', true);
        } else {
            $('#reasonDiv').hide();
            $('textarea[name="raison_suppression"]').prop('required', false);
        }
    });

    // Handle status form submission
    $('#statusForm').submit(function(e) {
        e.preventDefault();
        
        if (!currentMembreId) return;
        
        const formData = new FormData(this);
        
        showLoading();
        
        $.ajax({
            url: `/gestionnaire/membres/${currentMembreId}/status`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                $('#statusModal').modal('hide');
                
                if (response.success) {
                    alert(response.success);
                    location.reload();
                }
            },
            error: function(xhr) {
                hideLoading();
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert('خطأ: ' + xhr.responseJSON.message);
                } else {
                    alert('حدث خطأ أثناء تغيير الحالة');
                }
            }
        });
    });

    function exportData() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("gestionnaire.membres.export") }}?' + params.toString();
    }

    // Enhanced search functionality
    let searchTimeout;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        
        if (searchTerm.length >= 3 || searchTerm.length === 0) {
            searchTimeout = setTimeout(function() {
                $('form').submit();
            }, 500);
        }
    });

    // Auto-submit on filter change
    $('select[name="statut"]').change(function() {
        $('form').submit();
    });
</script>
@endpush