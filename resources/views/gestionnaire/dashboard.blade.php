@extends('layouts.gestionnaire')

@section('title', 'لوحة التحكم')

@section('breadcrumb')
    <li class="breadcrumb-item active">لوحة التحكم</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">مرحباً، {{ Auth::user()->nom_complet }}</h4>
                            <p class="text-muted mb-0">
                                مرحباً بك في لوحة إدارة التعاونية
                                @if(Auth::user()->cooperatives->first())
                                    - {{ Auth::user()->cooperatives->first()->nom_cooperative }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ today()->format('l، d F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success w-100 p-4">
                                <div class="text-center">
                                    <i class="fas fa-plus fa-2x mb-2"></i>
                                    <h6 class="mb-1">استلام جديد</h6>
                                    <small>تسجيل استلام لبن جديد</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('gestionnaire.membres.create') }}" class="btn btn-primary w-100 p-4">
                                <div class="text-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <h6 class="mb-1">إضافة مربي</h6>
                                    <small>تسجيل مربي جديد</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('gestionnaire.stock.create-livraison') }}" class="btn btn-info w-100 p-4">
                                <div class="text-center">
                                    <i class="fas fa-truck fa-2x mb-2"></i>
                                    <h6 class="mb-1">تسليم جديد</h6>
                                    <small>إنشاء تسليم للمصنع</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ today()->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}" 
                               class="btn btn-outline-primary w-100 p-4">
                                <div class="text-center">
                                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                                    <h6 class="mb-1">استلامات اليوم</h6>
                                    <small>عرض استلامات اليوم</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <h5 class="card-title">إدارة المربين</h5>
                    <p class="card-text text-muted">
                        إضافة وإدارة المربين المسجلين في التعاونية
                    </p>
                    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-primary">
                        الانتقال إلى المربين
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-clipboard-list fa-lg"></i>
                    </div>
                    <h5 class="card-title">إدارة الاستلام</h5>
                    <p class="card-text text-muted">
                        تسجيل ومتابعة استلامات اللبن اليومية
                    </p>
                    <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-success">
                        الانتقال إلى الاستلامات
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-warehouse fa-lg"></i>
                    </div>
                    <h5 class="card-title">إدارة المخزون</h5>
                    <p class="card-text text-muted">
                        متابعة المخزون وإدارة التسليمات للمصنع
                    </p>
                    <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-info">
                        الانتقال إلى المخزون
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        كيفية الاستخدام
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">1. إدارة المربين</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-1"></i>إضافة مربين جدد</li>
                                <li><i class="fas fa-check text-success me-1"></i>تعديل بيانات المربين</li>
                                <li><i class="fas fa-check text-success me-1"></i>إدارة حالة المربين (نشط/غير نشط)</li>
                                <li><i class="fas fa-check text-success me-1"></i>عرض إحصائيات كل مربي</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-success">2. تسجيل الاستلامات</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-1"></i>تسجيل استلام اللبن يومياً</li>
                                <li><i class="fas fa-check text-success me-1"></i>تعديل الاستلامات (خلال 7 أيام)</li>
                                <li><i class="fas fa-check text-success me-1"></i>متابعة إجمالي الاستلامات</li>
                                <li><i class="fas fa-check text-success me-1"></i>تصدير البيانات</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-info">3. إدارة المخزون</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-1"></i>متابعة المخزون اليومي</li>
                                <li><i class="fas fa-check text-success me-1"></i>إنشاء تسليمات للمصنع</li>
                                <li><i class="fas fa-check text-success me-1"></i>تتبع حالة التسليمات</li>
                                <li><i class="fas fa-check text-success me-1"></i>عرض الإحصائيات والتقارير</li>
                            </ul>
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
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
    }
    
    .card-body .btn {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Add some interactive effects
        $('.card').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Quick access shortcuts
            if (e.altKey) {
                switch(e.keyCode) {
                    case 49: // Alt + 1
                        window.location.href = '{{ route("gestionnaire.membres.index") }}';
                        break;
                    case 50: // Alt + 2
                        window.location.href = '{{ route("gestionnaire.receptions.index") }}';
                        break;
                    case 51: // Alt + 3
                        window.location.href = '{{ route("gestionnaire.stock.index") }}';
                        break;
                    case 78: // Alt + N (New reception)
                        window.location.href = '{{ route("gestionnaire.receptions.create") }}';
                        break;
                }
            }
        });

        // Show keyboard shortcuts hint
        let shortcutsShown = localStorage.getItem('shortcuts_shown');
        if (!shortcutsShown) {
            setTimeout(function() {
                alert('نصيحة: استخدم Alt + 1/2/3 للانتقال السريع بين الأقسام، Alt + N لإنشاء استلام جديد');
                localStorage.setItem('shortcuts_shown', 'true');
            }, 3000);
        }
    });
</script>
@endpush