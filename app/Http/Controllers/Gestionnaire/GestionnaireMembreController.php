<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\MembreEleveur;
use App\Models\Cooperative;
use App\Models\ReceptionLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GestionnaireMembreController extends Controller
{
    /**
     * Display a listing of membres for the gestionnaire's cooperative.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $query = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                             ->with(['cooperative']);

        // Apply filters
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('numero_carte_nationale', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $membres = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = [
            'total' => MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)->count(),
            'actifs' => MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)->actif()->count(),
            'inactifs' => MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)->inactif()->count(),
            'supprimes' => MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)->supprime()->count(),
        ];

        return view('gestionnaire.membres.index', compact('membres', 'cooperative', 'stats'));
    }

    /**
     * Show the form for creating a new membre.
     */
    public function create()
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        return view('gestionnaire.membres.create', compact('cooperative'));
    }

    /**
     * Store a newly created membre in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:membres_eleveurs,email',
            'numero_carte_nationale' => 'required|string|max:20|unique:membres_eleveurs,numero_carte_nationale',
        ], [
            'nom_complet.required' => 'الاسم الكامل مطلوب',
            'adresse.required' => 'العنوان مطلوب',
            'telephone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'numero_carte_nationale.required' => 'رقم البطاقة الوطنية مطلوب',
            'numero_carte_nationale.unique' => 'رقم البطاقة الوطنية مستخدم بالفعل',
        ]);

        MembreEleveur::create([
            'id_cooperative' => $cooperative->id_cooperative,
            'nom_complet' => $request->nom_complet,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'numero_carte_nationale' => $request->numero_carte_nationale,
            'statut' => 'actif',
        ]);

        return redirect()->route('gestionnaire.membres.index')
                        ->with('success', 'تم إضافة العضو بنجاح');
    }

    /**
     * Display the specified membre.
     */
    public function show($id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $membre = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                              ->where('id_membre', $id)
                              ->firstOrFail();

        // Get membre statistics
        $stats = [
            'total_receptions' => ReceptionLait::where('id_membre', $membre->id_membre)->count(),
            'total_litres' => ReceptionLait::where('id_membre', $membre->id_membre)->sum('quantite_litres'),
            'moyenne_mensuelle' => ReceptionLait::where('id_membre', $membre->id_membre)
                                                ->whereBetween('date_reception', [now()->subMonth(), now()])
                                                ->avg('quantite_litres'),
            'derniere_reception' => ReceptionLait::where('id_membre', $membre->id_membre)
                                                 ->latest('date_reception')
                                                 ->first(),
        ];

        // Get recent receptions
        $receptions = ReceptionLait::where('id_membre', $membre->id_membre)
                                  ->with(['cooperative'])
                                  ->latest('date_reception')
                                  ->take(10)
                                  ->get();

        return view('gestionnaire.membres.show', compact('membre', 'stats', 'receptions'));
    }

    /**
     * Show the form for editing the specified membre.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $membre = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                              ->where('id_membre', $id)
                              ->firstOrFail();

        return view('gestionnaire.membres.edit', compact('membre', 'cooperative'));
    }

    /**
     * Update the specified membre in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $membre = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                              ->where('id_membre', $id)
                              ->firstOrFail();

        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => ['required', 'email', Rule::unique('membres_eleveurs')->ignore($membre->id_membre, 'id_membre')],
            'numero_carte_nationale' => ['required', 'string', 'max:20', Rule::unique('membres_eleveurs')->ignore($membre->id_membre, 'id_membre')],
        ], [
            'nom_complet.required' => 'الاسم الكامل مطلوب',
            'adresse.required' => 'العنوان مطلوب',
            'telephone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'numero_carte_nationale.required' => 'رقم البطاقة الوطنية مطلوب',
            'numero_carte_nationale.unique' => 'رقم البطاقة الوطنية مستخدم بالفعل',
        ]);

        $membre->update([
            'nom_complet' => $request->nom_complet,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'numero_carte_nationale' => $request->numero_carte_nationale,
        ]);

        return redirect()->route('gestionnaire.membres.show', $membre->id_membre)
                        ->with('success', 'تم تحديث بيانات العضو بنجاح');
    }

    /**
     * Change membre status.
     */
    public function changeStatus(Request $request, $id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $membre = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                              ->where('id_membre', $id)
                              ->firstOrFail();

        $request->validate([
            'statut' => 'required|in:actif,inactif,suppression',
            'raison_suppression' => 'required_if:statut,suppression|string|max:500',
        ]);

        if ($request->statut === 'suppression') {
            $membre->supprimer($request->raison_suppression);
            $message = 'تم حذف العضو بنجاح';
        } elseif ($request->statut === 'actif') {
            $membre->activer();
            $message = 'تم تفعيل العضو بنجاح';
        } else {
            $membre->desactiver();
            $message = 'تم إلغاء تفعيل العضو بنجاح';
        }

        return response()->json(['success' => $message]);
    }

    /**
     * Export membres to Excel.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $query = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative);

        // Apply same filters as index
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('numero_carte_nationale', 'like', "%{$search}%");
            });
        }

        $membres = $query->get();

        $filename = 'membres_' . $cooperative->nom_cooperative . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($membres) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'الاسم الكامل',
                'البريد الإلكتروني', 
                'الهاتف',
                'رقم البطاقة الوطنية',
                'العنوان',
                'الحالة',
                'تاريخ التسجيل'
            ]);

            // Data
            foreach ($membres as $membre) {
                fputcsv($file, [
                    $membre->nom_complet,
                    $membre->email,
                    $membre->telephone,
                    $membre->numero_carte_nationale,
                    $membre->adresse,
                    $membre->statut_label,
                    $membre->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}