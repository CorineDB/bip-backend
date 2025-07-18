<?php

namespace Database\Seeders;

use App\Models\Commune;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommuneSeeder extends Seeder
{
    public function run(): void
    {

        // Supprime toutes les lignes de la table
        Commune::truncate();

        $communes = [
            // Département Alibori (6 communes)
            ['code' => 'AL-BAN', 'nom' => 'Banikoara', 'slug' => 'banikoara', 'departement_code' => 'AL'],
            ['code' => 'AL-GOG', 'nom' => 'Gogounou', 'slug' => 'gogounou', 'departement_code' => 'AL'],
            ['code' => 'AL-KAR', 'nom' => 'Karimama', 'slug' => 'karimama', 'departement_code' => 'AL'],
            ['code' => 'AL-KER', 'nom' => 'Kérou', 'slug' => 'kerou', 'departement_code' => 'AL'],
            ['code' => 'AL-MAL', 'nom' => 'Malanville', 'slug' => 'malanville', 'departement_code' => 'AL'],
            ['code' => 'AL-SEG', 'nom' => 'Ségbana', 'slug' => 'segbana', 'departement_code' => 'AL'],

            // Département Atacora (9 communes)
            ['code' => 'AK-BOU', 'nom' => 'Boukoumbé', 'slug' => 'boukoumbe', 'departement_code' => 'AK'],
            ['code' => 'AK-COB', 'nom' => 'Cobly', 'slug' => 'cobly', 'departement_code' => 'AK'],
            ['code' => 'AK-KER', 'nom' => 'Kérou', 'slug' => 'kerou-atacora', 'departement_code' => 'AK'],
            ['code' => 'AK-KOU', 'nom' => 'Kouandé', 'slug' => 'kouande', 'departement_code' => 'AK'],
            ['code' => 'AK-MAT', 'nom' => 'Matéri', 'slug' => 'materi', 'departement_code' => 'AK'],
            ['code' => 'AK-NAT', 'nom' => 'Natitingou', 'slug' => 'natitingou', 'departement_code' => 'AK'],
            ['code' => 'AK-PEH', 'nom' => 'Péhunco', 'slug' => 'pehunco', 'departement_code' => 'AK'],
            ['code' => 'AK-TAN', 'nom' => 'Tanguiéta', 'slug' => 'tanguieta', 'departement_code' => 'AK'],
            ['code' => 'AK-TOU', 'nom' => 'Toucountouna', 'slug' => 'toucountouna', 'departement_code' => 'AK'],

            // Département Atlantique (8 communes)
            ['code' => 'AT-ABM', 'nom' => 'Abomey-Calavi', 'slug' => 'abomey-calavi', 'departement_code' => 'AT'],
            ['code' => 'AT-ALL', 'nom' => 'Allada', 'slug' => 'allada', 'departement_code' => 'AT'],
            ['code' => 'AT-KPO', 'nom' => 'Kpomassè', 'slug' => 'kpomasse', 'departement_code' => 'AT'],
            ['code' => 'AT-OUI', 'nom' => 'Ouidah', 'slug' => 'ouidah', 'departement_code' => 'AT'],
            ['code' => 'AT-SOA', 'nom' => 'Sô-Ava', 'slug' => 'so-ava', 'departement_code' => 'AT'],
            ['code' => 'AT-TOR', 'nom' => 'Tori-Bossito', 'slug' => 'tori-bossito', 'departement_code' => 'AT'],
            ['code' => 'AT-TOF', 'nom' => 'Toffo', 'slug' => 'toffo', 'departement_code' => 'AT'],
            ['code' => 'AT-ZE', 'nom' => 'Zè', 'slug' => 'ze', 'departement_code' => 'AT'],

            // Département Borgou (9 communes)
            ['code' => 'BO-BEM', 'nom' => 'Bembèrèkè', 'slug' => 'bembereke', 'departement_code' => 'BO'],
            ['code' => 'BO-KAL', 'nom' => 'Kalalé', 'slug' => 'kalale', 'departement_code' => 'BO'],
            ['code' => 'BO-KAN', 'nom' => 'Kandi', 'slug' => 'kandi', 'departement_code' => 'BO'],
            ['code' => 'BO-NDA', 'nom' => 'N\'Dali', 'slug' => 'ndali', 'departement_code' => 'BO'],
            ['code' => 'BO-NIK', 'nom' => 'Nikki', 'slug' => 'nikki', 'departement_code' => 'BO'],
            ['code' => 'BO-PAR', 'nom' => 'Parakou', 'slug' => 'parakou', 'departement_code' => 'BO'],
            ['code' => 'BO-PER', 'nom' => 'Pèrèrè', 'slug' => 'perere', 'departement_code' => 'BO'],
            ['code' => 'BO-SIN', 'nom' => 'Sinendé', 'slug' => 'sinende', 'departement_code' => 'BO'],
            ['code' => 'BO-TCH', 'nom' => 'Tchaourou', 'slug' => 'tchaourou', 'departement_code' => 'BO'],
            ['Bembèrèkè', 'slug' => 'bembereke', 'departement_code' => 'BO'],
            ['code' => 'BO-KAL', 'nom' => 'Kalalé', 'slug' => 'kalale', 'departement_code' => 'BO'],
            ['code' => 'BO-KAN', 'nom' => 'Kandi', 'slug' => 'kandi', 'departement_code' => 'BO'],
            ['code' => 'BO-NDA', 'nom' => 'N\'Dali', 'slug' => 'ndali', 'departement_code' => 'BO'],
            ['code' => 'BO-NIK', 'nom' => 'Nikki', 'slug' => 'nikki', 'departement_code' => 'BO'],
            ['code' => 'BO-PAR', 'nom' => 'Parakou', 'slug' => 'parakou', 'departement_code' => 'BO'],
            ['code' => 'BO-PER', 'nom' => 'Pèrèrè', 'slug' => 'perere', 'departement_code' => 'BO'],
            ['code' => 'BO-SIN', 'nom' => 'Sinendé', 'slug' => 'sinende', 'departement_code' => 'BO'],
            ['code' => 'BO-TCH', 'nom' => 'Tchaourou', 'slug' => 'tchaourou', 'departement_code' => 'BO'],

            // Département Collines (6 communes)
            ['code' => 'CO-BAN', 'nom' => 'Bantè', 'slug' => 'bante', 'departement_code' => 'CO'],
            ['code' => 'CO-DAS', 'nom' => 'Dassa-Zoumè', 'slug' => 'dassa-zoume', 'departement_code' => 'CO'],
            ['code' => 'CO-GLA', 'nom' => 'Glazoué', 'slug' => 'glazoue', 'departement_code' => 'CO'],
            ['code' => 'CO-OUE', 'nom' => 'Ouèssè', 'slug' => 'ouesse', 'departement_code' => 'CO'],
            ['code' => 'CO-SAV', 'nom' => 'Savé', 'slug' => 'save', 'departement_code' => 'CO'],
            ['code' => 'CO-SAL', 'nom' => 'Savalou', 'slug' => 'savalou', 'departement_code' => 'CO'],

            // Département Donga (4 communes)
            ['code' => 'DO-BAS', 'nom' => 'Bassila', 'slug' => 'bassila', 'departement_code' => 'DO'],
            ['code' => 'DO-COP', 'nom' => 'Copargo', 'slug' => 'copargo', 'departement_code' => 'DO'],
            ['code' => 'DO-DJO', 'nom' => 'Djougou', 'slug' => 'djougou', 'departement_code' => 'DO'],
            ['code' => 'DO-OUA', 'nom' => 'Ouaké', 'slug' => 'ouake', 'departement_code' => 'DO'],

            // Département Kouffo (6 communes)
            ['code' => 'KO-ADJ', 'nom' => 'Adja-Ouèrè', 'slug' => 'adja-ouere-kouffo', 'departement_code' => 'KO'],
            ['code' => 'KO-DOG', 'nom' => 'Dogbo', 'slug' => 'dogbo', 'departement_code' => 'KO'],
            ['code' => 'KO-KLO', 'nom' => 'Klouékanmè', 'slug' => 'klouekamme', 'departement_code' => 'KO'],
            ['code' => 'KO-LAL', 'nom' => 'Lalo', 'slug' => 'lalo', 'departement_code' => 'KO'],
            ['code' => 'KO-TOV', 'nom' => 'Toviklin', 'slug' => 'toviklin', 'departement_code' => 'KO'],
            ['code' => 'KO-AZO', 'nom' => 'Azohoué-Aliho', 'slug' => 'azohoue-aliho', 'departement_code' => 'KO'],

            // Département Littoral (1 commune)
            ['code' => 'LI-COT', 'nom' => 'Cotonou', 'slug' => 'cotonou', 'departement_code' => 'LI'],

            // Département Mono (6 communes)
            ['code' => 'MO-ATH', 'nom' => 'Athiémé', 'slug' => 'athieme', 'departement_code' => 'MO'],
            ['code' => 'MO-BOP', 'nom' => 'Bopa', 'slug' => 'bopa', 'departement_code' => 'MO'],
            ['code' => 'MO-COM', 'nom' => 'Comé', 'slug' => 'come', 'departement_code' => 'MO'],
            ['code' => 'MO-GRA', 'nom' => 'Grand-Popo', 'slug' => 'grand-popo', 'departement_code' => 'MO'],
            ['code' => 'MO-HOU', 'nom' => 'Houéyogbé', 'slug' => 'houeyogbe', 'departement_code' => 'MO'],
            ['code' => 'MO-LOK', 'nom' => 'Lokossa', 'slug' => 'lokossa', 'departement_code' => 'MO'],

            // Département Ouémé (9 communes)
            ['code' => 'OU-ADJ', 'nom' => 'Adjarra', 'slug' => 'adjarra', 'departement_code' => 'OU'],
            ['code' => 'OU-ADO', 'nom' => 'Adjohoun', 'slug' => 'adjohoun', 'departement_code' => 'OU'],
            ['code' => 'OU-AGU', 'nom' => 'Aguégués', 'slug' => 'aguegues', 'departement_code' => 'OU'],
            ['code' => 'OU-AVR', 'nom' => 'Avrankou', 'slug' => 'avrankou', 'departement_code' => 'OU'],
            ['code' => 'OU-BON', 'nom' => 'Bonou', 'slug' => 'bonou', 'departement_code' => 'OU'],
            ['code' => 'OU-DAN', 'nom' => 'Dangbo', 'slug' => 'dangbo', 'departement_code' => 'OU'],
            ['code' => 'OU-POR', 'nom' => 'Porto-Novo', 'slug' => 'porto-novo', 'departement_code' => 'OU'],
            ['code' => 'OU-SEM', 'nom' => 'Sèmè-Kpodji', 'slug' => 'seme-kpodji', 'departement_code' => 'OU'],
            ['code' => 'OU-AKP', 'nom' => 'Akpro-Missérété', 'slug' => 'akpro-misserete', 'departement_code' => 'OU'],

            // Département Plateau (5 communes)
            ['code' => 'PL-ADJ', 'nom' => 'Adja-Ouèrè', 'slug' => 'adja-ouere-plateau', 'departement_code' => 'PL'],
            ['code' => 'PL-IFE', 'nom' => 'Ifangni', 'slug' => 'ifangni', 'departement_code' => 'PL'],
            ['code' => 'PL-KET', 'nom' => 'Kétou', 'slug' => 'ketou', 'departement_code' => 'PL'],
            ['code' => 'PL-POB', 'nom' => 'Pobè', 'slug' => 'pobe', 'departement_code' => 'PL'],
            ['code' => 'PL-SAK', 'nom' => 'Sakété', 'slug' => 'sakete', 'departement_code' => 'PL'],

            // Département Zou (9 communes)
            ['code' => 'ZO-ABO', 'nom' => 'Abomey', 'slug' => 'abomey', 'departement_code' => 'ZO'],
            ['code' => 'ZO-AGB', 'nom' => 'Agbangnizoun', 'slug' => 'agbangnizoun', 'departement_code' => 'ZO'],
            ['code' => 'ZO-BOH', 'nom' => 'Bohicon', 'slug' => 'bohicon', 'departement_code' => 'ZO'],
            ['code' => 'ZO-COV', 'nom' => 'Covè', 'slug' => 'cove', 'departement_code' => 'ZO'],
            ['code' => 'ZO-DJI', 'nom' => 'Djidja', 'slug' => 'djidja', 'departement_code' => 'ZO'],
            ['code' => 'ZO-OUI', 'nom' => 'Ouinhi', 'slug' => 'ouinhi', 'departement_code' => 'ZO'],
            ['code' => 'ZO-ZAG', 'nom' => 'Za-Kpota', 'slug' => 'za-kpota', 'departement_code' => 'ZO'],
            ['code' => 'ZO-ZAN', 'nom' => 'Zangnanado', 'slug' => 'zangnanado', 'departement_code' => 'ZO'],
            ['code' => 'ZO-ZOG', 'nom' => 'Zogbodomey', 'slug' => 'zogbodomey', 'departement_code' => 'ZO'],
        ];

        foreach ($communes as $commune) {
            // Récupérer l'ID du département
            $departement = DB::table('departements')->where('code', $commune['departement_code'])->first();

            if ($departement) {
                DB::table('communes')->insert([
                    'code' => $commune['code'],
                    'nom' => $commune['nom'],
                    'slug' => $commune['slug'],
                    'departementId' => $departement->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}