@extends('layouts.gestionnaire')

@section('title', 'تفاصيل المربي - ' . $membre->nom_complet)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.membres.index') }}">إدارة المربين</a></li>
    <li class="breadcrumb-item active">{{ $membre->nom_complet }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Member Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4">
                                    <span style="font-size: 2rem; font-weight: bold;">
                                        {{ substr($membre->nom_complet, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="mb-1">{{ $membre->nom_complet }}</h3>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-id-card me-2"></i>
                                        {{ $membre->numero_carte_nationale }}
                                    </p>
                                    <span class="badge bg-{{ $membre->statut_color }} fs-6">
                                        {{ $membre->statut_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('gestionnaire.membres.edit', $membre->id_membre) }}" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                    تعديل
                                </a>
                                <button class="btn btn-info" 
                                        onclick="changeStatus({{ $membre->id_membre }}, '{{ $membre->statut }}')">
                                    <i class="fas fa-exchange-alt"></i>
                                    تغيير الحالة
                                </button>
                                <a href="{{ route('gestionnaire.receptions.create') }}?membre={{ $membre->id_membre }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    استلام جديد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_receptions'] ?? 0 }}</div>
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
                        <div class="stats-number">{{ number_format($stats['total_litres'] ?? 0, 1) }}</div>
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
                        <div class="stats-number">{{ number_format($stats['moyenne_mensuelle'] ?? 0, 1) }}</div>
                        <div>متوسط شهري (لتر)</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card bg-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">
                            @if($stats['derniere_reception'])
                                {{ $stats['derniere_reception']->date_reception->diffForHumans() }}
                            @else
                                -
                            @endif
                        </div>
                        <div>آخر استلام</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Member Info -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        معلومات المربي
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-4 text-muted">الاسم:</div>
                        <div class="col-8"><strong>{{ $membre->nom_complet }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-muted">البريد:</div>
                        <div class="col-8">
                            <a href="mailto:{{ $membre->email }}">{{ $membre->email }}</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-muted">الهاتف:</div>
                        <div class="col-8">
                            <a href="tel:{{ $membre->telephone }}">{{ $membre->telephone }}</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-muted">البطاقة:</div>
                        <div class="col-8"><code>{{ $membre->numero_carte_nationale }}</code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-muted">العنوان:</div>
                        <div class="col-8">{{ $membre->adresse }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-muted">الحالة:</div>
                        <div class="col-8">
                            <span class="badge bg-{{ $membre->statut_color }}">
                                {{ $membre->statut_label }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-muted">تاريخ التسجيل:</div>
                        <div class="col-8">
                            <small>{{ $membre->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    
                    @if($membre->statut === 'suppression' && $membre->raison_suppression)
                        <div class="alert alert-danger mt-3">
                            <h6>سبب الحذف:</h6>
                            <p class="mb-0">{{ $membre->raison_suppression }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Receptions -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر الاستلامات
                    </h5>
                    <a href="{{ route('gestionnaire.receptions.index') }}?membre_id={{ $membre->id_membre }}" 
                       class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($receptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>رقم الاستلام</th>
                                        <th>التاريخ</th>
                                        <th>الكمية</th>
                                        <th>وقت التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receptions as $reception)
                                        <tr>
                                            <td>
                                                <a href="{{ route('gestionnaire.receptions.show', $reception->id_reception) }}" 
                                                   class="text-decoration-none">
                                                    {{ $reception->matricule_reception }}
                                                </a>
                                            </td>
                                            <td>{{ $reception->date_reception->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ number_format($reception->quantite_litres, 1) }} لتر
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $reception->created_at->format('H:i') }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد استلامات مسجلة لهذا المربي</p>
                            <a href="{{ route('gestionnaire.receptions.create') }}?membre={{ $membre->id_membre }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                تسجيل استلام جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    @if($stats['total_receptions'] > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area me-2"></i>
                            إحصائيات الاستلام الشهرية
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
    .avatar-lg {
        width: 80px;
        height: 80px;
    }
    
    .stats-card .stats-number {
        font-size: 1.8rem;
    }
    
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let currentMembreId = {{ $membre->id_membre }};

    function changeStatus(membreId, currentStatus) {
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

    // Monthly chart
    @if($stats['total_receptions'] > 0)
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        // Get monthly data via AJAX
        $.ajax({
            url: '/gestionnaire/membres/{{ $membre->id_membre }}/monthly-data',
            method: 'GET',
            success: function(data) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'كمية اللبن (لتر)',
                            data: data.quantities,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'كمية اللبن (لتر)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'الشهر'
                                }
                            }
                        }
                    }
                });
            },
            error: function() {
                $('#monthlyChart').parent().html('<p class="text-center text-muted">خطأ في تحميل البيانات</p>');
            }
        });
    @endif
</script>
@endpush