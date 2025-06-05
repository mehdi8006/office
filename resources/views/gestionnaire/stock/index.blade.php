@extends('layouts.gestionnaire')

@section('title', 'إدارة المخزون')

@section('breadcrumb')
    <li class="breadcrumb-item active">إدارة المخزون</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Date Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-warehouse me-2"></i>
                                مخزون التعاونية - {{ $cooperative->nom_cooperative }}
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <div>
                                    <label class="form-label mb-1">التاريخ:</label>
                                    <input type="date" class="form-control" id="stockDate" 
                                           value="{{ $date }}" max="{{ today()->format('Y-m-d') }}">
                                </div>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary" onclick="changeDate(-1)">
                                        <i class="fas fa-chevron-right"></i>
                                        أمس
                                    </button>
                                    <button class="btn btn-primary" onclick="setToday()">
                                        اليوم
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="changeDate(1)" 
                                            {{ $date == today()->format('Y-m-d') ? 'disabled' : '' }}>
                                        غداً
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Stock Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ number_format($stockToday->quantite_totale, 1) }}</div>
                        <div>المخزون الإجمالي</div>
                        <small>لتر</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ number_format($stockToday->quantite_disponible, 1) }}</div>
                        <div>المخزون المتاح</div>
                        <small>لتر</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ number_format($stockToday->quantite_livree, 1) }}</div>
                        <div>المخزون المُسلّم</div>
                        <small>لتر</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ number_format($stockToday->percentage_livre, 1) }}%</div>
                        <div>نسبة التسليم</div>
                        <small>من الإجمالي</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Stock Progress -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        توزيع المخزون اليومي
                    </h5>
                </div>
                <div class="card-body">
                    @if($stockToday->quantite_totale > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>المخزون المتاح</span>
                                <span>{{ number_format($stockToday->quantite_disponible, 1) }} لتر ({{ number_format($stockToday->percentage_disponible, 1) }}%)</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $stockToday->percentage_disponible }}%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>المخزون المُسلّم</span>
                                <span>{{ number_format($stockToday->quantite_livree, 1) }} لتر ({{ number_format($stockToday->percentage_livre, 1) }}%)</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-info" 
                                     style="width: {{ $stockToday->percentage_livre }}%"></div>
                            </div>
                        </div>

                        <div class="row text-center mt-4">
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-success">{{ $stockToday->quantite_formattee_disponible }}</h6>
                                    <small class="text-muted">متاح للتسليم</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-info">{{ $stockToday->quantite_formattee_livree }}</h6>
                                    <small class="text-muted">تم تسليمه</small>
                                </div>
                            </div>
                        </div>

                        @if($stockToday->quantite_disponible > 0)
                            <div class="text-center mt-4">
                                <a href="{{ route('gestionnaire.stock.create-livraison', ['date' => $date]) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-truck me-2"></i>
                                    إنشاء تسليم جديد
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا يوجد مخزون في هذا التاريخ</p>
                            <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                تسجيل استلام جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stock Status -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        حالة المخزون
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-{{ $stockToday->statut_color }} text-center">
                        <h5>{{ $stockToday->statut_stock }}</h5>
                        <p class="mb-0">{{ $stockToday->quantite_totale_formattee }}</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h6 class="mb-1">{{ number_format($weeklyStats['total_recu'], 1) }}</h6>
                                <small class="text-muted">إجمالي الأسبوع (لتر)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h6 class="mb-1">{{ number_format($monthlyStats['moyenne_journaliere'], 1) }}</h6>
                                <small class="text-muted">متوسط يومي (لتر)</small>
                            </div>
                        </div>
                    </div>

                    @if($stockToday->quantite_totale > 0)
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>تاريخ المخزون:</span>
                                <strong>{{ $stockToday->date_stock->format('d/m/Y') }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>آخر تحديث:</span>
                                <small class="text-muted">{{ $stockToday->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-truck me-2"></i>
                        آخر التسليمات
                    </h5>
                    <a href="{{ route('gestionnaire.stock.livraisons') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i>
                        عرض جميع التسليمات
                    </a>
                </div>
                <div class="card-body">
                    @if($recentLivraisons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>تاريخ التسليم</th>
                                        <th>الكمية</th>
                                        <th>السعر الإجمالي</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLivraisons as $livraison)
                                        <tr>
                                            <td>
                                                <strong>{{ $livraison->date_livraison->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $livraison->date_livraison->format('l') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    {{ $livraison->quantite_formattee }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $livraison->montant_formattee }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $livraison->prix_formattee }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $livraison->statut_color }}">
                                                    {{ $livraison->statut_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($livraison->statut === 'planifiee')
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="updateLivraisonStatus({{ $livraison->id_livraison }}, 'validee')"
                                                                title="تأكيد">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="cancelLivraison({{ $livraison->id_livraison }})"
                                                                title="إلغاء">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @elseif($livraison->statut === 'validee')
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="updateLivraisonStatus({{ $livraison->id_livraison }}, 'payee')"
                                                                title="تحديد كمدفوع">
                                                            <i class="fas fa-dollar-sign"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد تسليمات مسجلة مؤخراً</p>
                            @if($stockToday->quantite_disponible > 0)
                                <a href="{{ route('gestionnaire.stock.create-livraison', ['date' => $date]) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    إنشاء تسليم جديد
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock History Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        تطور المخزون (آخر 30 يوم)
                    </h5>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" onclick="loadChart(7)">7 أيام</button>
                        <button class="btn btn-primary btn-sm" onclick="loadChart(30)">30 يوم</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="exportStock()">تصدير</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="stockChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let stockChart;

    $(document).ready(function() {
        loadChart(30);
        
        // Date change handler
        $('#stockDate').change(function() {
            const newDate = $(this).val();
            window.location.href = '{{ route("gestionnaire.stock.index") }}?date=' + newDate;
        });
    });

    function changeDate(days) {
        const currentDate = new Date('{{ $date }}');
        currentDate.setDate(currentDate.getDate() + days);
        
        const newDate = currentDate.toISOString().split('T')[0];
        const today = new Date().toISOString().split('T')[0];
        
        if (newDate <= today) {
            $('#stockDate').val(newDate).trigger('change');
        }
    }

    function setToday() {
        $('#stockDate').val('{{ today()->format("Y-m-d") }}').trigger('change');
    }

    function loadChart(days) {
        showLoading();
        
        $.ajax({
            url: '{{ route("gestionnaire.stock.chart-data") }}',
            method: 'GET',
            data: { days: days },
            success: function(data) {
                hideLoading();
                
                const ctx = document.getElementById('stockChart').getContext('2d');
                
                if (stockChart) {
                    stockChart.destroy();
                }
                
                stockChart = new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'الكمية (لتر)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'التاريخ'
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            },
            error: function() {
                hideLoading();
                $('#stockChart').parent().html('<p class="text-center text-muted">خطأ في تحميل البيانات</p>');
            }
        });
    }

    function updateLivraisonStatus(livraisonId, newStatus) {
        const statusNames = {
            'validee': 'تأكيد',
            'payee': 'تحديد كمدفوع'
        };
        
        if (confirm(`هل أنت متأكد من ${statusNames[newStatus]} هذا التسليم؟`)) {
            showLoading();
            
            $.ajax({
                url: `/gestionnaire/stock/livraisons/${livraisonId}/status`,
                method: 'POST',
                data: { statut: newStatus },
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
                        alert('حدث خطأ أثناء تحديث الحالة');
                    }
                }
            });
        }
    }

    function cancelLivraison(livraisonId) {
        if (confirm('هل أنت متأكد من إلغاء هذا التسليم؟\nسيتم إرجاع الكمية إلى المخزون المتاح.')) {
            showLoading();
            
            $.ajax({
                url: `/gestionnaire/stock/livraisons/${livraisonId}`,
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
                        alert('حدث خطأ أثناء إلغاء التسليم');
                    }
                }
            });
        }
    }

    function exportStock() {
        window.location.href = '{{ route("gestionnaire.stock.export") }}';
    }

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Arrow keys for date navigation
        if (e.altKey) {
            if (e.keyCode === 37) { // Alt + Left Arrow
                changeDate(-1);
            } else if (e.keyCode === 39) { // Alt + Right Arrow
                changeDate(1);
            } else if (e.keyCode === 72) { // Alt + H (Home - Today)
                setToday();
            }
        }
    });

    // Auto-refresh every 5 minutes for today's data
    @if($date == today()->format('Y-m-d'))
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes
    @endif
</script>
@endpush

@push('styles')
<style>
    .progress {
        border-radius: 10px;
    }
    
    .stats-card .stats-number {
        font-size: 1.8rem;
        font-weight: bold;
    }
    
    .card {
        transition: transform 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    #stockChart {
        max-height: 400px;
    }
</style>
@endpush