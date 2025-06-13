<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use App\Models\StockLait;
use App\Models\LivraisonUsine;
use App\Models\PaiementCooperativeUsine;
use App\Models\PaiementCooperativeEleveur;
use App\Models\PrixUnitaire;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Mois5Et6Seeder extends Seeder
{
    private $prixUnitaire;
    private $year = 2024;
    
    public function run(): void
    {
        $this->command->info('🚀 Début du seeding pour les mois 5 et 6 (Mai et Juin)...');
        
        // Récupérer le prix unitaire actuel
        $this->prixUnitaire = PrixUnitaire::getPrixActuel();
        $this->command->info("💰 Prix unitaire actuel: {$this->prixUnitaire} DH/L");
        
        // Récupérer toutes les coopératives actives
        $cooperatives = Cooperative::actif()->with('membresActifs')->get();
        $this->command->info("🏢 {$cooperatives->count()} coopératives actives trouvées");
        
        // Traitement pour Mai (mois 5)
        $this->command->info("\n📅 === TRAITEMENT DU MOIS DE MAI ===");
        $this->traiterMois($cooperatives, 5);
        
        // Traitement pour Juin (mois 6)
        $this->command->info("\n📅 === TRAITEMENT DU MOIS DE JUIN ===");
        $this->traiterMois($cooperatives, 6);
        
        $this->command->info("\n✅ Seeding terminé avec succès!");
    }
    
    private function traiterMois($cooperatives, $mois)
    {
        $nomMois = $mois == 5 ? 'Mai' : 'Juin';
        
        // 1. Générer les réceptions quotidiennes
        $this->genererReceptionsQuotidiennes($cooperatives, $mois);
        
        // 2. Mettre à jour les stocks quotidiens
        $this->mettreAJourStocks($cooperatives, $mois);
        
        // 3. Générer les livraisons usine
        $this->genererLivraisonsUsine($cooperatives, $mois);
        
        // 4. Générer les paiements
        $this->genererPaiements($cooperatives, $mois);
        
        $this->command->info("✅ {$nomMois} traité avec succès");
    }
    
    private function genererReceptionsQuotidiennes($cooperatives, $mois)
    {
        $this->command->info("📦 Génération des réceptions quotidiennes...");
        
        $startDate = Carbon::create($this->year, $mois, 1);
        $endDate = Carbon::create($this->year, $mois, 1)->endOfMonth();
        
        $totalReceptions = 0;
        
        foreach ($cooperatives as $cooperative) {
            $membresActifs = $cooperative->membresActifs;
            
            if ($membresActifs->isEmpty()) {
                continue;
            }
            
            $this->command->info("  🏢 {$cooperative->nom_cooperative} ({$membresActifs->count()} membres)");
            
            // Pour chaque jour du mois
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                // Pas de collecte le dimanche
                if ($date->dayOfWeek === 0) {
                    continue;
                }
                
                // Génération des réceptions pour chaque membre
                foreach ($membresActifs as $membre) {
                    // Probabilité de livraison : 85% (parfois les éleveurs ne livrent pas)
                    if (rand(1, 100) <= 85) {
                        $quantite = $this->genererQuantiteReception($mois, $date->dayOfWeek);
                        
                        ReceptionLait::create([
                            'id_cooperative' => $cooperative->id_cooperative,
                            'id_membre' => $membre->id_membre,
                            'date_reception' => $date->format('Y-m-d'),
                            'quantite_litres' => $quantite,
                        ]);
                        
                        $totalReceptions++;
                    }
                }
            }
        }
        
        $this->command->info("  ✅ {$totalReceptions} réceptions créées");
    }
    
    private function genererQuantiteReception($mois, $dayOfWeek)
    {
        // Quantité de base selon le mois (juin légèrement plus productive)
        $baseQuantite = $mois == 5 ? 25 : 28;
        
        // Variation selon le jour de la semaine
        $facteurJour = match($dayOfWeek) {
            1 => 1.1, // Lundi (accumulation weekend)
            2, 3, 4 => 1.0, // Mardi-Jeudi (normal)
            5 => 0.9, // Vendredi (légèrement moins)
            6 => 0.8, // Samedi (moins)
            default => 1.0
        };
        
        // Variation aléatoire individuelle ±40%
        $variationAleatoire = rand(60, 140) / 100;
        
        $quantite = $baseQuantite * $facteurJour * $variationAleatoire;
        
        // Arrondir à 0.5L près et minimum 5L
        return max(5, round($quantite * 2) / 2);
    }
    
    private function mettreAJourStocks($cooperatives, $mois)
    {
        $this->command->info("📊 Mise à jour des stocks quotidiens...");
        
        $startDate = Carbon::create($this->year, $mois, 1);
        $endDate = Carbon::create($this->year, $mois, 1)->endOfMonth();
        
        $totalStocks = 0;
        
        foreach ($cooperatives as $cooperative) {
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                // Pas de stock le dimanche (pas de collecte)
                if ($date->dayOfWeek === 0) {
                    continue;
                }
                
                StockLait::updateDailyStock(
                    $cooperative->id_cooperative,
                    $date->format('Y-m-d')
                );
                
                $totalStocks++;
            }
        }
        
        $this->command->info("  ✅ {$totalStocks} stocks mis à jour");
    }
    
    private function genererLivraisonsUsine($cooperatives, $mois)
    {
        $this->command->info("🚚 Génération des livraisons usine...");
        
        $startDate = Carbon::create($this->year, $mois, 1);
        $endDate = Carbon::create($this->year, $mois, 1)->endOfMonth();
        
        $totalLivraisons = 0;
        
        foreach ($cooperatives as $cooperative) {
            $this->command->info("  🏢 Livraisons pour {$cooperative->nom_cooperative}");
            
            // Livraisons 3 fois par semaine : Mardi, Jeudi, Samedi
            $joursLivraison = [2, 4, 6]; // Mardi, Jeudi, Samedi
            
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                if (!in_array($date->dayOfWeek, $joursLivraison)) {
                    continue;
                }
                
                // Récupérer le stock disponible pour cette date
                $stock = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                    ->whereDate('date_stock', $date->format('Y-m-d'))
                    ->first();
                
                if (!$stock || $stock->quantite_disponible <= 0) {
                    continue;
                }
                
                // Livrer entre 70% et 90% du stock disponible
                $pourcentageLivraison = rand(70, 90) / 100;
                $quantiteLivraison = $stock->quantite_disponible * $pourcentageLivraison;
                $quantiteLivraison = round($quantiteLivraison, 2);
                
                if ($quantiteLivraison >= 10) { // Minimum 10L pour livrer
                    // Créer la livraison
                    $livraison = LivraisonUsine::create([
                        'id_cooperative' => $cooperative->id_cooperative,
                        'date_livraison' => $date->format('Y-m-d'),
                        'quantite_litres' => $quantiteLivraison,
                        'statut' => 'validee', // La plupart sont validées
                    ]);
                    
                    // Mettre à jour le stock
                    $stock->livrer($quantiteLivraison);
                    
                    $totalLivraisons++;
                }
            }
        }
        
        $this->command->info("  ✅ {$totalLivraisons} livraisons créées");
    }
    
    private function genererPaiements($cooperatives, $mois)
    {
        $this->command->info("💰 Génération des paiements...");
        
        // 1. Paiements usine → coopératives (par quinzaines)
        $this->genererPaiementsUsine($cooperatives, $mois);
        
        // 2. Paiements coopératives → éleveurs (mensuels)
        $this->genererPaiementsEleveurs($cooperatives, $mois);
    }
    
    private function genererPaiementsUsine($cooperatives, $mois)
    {
        $this->command->info("  💳 Paiements usine → coopératives");
        
        $year = $this->year;
        
        // Première quinzaine (1-15)
        $debut1 = Carbon::create($year, $mois, 1);
        $fin1 = Carbon::create($year, $mois, 15);
        
        // Deuxième quinzaine (16-fin du mois)
        $debut2 = Carbon::create($year, $mois, 16);
        $fin2 = Carbon::create($year, $mois, 1)->endOfMonth();
        
        $totalPaiements = 0;
        
        foreach ($cooperatives as $cooperative) {
            // Première quinzaine
            $quantite1 = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
                ->where('statut', 'validee')
                ->whereBetween('date_livraison', [$debut1, $fin1])
                ->sum('quantite_litres');
            
            if ($quantite1 > 0) {
                PaiementCooperativeUsine::create([
                    'id_cooperative' => $cooperative->id_cooperative,
                    'date_paiement' => $fin1->format('Y-m-d'),
                    'montant' => $quantite1 * $this->prixUnitaire,
                    'prix_unitaire' => $this->prixUnitaire,
                    'quantite_litres' => $quantite1,
                    'statut' => rand(1, 10) <= 8 ? 'paye' : 'en_attente', // 80% payés
                ]);
                $totalPaiements++;
            }
            
            // Deuxième quinzaine
            $quantite2 = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
                ->where('statut', 'validee')
                ->whereBetween('date_livraison', [$debut2, $fin2])
                ->sum('quantite_litres');
            
            if ($quantite2 > 0) {
                PaiementCooperativeUsine::create([
                    'id_cooperative' => $cooperative->id_cooperative,
                    'date_paiement' => $fin2->format('Y-m-d'),
                    'montant' => $quantite2 * $this->prixUnitaire,
                    'prix_unitaire' => $this->prixUnitaire,
                    'quantite_litres' => $quantite2,
                    'statut' => rand(1, 10) <= 7 ? 'paye' : 'en_attente', // 70% payés
                ]);
                $totalPaiements++;
            }
        }
        
        $this->command->info("    ✅ {$totalPaiements} paiements usine créés");
    }
    
    private function genererPaiementsEleveurs($cooperatives, $mois)
    {
        $this->command->info("  💵 Paiements coopératives → éleveurs");
        
        $debut = Carbon::create($this->year, $mois, 1);
        $fin = Carbon::create($this->year, $mois, 1)->endOfMonth();
        
        // Prix légèrement inférieur pour les éleveurs (marge coopérative)
        $prixEleveur = $this->prixUnitaire * 0.85; // 85% du prix usine
        
        $totalPaiements = 0;
        
        foreach ($cooperatives as $cooperative) {
            $paiements = PaiementCooperativeEleveur::calculerPaiementsCooperative(
                $cooperative->id_cooperative,
                $debut->format('Y-m-d'),
                $fin->format('Y-m-d'),
                $prixEleveur
            );
            
            // Marquer quelques paiements comme payés
            foreach ($paiements as $paiement) {
                if (rand(1, 10) <= 6) { // 60% des paiements sont effectués
                    $paiement->marquerPaye();
                }
                $totalPaiements++;
            }
        }
        
        $this->command->info("    ✅ {$totalPaiements} paiements éleveurs créés");
    }
}