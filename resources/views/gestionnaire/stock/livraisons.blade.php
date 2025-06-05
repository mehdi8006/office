@extends('layouts.gestionnaire')

@section('title', 'إدارة التسليمات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.stock.index') }}">إدارة المخزون</a></li>
    <li class="breadcrumb-item active">إدارة التسليمات</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_livraisons'] }}</div>
                        <div>إجمالي التسليمات</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-truck"></i>
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
                        <div class="stats-number">{{ number_format($stats['total_montant'], 0) }}</div>
                        <div>إجمالي المبلغ</div>
                        <small>درهم</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['planifiees'] }}</div>
                        <div>تسليمات مخططة</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-warning bg-opacity-10">
                                <h4 class="text-warning mb-1">{{ $stats['planifiees'] }}</h4>
                                <small class="text-muted">مخططة</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-info bg-opacity-10">
                                <h4 class="text-info mb-1">{{ $stats['validees'] }}</h4>
                                <small class="text-muted">مؤكدة</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-success bg-opacity-10">
                                <h4 class="text-success mb-1">{{ $stats['payees'] }}</h4>
                                <small class="text-muted">مدفوعة</small>
                            </div>
                        </div>
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
                    <i class="fas fa-list me-2"></i>
                    قائمة التسليمات - {{ $cooperative->nom_cooperative }}
                </h5>
                <div>
                    <a href="{{ route('gestionnaire.stock.create-livraison') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        تسليم جديد
                    </a>
                    <button class="btn btn-outline-primary" onclick="exportLivraisons()">
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
                    <form method="GET" action="{{ route('gestionnaire.stock.livraisons') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select class="form-select" name="statut">
                                <option value="">جميع الحالات</option>
                                <option value="planifiee" {{ request('statut') == 'planifiee' ? 'selected' : '' }}>مخططة</option>
                                <option value="validee" {{ request('statut') == 'validee' ? 'selected' : '' }}>مؤكدة</option>
                                <option value="payee" {{ request('statut') == 'payee' ? 'selected' : '' }}>مدفوعة</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
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
                                <a href="{{ route('gestionnaire.stock.livraisons') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <a href="{{ route('gestionnaire.stock.livraisons') }}?date_from={{ today()->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            اليوم
                        </a>
                        <a href="{{ route('gestionnaire.stock.livraisons') }}?date_from={{ now()->startOfWeek()->format('Y-m-d') }}&date_to={{ now()->endOfWeek()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            هذا الأسبوع
                        </a>
                        <a href="{{ route('gestionnaire.stock.livraisons') }}?date_from={{ now()->startOfMonth()->format('Y-m-d') }}&date_to={{ now()->endOfMonth()->format('Y-m-d') }}" 
                           class="btn btn-outline-primary btn-sm">
                            هذا الشهر
                        </a>
                        <a href="{{ route('gestionnaire.stock.livraisons') }}?statut=planifiee" 
                           class="btn btn-outline-warning btn-sm">
                            مخططة فقط
                        </a>
                        <a href="{{ route('gestionnaire.stock.livraisons') }}?statut=validee" 
                           class="btn btn-outline-info btn-sm">
                            مؤكدة فقط
                        </a>
                    </div>
                </div>
            </div>

            <!-- Livraisons Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>تاريخ التسليم</th>
                            <th>الكمية</th>
                            <th>السعر الوحدة</th>
                            <th>المبلغ الإجمالي</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($livraisons as $livraison)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $livraison->date_livraison->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $livraison->date_livraison->format('l') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        {{ $livraison->quantite_formattee }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $livraison->prix_formattee }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-success">{{ $livraison->montant_formattee }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $livraison->statut_color }} fs-6">
                                        {{ $livraison->statut_label }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $livraison->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        {{ $livraison->created_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($livraison->statut === 'planifiee')
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="updateLivraisonStatus({{ $livraison->id_livraison }}, 'validee')"
                                                    title="تأكيد التسليم">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelLivraison({{ $livraison->id_livraison }})"
                                                    title="إلغاء التسليم">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($livraison->statut === 'validee')
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="updateLivraisonStatus({{ $livraison->id_livraison }}, 'payee')"
                                                    title="تحديد كمدفوع">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                        @elseif($livraison->statut === 'payee')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i>
                                                مكتمل
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-truck fa-3x mb-3"></i>
                                        <p>لا توجد تسليمات مسجلة</p>
                                        <a href="{{ route('gestionnaire.stock.create-livraison') }}" class="btn btn-success">
                                            إنشاء تسليم جديد
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($livraisons->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $livraisons->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Card -->
    @if($livraisons->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            ملخص النتائج المعروضة
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <h6 class="mb-1">{{ $livraisons->count() }}</h6>
                                    <small class="text-muted">تسليمات معروضة</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <h6 class="mb-1">{{ number_format($livraisons->sum('quantite_litres'), 1) }}</h6>
                                    <small class="text-muted">إجمالي اللترات</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <h6 class="mb-1">{{ number_format($livraisons->avg('prix_unitaire'), 2) }}</h6>
                                    <small class="text-muted">متوسط السعر (درهم/لتر)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <h6 class="mb-1 text-success">{{ number_format($livraisons->sum('montant_total'), 0) }}</h6>
                                    <small class="text-muted">إجمالي المبلغ (درهم)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .stats-card .stats-number {
        font-size: 1.8rem;
        font-weight: bold;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
</style>
@endpush

@push('scripts')
<script>
    function updateLivraisonStatus(livraisonId, newStatus) {
        const statusNames = {
            'validee': 'تأكيد',
            'payee': 'تحديد كمدفوع'
        };
        
        const actionName = statusNames[newStatus];
        
        if (confirm(`هل أنت متأكد من ${actionName} هذا التسليم؟`)) {
            showLoading();
            
            $.ajax({
                url: `/gestionnaire/stock/livraisons/${livraisonId}/status`,
                method: 'POST',
                data: { statut: newStatus },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Show success message
                        const alertHtml = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                ${response.success}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        $('main').prepend(alertHtml);
                        
                        // Reload page after short delay
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    
                    let errorMessage = 'حدث خطأ أثناء تحديث الحالة';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            خطأ: ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('main').prepend(alertHtml);
                }
            });
        }
    }

    function cancelLivraison(livraisonId) {
        if (confirm('هل أنت متأكد من إلغاء هذا التسليم؟\n\nسيتم إرجاع الكمية إلى المخزون المتاح.\nلا يمكن التراجع عن هذا الإجراء.')) {
            showLoading();
            
            $.ajax({
                url: `/gestionnaire/stock/livraisons/${livraisonId}`,
                method: 'DELETE',
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Show success message
                        const alertHtml = `
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                ${response.success}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        $('main').prepend(alertHtml);
                        
                        // Reload page after short delay
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    
                    let errorMessage = 'حدث خطأ أثناء إلغاء التسليم';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            خطأ: ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('main').prepend(alertHtml);
                }
            });
        }
    }

    function exportLivraisons() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("gestionnaire.stock.export") }}?' + params.toString();
    }

    // Auto-submit on filter change
    $('select[name="statut"]').change(function() {
        $('form').submit();
    });

    // Quick status filter buttons
    $('.btn-outline-warning, .btn-outline-info').click(function(e) {
        if ($(this).attr('href')) {
            e.preventDefault();
            window.location.href = $(this).attr('href');
        }
    });

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // N for new livraison
        if (e.keyCode === 78 && !e.ctrlKey && !e.altKey) {
            window.location.href = '{{ route("gestionnaire.stock.create-livraison") }}';
        }
        
        // R to refresh
        if (e.keyCode === 82 && !e.ctrlKey && !e.altKey) {
            location.reload();
        }
    });

    // Bulk actions (for future implementation)
    function selectAll() {
        $('input[type="checkbox"]').prop('checked', true);
        updateBulkActions();
    }

    function selectNone() {
        $('input[type="checkbox"]').prop('checked', false);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkedCount = $('input[type="checkbox"]:checked').length;
        if (checkedCount > 0) {
            $('#bulkActions').show();
            $('#bulkCount').text(checkedCount);
        } else {
            $('#bulkActions').hide();
        }
    }

    // Auto-refresh for today's data
    @if(request('date_from') == today()->format('Y-m-d') && request('date_to') == today()->format('Y-m-d'))
        setInterval(function() {
            // Only refresh if we're viewing today's data
            location.reload();
        }, 60000); // Every minute
    @endif
</script>
@endpush