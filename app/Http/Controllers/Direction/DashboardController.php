<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\Utilisateur;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use App\Models\LivraisonUsine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Vérifier les permissions pour la direction
     */
    private function checkDirectionAccess()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'direction') {
            abort(403, 'Accès non autorisé. Seule la direction peut accéder à cette section.');
        }

        if (!$user->isActif()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Votre compte est inactif.');
        }
    }

    /**
     * Afficher le dashboard de la direction
     */
    public function index()
    {
        $this->checkDirectionAccess();

        // Statistiques des coopératives
        $cooperativesStats = [
            'total' => Cooperative::count(),
            'actives' => Cooperative::where('statut', 'actif')->count(),
            'inactives' => Cooperative::where('statut', 'inactif')->count(),
            'sans_responsable' => Cooperative::whereNull('responsable_id')->count(),
            'avec_responsable' => Cooperative::whereNotNull('responsable_id')->count(),
        ];

        // Statistiques des utilisateurs
        $utilisateursStats = [
            'total' => Utilisateur::count(),
            'actifs' => Utilisateur::where('statut', 'actif')->count(),
            'inactifs' => Utilisateur::where('statut', 'inactif')->count(),
            'direction' => Utilisateur::where('role', 'direction')->count(),
            'gestionnaires' => Utilisateur::where('role', 'gestionnaire')->count(),
            'usva' => Utilisateur::where('role', 'usva')->count(),
        ];

        // Statistiques des membres éleveurs
        $membresStats = [
            'total' => MembreEleveur::count(),
            'actifs' => MembreEleveur::where('statut', 'actif')->count(),
            'inactifs' => MembreEleveur::where('statut', 'inactif')->count(),
            'supprimes' => MembreEleveur::where('statut', 'suppression')->count(),
        ];

        // Statistiques de production (30 derniers jours)
        $productionStats = [
            'receptions_total' => ReceptionLait::where('date_reception', '>=', Carbon::now()->subDays(30))->sum('quantite_litres'),
            'livraisons_total' => LivraisonUsine::where('date_livraison', '>=', Carbon::now()->subDays(30))->sum('quantite_litres'),
            'receptions_count' => ReceptionLait::where('date_reception', '>=', Carbon::now()->subDays(30))->count(),
            'livraisons_count' => LivraisonUsine::where('date_livraison', '>=', Carbon::now()->subDays(30))->count(),
        ];

        // Dernières coopératives créées
        $dernieresCooperatives = Cooperative::with('responsable')
                                           ->orderBy('created_at', 'desc')
                                           ->limit(5)
                                           ->get();

        // Derniers utilisateurs créés
        $derniersUtilisateurs = Utilisateur::orderBy('created_at', 'desc')
                                          ->limit(5)
                                          ->get();

        // Coopératives sans responsable
        $cooperativesSansResponsable = Cooperative::whereNull('responsable_id')
                                                 ->where('statut', 'actif')
                                                 ->limit(5)
                                                 ->get();

        // Gestionnaires sans coopérative
        $gestionnairesSansCooperative = Utilisateur::where('role', 'gestionnaire')
                                                  ->where('statut', 'actif')
                                                  ->whereDoesntHave('cooperativeGeree')
                                                  ->limit(5)
                                                  ->get();

        return view('direction.dashboard', compact(
            'cooperativesStats',
            'utilisateursStats', 
            'membresStats',
            'productionStats',
            'dernieresCooperatives',
            'derniersUtilisateurs',
            'cooperativesSansResponsable',
            'gestionnairesSansCooperative'
        ));
    }
}