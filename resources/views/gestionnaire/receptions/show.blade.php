@extends('layouts.gestionnaire')

@section('title', 'تفاصيل الاستلام - ' . $reception->matricule_reception)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.receptions.index') }}">إدارة الاستلام</a></li>
    <li class="breadcrumb-item active">{{ $reception->matricule_reception }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Reception Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-4" 
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-1">{{ $reception->matricule_reception }}</h3>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ $reception->date_reception->format('l، d F Y') }}
                                    </p>
                                    <span class="badge bg-success fs-6">
                                        {{ $reception->quantite_formattee }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                @if($reception->created_at > now()->subDays(7))
                                    <a href="{{ route('gestionnaire.receptions.edit', $reception->id_reception) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                        تعديل
                                    </a>
                                    <button class="btn btn-danger" 
                                            onclick="deleteReception({{ $reception->id_reception }})">
                                        <i class="fas fa-trash"></i>
                                        حذف
                                    </button>
                                @endif
                                <a href="{{ route('gestionnaire.receptions.create') }}?membre={{ $reception->id_membre }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    استلام جديد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Reception Details -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                تفاصيل الاستلام
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-5 text-muted">رقم الاستلام:</div>
                                <div class="col-7">
                                    <strong class="text-primary">{{ $reception->matricule_reception }}</strong>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-5 text-muted">تاريخ الاستلام:</div>
                                <div class="col-7">
                                    <strong>{{ $reception->date_reception->format('d/m/Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $reception->date_reception->format('l') }}</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-5 text-muted">كمية اللبن:</div>
                                <div class="col-7">
                                    <span class="badge bg-success fs-6">
                                        {{ number_format($reception->quantite_litres, 2) }} لتر
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-5 text-muted">وقت التسجيل:</div>
                                <div class="col-7">
                                    <strong>{{ $reception->created_at->format('d/m/Y H:i') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $reception->created_at->diffForHumans() }}</small>
                                </div>
                            </div>

                            @if($reception->updated_at != $reception->created_at)
                                <div class="row mb-3">
                                    <div class="col-5 text-muted">آخر تحديث:</div>
                                    <div class="col-7">
                                        <small class="text-warning">
                                            <i class="fas fa-edit me-1"></i>
                                            {{ $reception->updated_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-5 text-muted">التعاونية:</div>
                                <div class="col-7">
                                    <strong>{{ $reception->cooperative->nom_cooperative }}</strong>
                                </div>
                            </div>

                            <!-- Edit Permission Info -->
                            <div class="alert alert-{{ $reception->created_at > now()->subDays(7) ? 'info' : 'warning' }} mt-3">
                                <small>
                                    <i class="fas fa-{{ $reception->created_at > now()->subDays(7) ? 'info-circle' : 'exclamation-triangle' }} me-1"></i>
                                    @if($reception->created_at > now()->subDays(7))
                                        يمكن تعديل أو حذف هذا الاستلام لأنه مسجل منذ أقل من 7 أيام
                                    @else
                                        لا يمكن تعديل أو حذف هذا الاستلام لأنه مسجل منذ أكثر من 7 أيام
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Details -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                بيانات المربي
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <span style="font-size: 1.5rem; font-weight: bold;">
                                        {{ substr($reception->membre->nom_complet, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $reception->membre->nom_complet }}</h6>
                                    <span class="badge bg-{{ $reception->membre->statut_color }}">
                                        {{ $reception->membre->statut_label }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-5 text-muted">البريد الإلكتروني:</div>
                                <div class="col-7">
                                    <a href="mailto:{{ $reception->membre->email }}">
                                        {{ $reception->membre->email }}
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-5 text-muted">الهاتف:</div>
                                <div class="col-7">
                                    <a href="tel:{{ $reception->membre->telephone }}">
                                        {{ $reception->membre->telephone }}
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-5 text-muted">رقم البطاقة الوطنية:</div>
                                <div class="col-7">
                                    <code>{{ $reception->membre->numero_carte_nationale }}</code>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-5 text-muted">العنوان:</div>
                                <div class="col-7">
                                    {{ $reception->membre->adresse }}
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4">
                                <div class="row">
                                    <div class="col-6">
                                        <a href="{{ route('gestionnaire.membres.show', $reception->membre->id_membre) }}" 
                                           class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fas fa-eye me-1"></i>
                                            عرض المربي
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('gestionnaire.receptions.index') }}?membre_id={{ $reception->membre->id_membre }}" 
                                           class="btn btn-outline-info btn-sm w-100">
                                            <i class="fas fa-history me-1"></i>
                                            استلاماته
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Receptions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                استلامات أخرى من نفس المربي
                            </h5>
                            <span class="badge bg-primary">{{ $reception->membre->nom_complet }}</span>
                        </div>
                        <div class="card-body">
                            <div id="relatedReceptions">
                                <!-- Will be loaded via AJAX -->
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">جاري التحميل...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-2"></i>
                            رجوع إلى قائمة الاستلامات
                        </a>
                        
                        <div>
                            <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>
                                استلام جديد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    
    .card:hover {
        box-shadow: 0 4px 25px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .table-sm td {
        padding: 0.5rem;
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        loadRelatedReceptions();
    });

    function deleteReception(receptionId) {
        if (confirm('هل أنت متأكد من حذف هذا الاستلام؟\nلا يمكن التراجع عن هذا الإجراء.\nسيتم تحديث المخزون تلقائياً.')) {
            showLoading();
            
            $.ajax({
                url: `/gestionnaire/receptions/${receptionId}`,
                method: 'DELETE',
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        alert(response.success);
                        window.location.href = '{{ route("gestionnaire.receptions.index") }}';
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

    function loadRelatedReceptions() {
        const membreId = {{ $reception->membre->id_membre }};
        const currentReceptionId = {{ $reception->id_reception }};
        
        $.ajax({
            url: '{{ route("gestionnaire.receptions.index") }}',
            method: 'GET',
            data: { 
                membre_id: membreId,
                ajax: 1
            },
            success: function(response) {
                // This would need to be implemented in the controller to return JSON
                // For now, we'll create a simulated response
                loadRelatedReceptionsData(membreId, currentReceptionId);
            },
            error: function() {
                $('#relatedReceptions').html('<p class="text-center text-danger">خطأ في تحميل البيانات</p>');
            }
        });
    }

    function loadRelatedReceptionsData(membreId, currentReceptionId) {
        // Simulated data loading - in a real app, this would come from the server
        const recentReceptions = [
            @php
                $relatedReceptions = \App\Models\ReceptionLait::where('id_membre', $reception->membre->id_membre)
                    ->where('id_reception', '!=', $reception->id_reception)
                    ->latest('date_reception')
                    ->take(10)
                    ->get();
            @endphp
            @foreach($relatedReceptions as $index => $related)
                {
                    id: {{ $related->id_reception }},
                    matricule: '{{ $related->matricule_reception }}',
                    date: '{{ $related->date_reception->format('Y-m-d') }}',
                    quantite: {{ $related->quantite_litres }},
                    created_at: '{{ $related->created_at->format('Y-m-d H:i') }}'
                }{{ $index < $relatedReceptions->count() - 1 ? ',' : '' }}
            @endforeach
        ];

        let html = '';

        if (recentReceptions.length > 0) {
            html = `
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>رقم الاستلام</th>
                                <th>تاريخ الاستلام</th>
                                <th>الكمية</th>
                                <th>وقت التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            recentReceptions.forEach(function(reception) {
                html += `
                    <tr>
                        <td>
                            <a href="/gestionnaire/receptions/${reception.id}" class="text-decoration-none fw-bold">
                                ${reception.matricule}
                            </a>
                        </td>
                        <td>${new Date(reception.date).toLocaleDateString('ar-MA')}</td>
                        <td>
                            <span class="badge bg-success">${parseFloat(reception.quantite).toFixed(1)} لتر</span>
                        </td>
                        <td>
                            <small class="text-muted">${reception.created_at}</small>
                        </td>
                        <td>
                            <a href="/gestionnaire/receptions/${reception.id}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            // Add link to see all receptions
            html += `
                <div class="text-center mt-3">
                    <a href="{{ route('gestionnaire.receptions.index') }}?membre_id=${membreId}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>
                        عرض جميع استلامات هذا المربي
                    </a>
                </div>
            `;
        } else {
            html = `
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد استلامات أخرى لهذا المربي</p>
                    <a href="{{ route('gestionnaire.receptions.create') }}?membre={{ $reception->membre->id_membre }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        تسجيل استلام جديد
                    </a>
                </div>
            `;
        }

        $('#relatedReceptions').html(html);
    }

    // Print functionality
    function printReception() {
        window.print();
    }

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + P to print
        if (e.ctrlKey && e.keyCode === 80) {
            e.preventDefault();
            printReception();
        }
        
        // Escape to go back to list
        if (e.keyCode === 27) {
            window.location.href = '{{ route("gestionnaire.receptions.index") }}';
        }
        
        // E to edit (if allowed)
        @if($reception->created_at > now()->subDays(7))
        if (e.keyCode === 69 && !e.ctrlKey && !e.altKey) {
            window.location.href = '{{ route("gestionnaire.receptions.edit", $reception->id_reception) }}';
        }
        @endif
    });
</script>
@endpush