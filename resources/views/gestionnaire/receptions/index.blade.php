@extends('layouts.gestionnaire')

@section('title', 'إدارة الاستلام')

@section('breadcrumb')
    <li class="breadcrumb-item active">إدارة الاستلام</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_receptions'] }}</div>
                        <div>إجمالي الاستلامات</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ number_format($stats['total_litres'], 1) }}</div>
                        <div>إجمالي اللترات</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['receptions_aujourdhui'] }}</div>
                        <div>استلامات اليوم</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ number_format($stats['litres_aujourdhui'], 1) }}</div>
                        <div>لترات اليوم</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-droplet"></i>
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
                    <i class="fas fa-clipboard-list me-2"></i>
                    سجل الاستلامات - {{ $cooperative->nom_cooperative }}
                </h5>
                <div>
                    <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        استلام جديد
                    </a>
                    <button class="btn btn-outline-primary" onclick="exportData()">
                        <i class="fas fa-download"></i>
                        تصدير
                    </button>
                    <button class="btn btn-outline-info" onclick="showDailySummary()">
                        <i class="fas fa-chart-pie"></i>
                        ملخص يومي
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <form method="GET" action="{{ route('gestionnaire.receptions.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="رقم الاستلام أو اسم المربي...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المربي</label>
                            <select class="form-select" name="membre_id">
                                <option value="">جميع المربين</option>
                                @foreach($membres as $membre)
                                    <option value="{{ $membre->id_membre }}" 
                                            {{ request('membre_id') == $membre->id_membre ? 'selected' : '' }}>
                                        {{ $membre->nom_complet }}
                                    </option>
                                @endforeach
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
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                    بحث
                                </button>
                                <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Date Filters -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ today()->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            اليوم
                        </a>
                        <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ now()->startOfWeek()->format('Y-m-d') }}&date_to={{ now()->endOfWeek()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            هذا الأسبوع
                        </a>
                        <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ now()->startOfMonth()->format('Y-m-d') }}&date_to={{ now()->endOfMonth()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            هذا الشهر
                        </a>
                        <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ now()->subDays(7)->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            آخر 7 أيام
                        </a>
                    </div>
                </div>
            </div>

            <!-- Receptions Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رقم الاستلام</th>
                            <th>المربي</th>
                            <th>تاريخ الاستلام</th>
                            <th>الكمية</th>
                            <th>وقت التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receptions as $reception)
                            <tr>
                                <td>
                                    <a href="{{ route('gestionnaire.receptions.show', $reception->id_reception) }}" 
                                       class="text-decoration-none fw-bold">
                                        {{ $reception->matricule_reception }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ substr($reception->membre->nom_complet, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('gestionnaire.membres.show', $reception->membre->id_membre) }}" 
                                               class="text-decoration-none">
                                                {{ $reception->membre->nom_complet }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $reception->membre->numero_carte_nationale }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $reception->date_reception->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $reception->date_reception->format('l') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6">
                                        {{ number_format($reception->quantite_litres, 1) }} لتر
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $reception->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        {{ $reception->created_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('gestionnaire.receptions.show', $reception->id_reception) }}" 
                                           class="btn btn-sm btn-outline-primary" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($reception->created_at > now()->subDays(7))
                                            <a href="{{ route('gestionnaire.receptions.edit', $reception->id_reception) }}" 
                                               class="btn btn-sm btn-outline-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteReception({{ $reception->id_reception }})" 
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                        <p>لا توجد استلامات مسجلة</p>
                                        <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-primary">
                                            تسجيل استلام جديد
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($receptions->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $receptions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Daily Summary Modal -->
<div class="modal fade" id="dailySummaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">الملخص اليومي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">اختر التاريخ</label>
                        <input type="date" class="form-control" id="summaryDate" value="{{ today()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary d-block" onclick="loadDailySummary()">
                            <i class="fas fa-search"></i>
                            عرض الملخص
                        </button>
                    </div>
                </div>
                <div id="summaryContent">
                    <!-- Summary will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 35px;
        height: 35px;
        font-size: 14px;
        font-weight: bold;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .stats-card .stats-number {
        font-size: 1.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function deleteReception(receptionId) {
        if (confirm('هل أنت متأكد من حذف هذا الاستلام؟\nلا يمكن التراجع عن هذا الإجراء.')) {
            showLoading();
            
            $.ajax({
                url: `/gestionnaire/receptions/${receptionId}`,
                method: 'DELETE',
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        alert(response.success);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        alert('خطأ: ' + xhr.responseJSON.error);
                    } else {
                        alert('حدث خطأ أثناء حذف الاستلام');
                    }
                }
            });
        }
    }

    function exportData() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("gestionnaire.receptions.export") }}?' + params.toString();
    }

    function showDailySummary() {
        $('#dailySummaryModal').modal('show');
        loadDailySummary(); // Load today's summary by default
    }

    function loadDailySummary() {
        const date = $('#summaryDate').val();
        
        showLoading();
        
        $.ajax({
            url: '{{ route("gestionnaire.receptions.daily-summary") }}',
            method: 'GET',
            data: { date: date },
            success: function(response) {
                hideLoading();
                
                const summary = response.summary;
                const receptions = response.receptions;
                
                let html = `
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-primary text-white rounded">
                                <h4>${summary.total_receptions}</h4>
                                <small>إجمالي الاستلامات</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-success text-white rounded">
                                <h4>${parseFloat(summary.total_litres).toFixed(1)}</h4>
                                <small>إجمالي اللترات</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-info text-white rounded">
                                <h4>${parseFloat(summary.moyenne_litres || 0).toFixed(1)}</h4>
                                <small>متوسط الاستلام</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-warning text-white rounded">
                                <h4>${parseFloat(summary.max_litres || 0).toFixed(1)}</h4>
                                <small>أكبر استلام</small>
                            </div>
                        </div>
                    </div>
                `;
                
                if (receptions.length > 0) {
                    html += `
                        <h6>تفاصيل الاستلامات:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>رقم الاستلام</th>
                                        <th>المربي</th>
                                        <th>الكمية</th>
                                        <th>الوقت</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    receptions.forEach(function(reception) {
                        html += `
                            <tr>
                                <td>${reception.matricule_reception}</td>
                                <td>${reception.membre.nom_complet}</td>
                                <td><span class="badge bg-success">${parseFloat(reception.quantite_litres).toFixed(1)} لتر</span></td>
                                <td><small>${new Date(reception.created_at).toLocaleTimeString('ar-MA')}</small></td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    html += '<p class="text-center text-muted">لا توجد استلامات في هذا التاريخ</p>';
                }
                
                $('#summaryContent').html(html);
            },
            error: function(xhr) {
                hideLoading();
                $('#summaryContent').html('<p class="text-center text-danger">خطأ في تحميل البيانات</p>');
            }
        });
    }

    // Auto-submit on filter change
    $('select[name="membre_id"]').change(function() {
        $('form').submit();
    });

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

    // Real-time updates every 30 seconds for today's receptions
    @if(request('date_from') == today()->format('Y-m-d') && request('date_to') == today()->format('Y-m-d'))
        setInterval(function() {
            // Only refresh if we're viewing today's data
            location.reload();
        }, 30000);
    @endif
</script>
@endpush