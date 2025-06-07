<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\MembreEleveur;
use App\Models\Cooperative;
use App\Models\ReceptionLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MembreEleveurController extends Controller
{
    /**
     * Display a listing of membres with filters and pagination.
     */
    public function index(Request $request)
    {
        $query = MembreEleveur::with('cooperative');

        // Filter by status
        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        // Filter by cooperative
        if ($request->filled('cooperative_id')) {
            $query->where('id_cooperative', $request->cooperative_id);
        }

        // Search by name or CIN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('numero_carte_nationale', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $membres = $query->paginate(15)->withQueryString();

        // Get cooperatives for filter dropdown
        $cooperatives = Cooperative::actif()->orderBy('nom_cooperative')->get();

        // Statistics for dashboard cards
        $stats = [
            'total' => MembreEleveur::count(),
            'actifs' => MembreEleveur::actif()->count(),
            'inactifs' => MembreEleveur::inactif()->count(),
            'supprimes' => MembreEleveur::supprime()->count(),
        ];

        return view('gestionnaire.membres.index', compact('membres', 'cooperatives', 'stats'));
    }

    /**
     * Show the form for creating a new membre.
     */
    public function create()
    {
        $cooperatives = Cooperative::actif()->orderBy('nom_cooperative')->get();
        return view('gestionnaire.membres.create', compact('cooperatives'));
    }

    /**
     * Store a newly created membre in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cooperative' => 'required|exists:cooperatives,id_cooperative',
            'nom_complet' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('membres_eleveurs')->where(function ($query) use ($request) {
                    return $query->where('id_cooperative', $request->id_cooperative);
                })
            ],
            'numero_carte_nationale' => 'required|string|max:20|unique:membres_eleveurs,numero_carte_nationale',
        ], [
            'id_cooperative.required' => 'La coopérative est requise',
            'id_cooperative.exists' => 'La coopérative sélectionnée n\'existe pas',
            'nom_complet.required' => 'Le nom complet est requis',
            'adresse.required' => 'L\'adresse est requise',
            'telephone.required' => 'Le téléphone est requis',
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email existe déjà dans cette coopérative',
            'numero_carte_nationale.required' => 'Le numéro de carte nationale est requis',
            'numero_carte_nationale.unique' => 'Ce numéro de carte nationale existe déjà',
        ]);

        try {
            $membre = MembreEleveur::create($validated);
            
            return redirect()
                ->route('gestionnaire.membres.index')
                ->with('success', 'Membre éleveur ajouté avec succès');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout du membre');
        }
    }

    /**
     * Display the specified membre with detailed information.
     */
    public function show(MembreEleveur $membre)
    {
        $membre->load('cooperative');

        // Get reception history with pagination
        $receptions = ReceptionLait::where('id_membre', $membre->id_membre)
            ->with('cooperative')
            ->latest('date_reception')
            ->paginate(10, ['*'], 'receptions_page');

        // Calculate statistics
        $stats = $this->calculateMembreStats($membre);

        // Get monthly data for chart (last 12 months)
        $monthlyData = $this->getMonthlyReceptionData($membre);

        return view('gestionnaire.membres.show', compact('membre', 'receptions', 'stats', 'monthlyData'));
    }

    /**
     * Show the form for editing the specified membre.
     */
    public function edit(MembreEleveur $membre)
    {
        $cooperatives = Cooperative::actif()->orderBy('nom_cooperative')->get();
        return view('gestionnaire.membres.edit', compact('membre', 'cooperatives'));
    }

    /**
     * Update the specified membre in storage.
     */
    public function update(Request $request, MembreEleveur $membre)
    {
        $validated = $request->validate([
            'id_cooperative' => 'required|exists:cooperatives,id_cooperative',
            'nom_complet' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('membres_eleveurs')->where(function ($query) use ($request) {
                    return $query->where('id_cooperative', $request->id_cooperative);
                })->ignore($membre->id_membre, 'id_membre')
            ],
            'numero_carte_nationale' => [
                'required',
                'string',
                'max:20',
                Rule::unique('membres_eleveurs')->ignore($membre->id_membre, 'id_membre')
            ],
        ], [
            'id_cooperative.required' => 'La coopérative est requise',
            'nom_complet.required' => 'Le nom complet est requis',
            'email.unique' => 'Cet email existe déjà dans cette coopérative',
            'numero_carte_nationale.unique' => 'Ce numéro de carte nationale existe déjà',
        ]);

        try {
            $membre->update($validated);
            
            return redirect()
                ->route('gestionnaire.membres.show', $membre)
                ->with('success', 'Membre éleveur modifié avec succès');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification du membre');
        }
    }

    /**
     * Activate the specified membre.
     */
    public function activate(MembreEleveur $membre)
    {
        try {
            $membre->activer();
            
            return redirect()->back()->with('success', 'Membre activé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'activation');
        }
    }

    /**
     * Deactivate the specified membre.
     */
    public function deactivate(MembreEleveur $membre)
    {
        try {
            $membre->desactiver();
            
            return redirect()->back()->with('success', 'Membre désactivé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la désactivation');
        }
    }

    /**
     * Remove the specified membre from storage.
     */
    public function destroy(Request $request, MembreEleveur $membre)
    {
        $request->validate([
            'raison_suppression' => 'required|string|max:500'
        ], [
            'raison_suppression.required' => 'La raison de suppression est requise'
        ]);

        try {
            $membre->supprimer($request->raison_suppression);
            
            return redirect()
                ->route('gestionnaire.membres.index')
                ->with('success', 'Membre supprimé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Restore the specified deleted membre.
     */
    public function restore(MembreEleveur $membre)
    {
        // Vérifier que le membre est bien supprimé
        if ($membre->statut !== 'suppression') {
            return redirect()->back()->with('error', 'Ce membre n\'est pas supprimé');
        }

        try {
            $membre->restaurer();
            
            return redirect()
                ->back()
                ->with('success', 'Membre restauré avec succès et réactivé');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la restauration : ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics for a membre.
     */
    private function calculateMembreStats(MembreEleveur $membre)
    {
        return [
            'total_receptions' => ReceptionLait::where('id_membre', $membre->id_membre)->count(),
            'total_quantite' => ReceptionLait::where('id_membre', $membre->id_membre)->sum('quantite_litres'),
            'moyenne_mensuelle' => ReceptionLait::where('id_membre', $membre->id_membre)
                ->where('date_reception', '>=', now()->subMonths(12))
                ->avg('quantite_litres'),
            'derniere_reception' => ReceptionLait::where('id_membre', $membre->id_membre)
                ->latest('date_reception')
                ->first()?->date_reception,
        ];
    }

    /**
     * Get monthly reception data for charts.
     */
    private function getMonthlyReceptionData(MembreEleveur $membre)
    {
        $data = ReceptionLait::where('id_membre', $membre->id_membre)
            ->where('date_reception', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(date_reception) as year'),
                DB::raw('MONTH(date_reception) as month'),
                DB::raw('SUM(quantite_litres) as total_quantite'),
                DB::raw('COUNT(*) as nombre_receptions')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Fill missing months with zeros
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            $found = $data->where('year', $year)->where('month', $month)->first();
            
            $monthlyData[] = [
                'label' => $date->format('M Y'),
                'quantite' => $found ? floatval($found->total_quantite) : 0,
                'receptions' => $found ? $found->nombre_receptions : 0,
            ];
        }

        return $monthlyData;
    }
}