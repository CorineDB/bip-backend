<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArrondissementSeeder extends Seeder
{
    public function run(): void
    {
        // Supprime toutes les lignes de la table
        //Arrondissement::truncate();
        DB::table('arrondissements')->truncate();

        // Extraire tous les arrondissements du CommuneSeeder
        $communes = [
            // Département Alibori
            ['code' => 'AL-BAN', 'arrondissements' => ["Banikoara", "Founougo", "Gomparou", "Goumori", "Kokey", "Kokiborou", "Ounet", "Sompérékou", "Soroko", "Toura"]],
            ['code' => 'AL-GOG', 'arrondissements' => ["Bagou", "Gogounou", "Gounarou", "Ouara", "Sori", "Zoungou-Pantrossi"]],
            ['code' => 'AL-KAN', 'arrondissements' => ["Angaradébou", "Bensékou", "Donwari", "Kandi I", "Kandi II", "Kandi III", "Kassakou", "Saah", "Sam", "Sonsoro"]],
            ['code' => 'AL-KAR', 'arrondissements' => ["Birni Lafia", "Bogo-Bogo", "Karimama", "Kompa", "Monsey"]],
            ['code' => 'AL-MAL', 'arrondissements' => ["Garou", "Guéné", "Malanville", "Madécali", "Toumboutou"]],
            ['code' => 'AL-SEG', 'arrondissements' => ["Libantè", "Liboussou", "Lougou", "Segbana", "Sokotindji"]],

            // Département Atacora
            ['code' => 'AK-BOU', 'arrondissements' => ["Boukoumbé", "Dipoli", "Korontière", "Kossoucoingou", "Manta", "Natta", "Tabota"]],
            ['code' => 'AK-COB', 'arrondissements' => ["Cobly", "Datori", "Kountori", "Tapoga"]],
            ['code' => 'AK-KER', 'arrondissements' => ["Brignamaro", "Firou", "Kérou", "Koabagou"]],
            ['code' => 'AK-KOU', 'arrondissements' => ["Birni", "Chabi-Couma", "Fô-Tancé", "Guilmaro", "Kouandé", "Oroukayo"]],
            ['code' => 'AK-MAT', 'arrondissements' => ["Dassari", "Gouandé", "Matéri", "Nodi", "Tantéga", "Tchianhoun-Cossi"]],
            ['code' => 'AK-NAT', 'arrondissements' => ["Kotopounga", "Kouaba", "Koundata", "Natitingou I", "Natitingou II", "Natitingou III", "Natitingou IV", "Perma", "Tchoumi-Tchoumi"]],
            ['code' => 'AK-PEH', 'arrondissements' => ["Gnémasson", "Péhunco", "Tobré"]],
            ['code' => 'AK-TAN', 'arrondissements' => ["Cotiakou", "N'Dahonta", "Taiakou", "Tanguiéta", "Tanongou"]],
            ['code' => 'AK-TOU', 'arrondissements' => ["Kouarfa", "Tampégré", "Toucountouna"]],

            // Département Atlantique
            ['code' => 'AT-ABC', 'arrondissements' => ["Abomey-Calavi", "Akassato", "Godomey", "Glo-Djigbé", "Hêvié", "Kpanroun", "Ouèdo", "Togba", "Zinvié"]],
            ['code' => 'AT-ALL', 'arrondissements' => ["Agbanou", "Ahouannonzoun", "Allada", "Attogon", "Avakpa", "Ayou", "Hinvi", "Lissègazoun", "Lon-Agonmey", "Sekou", "Togoudo", "Tokpa-Avagoudo"]],
            ['code' => 'AT-KPO', 'arrondissements' => ["Aganmalomè", "Agbanto", "Agonkanmè", "Dédomè", "Dékanmè", "Kpomassè", "Sègbèya", "Sègbohouè", "Tokpa-Domè"]],
            ['code' => 'AT-OUI', 'arrondissements' => ["Avlékété", "Djègbadji", "Gakpé", "Houakpè-Daho", "Ouidah I", "Ouidah II", "Ouidah III", "Ouidah IV", "Pahou", "Savi"]],
            ['code' => 'AT-SAV', 'arrondissements' => ["Ahomey-Lokpo", "Dékanmey", "Ganvié I", "Ganvié II", "Houédo-Aguékon", "Sô-Ava", "Vekky"]],
            ['code' => 'AT-TOF', 'arrondissements' => ["Agué", "Colli-Agbamè", "Coussi", "Damè", "Djanglanmè", "Houègbo", "Kpomè", "Sè", "Sèhouè", "Toffo-Agué"]],
            ['code' => 'AT-TOR', 'arrondissements' => ["Avamè", "Azohouè-Aliho", "Azohouè-Cada", "Tori-Bossito", "Tori-Cada", "Tori-Gare Tori aïdohoue", "Tori acadjamè"]],
            ['code' => 'AT-ZE', 'arrondissements' => ["Adjan", "Dawè", "Djigbé", "Dodji-Bata", "Hèkanmé", "Koundokpoé", "Sèdjè-Dénou", "Sèdjè-Houégoudo", "Tangbo-Djevié", "Yokpo", "Zè"]],

            // Département Borgou
            ['code' => 'BO-BEM', 'arrondissements' => ["Bembéréké", "Béroubouay", "Bouanri", "Gamia", "Ina"]],
            ['code' => 'BO-KAL', 'arrondissements' => ["Basso", "Bouka", "Dérassi", "Dunkassa", "Kalalé", "Péonga"]],
            ['code' => 'BO-NDA', 'arrondissements' => ["Bori", "Gbégourou", "N'Dali", "Ouénou", "Sirarou"]],
            ['code' => 'BO-NIK', 'arrondissements' => ["Biro", "Gnonkourakali", "Nikki", "Ouénou", "Sérékalé", "Suya", "Tasso"]],
            ['code' => 'BO-PAR', 'arrondissements' => ["1er arrondissement de Parakou", "2e arrondissement de Parakou", "3e arrondissement de Parakou"]],
            ['code' => 'BO-PER', 'arrondissements' => ["Gninsy", "Guinagourou", "Kpané", "Pébié", "Pèrèrè", "Sontou"]],
            ['code' => 'BO-SIN', 'arrondissements' => ["Fô-Bourè", "Sèkèrè", "Sikki", "Sinendé"]],
            ['code' => 'BO-TCH', 'arrondissements' => ["Alafiarou", "Bétérou", "Goro", "Kika", "Sanson", "Tchaourou", "Tchatchou"]],

            // Département Collines
            ['code' => 'CO-BAN', 'arrondissements' => ["Agoua", "Akpassi", "Atokoligbé", "Bantè", "Bobè", "Gouka", "Koko", "Lougba", "Pira"]],
            ['code' => 'CO-DAS', 'arrondissements' => ["Akofodjoulè", "Dassa I", "Dassa II", "Gbaffo", "Kerè", "Kpingni", "Lèma", "Paouignan", "Soclogbo", "Tré"]],
            ['code' => 'CO-GLA', 'arrondissements' => ["Aklankpa", "Assanté", "Glazoué", "Gomè", "Kpakpaza", "Magoumi", "Ouèdèmè", "Sokponta", "Thio", "Zaffé"]],
            ['code' => 'CO-OUE', 'arrondissements' => ["Challa-Ogoi", "Djègbè", "Gbanlin", "Kémon", "Kilibo", "Laminou", "Odougba", "Ouèssè", "Toui"]],
            ['code' => 'CO-SAV', 'arrondissements' => ["Djaloukou", "Doumè", "Gobada", "Kpataba", "Lahotan", "Lèma", "Logozohè", "Monkpa", "Ottola", "Ouèssè", "Savalou-aga", "Savalou-agbado", "Savalou-attakè", "Tchetti"]],
            ['code' => 'CO-SAV2', 'arrondissements' => ["Adido", "Bèssè", "Boni", "Kaboua", "Ofè", "Okpara", "Plateau", "Sakin"]],

            // Département Couffo
            ['code' => 'KO-APL', 'arrondissements' => ["Aplahoué", "Atomè", "Azovè", "Dekpo", "Godohou", "Kissamey", "Lonkly"]],
            ['code' => 'KO-DJA', 'arrondissements' => ["Adjintimey", "Bètoumey", "Djakotomey I", "Djakotomey II", "Gohomey", "Houègamey", "Kinkinhoué", "Kokohoué", "Kpoba", "Sokouhoué"]],
            ['code' => 'KO-DOG', 'arrondissements' => ["Ayomi", "Dèvè", "Honton", "Lokogohoué", "Madjrè", "Tota", "Totchagni"]],
            ['code' => 'KO-KLO', 'arrondissements' => ["Adjanhonmè", "Ahogbèya", "Aya-Hohoué", "Djotto", "Hondji", "Klouékanmè", "Lanta", "Tchikpé"]],
            ['code' => 'KO-LAL', 'arrondissements' => ["Adoukandji", "Ahondjinnako", "Ahomadegbe", "Banigbé", "Gnizounmè", "Hlassamè", "Lalo", "Lokogba", "Tchito", "Tohou", "Zalli"]],
            ['code' => 'KO-TOV', 'arrondissements' => ["Adjido", "Avédjin", "Doko", "Houédogli", "Missinko", "Tannou-Gola", "Toviklin"]],

            // Département Donga
            ['code' => 'DO-BAS', 'arrondissements' => ["Alédjo", "Bassila", "Manigri", "Pénéssoulou"]],
            ['code' => 'DO-COP', 'arrondissements' => ["Anandana", "Copargo", "Pabégou", "Singré"]],
            ['code' => 'DO-DJO', 'arrondissements' => ["Barei", "Bariénou", "Bélléfoungou", "Bougou", "Djougou I", "Djougou II", "Djougou III", "Kolokondé", "Onklou", "Patargo", "Pélébina", "Sérou"]],
            ['code' => 'DO-OUA', 'arrondissements' => ["Badjoudè", "Kondé", "Ouaké", "Sèmèrè I", "Sèmèrè II", "Tchalinga"]],

            // Département Littoral
            ['code' => 'LI-COT', 'arrondissements' => ["1er arrondissement de Cotonou", "2e arrondissement de Cotonou", "3e arrondissement de Cotonou", "4e arrondissement de Cotonou", "5e arrondissement de Cotonou", "6e arrondissement de Cotonou", "7e arrondissement de Cotonou", "8e arrondissement de Cotonou", "9e arrondissement de Cotonou", "10e arrondissement de Cotonou", "11e arrondissement de Cotonou", "12e arrondissement de Cotonou", "13e arrondissement de Cotonou"]],

            // Département Mono
            ['code' => 'MO-ATH', 'arrondissements' => ["Adohoun", "Atchannou", "Athiémé", "Dédékpoé", "Kpinnou"]],
            ['code' => 'MO-BOP', 'arrondissements' => ["Agbodji", "Badazoui", "Bopa", "Gbakpodji", "Lobogo", "Possotomè", "Yégodoé"]],
            ['code' => 'MO-COM', 'arrondissements' => ["Agatogbo", "Akodéha", "Comè", "Ouèdèmè-Pédah", "Oumako"]],
            ['code' => 'MO-GPO', 'arrondissements' => ["Adjaha", "Agoué", "Avloh", "Djanglanmey", "Gbéhoué", "Grand-Popo", "Sazoué"]],
            ['code' => 'MO-HOU', 'arrondissements' => ["Dahé", "Doutou", "Honhoué", "Houéyogbé", "Sè", "Zoungbonou"]],
            ['code' => 'MO-LOK', 'arrondissements' => ["Agamé", "Houin", "Koudo", "Lokossa et Ouèdèmè"]],

            // Département Ouémé
            ['code' => 'OU-ADJ', 'arrondissements' => ["Adjarra I", "Adjarra II", "Aglogbé", "Honvié", "Malanhoui", "Médédjonou"]],
            ['code' => 'OU-ADH', 'arrondissements' => ["Adjohoun", "Akpadanou", "Awonou", "Azowlissè", "Dèmè", "Gangban", "Kodè", "Togbota"]],
            ['code' => 'OU-AGU', 'arrondissements' => ["Avagbodji", "Houédomè", "Zoungamè"]],
            ['code' => 'OU-AKM', 'arrondissements' => ["Akpro-Missérété", "Gomè-Sota", "Katagon", "Vakon", "Zodogbomey"]],
            ['code' => 'OU-AVR', 'arrondissements' => ["Atchoukpa", "Avrankou", "Djomon", "Gbozounmè", "Kouty", "Ouanho", "Sado"]],
            ['code' => 'OU-BON', 'arrondissements' => ["Affamè", "Atchonsa", "Bonou", "Damè-Wogon", "Houinviguè"]],
            ['code' => 'OU-DAN', 'arrondissements' => ["Dangbo", "Dèkin", "Gbéko", "Houédomey", "Hozin", "Késsounou", "Zounguè"]],
            ['code' => 'OU-PNV', 'arrondissements' => ["1er arrondissement", "2e arrondissement", "3e arrondissement", "4e arrondissement", "5e arrondissement"]],
            ['code' => 'OU-SKP', 'arrondissements' => ["Agblangandan", "Aholouyèmè", "Djèrègbè", "Ekpè", "Sèmè-Kpodji", "Tohouè"]],

            // Département Plateau
            ['code' => 'PL-AOU', 'arrondissements' => ["Adja-Ouèrè", "Ikpinlè", "Kpoulou", "Massè", "Oko-Akarè", "Totonnoukon"]],
            ['code' => 'PL-IFA', 'arrondissements' => ["Banigbé", "Daagbé", "Ifangni", "Ko-Koumolou", "Lagbé", "Tchaada"]],
            ['code' => 'PL-KET', 'arrondissements' => ["Adakplamé", "Idigny", "Kpankou", "Kétou", "Odometa", "Okpometa"]],
            ['code' => 'PL-POB', 'arrondissements' => ["Ahoyéyé", "Igana", "Issaba", "Pobè", "Towé"]],
            ['code' => 'PL-SAK', 'arrondissements' => ["Aguidi", "Ita-Djèbou", "Sakété I", "Sakété II", "Takon", "Yoko"]],

            // Département Zou
            ['code' => 'ZO-ABO', 'arrondissements' => ["Agbokpa", "Dètohou", "Djègbè", "Hounli", "Sèhoun", "Vidolè", "Zounzounmè"]],
            ['code' => 'ZO-AGB', 'arrondissements' => ["Adahondjigon", "Adingningon", "Agbangnizoun", "Kinta", "Kpota", "Lissazounmè", "Sahé", "Siwé", "Tanvé", "Zoungoudo"]],
            ['code' => 'ZO-BOH', 'arrondissements' => ["Agongointo", "Avogbanna", "Bohicon I", "Bohicon II", "Gnidjazoun", "Lissèzoun", "Ouassaho", "Passagon", "Saclo", "Sodohomè"]],
            ['code' => 'ZO-COV', 'arrondissements' => ["Adogbé", "Gounli", "Houéko", "Houen-Hounso", "Lainta-Cogbè", "Naogon", "Soli", "Zogba"]],
            ['code' => 'ZO-DJI', 'arrondissements' => ["Agondji", "Agouna", "Dan", "Djidja", "Dohouimè", "Gobaix", "Monsourou", "Mougnon", "Oungbègamè", "Houto", "Setto", "Zoukon"]],
            ['code' => 'ZO-OUIN', 'arrondissements' => ["Dasso", "Ouinhi", "Sagon", "Tohoué"]],
            ['code' => 'ZO-ZKP', 'arrondissements' => ["Allahé", "Assalin", "Houngomey", "Kpakpamè", "Kpozoun", "Za-Kpota", "Za-Tanta", "Zèko"]],
            ['code' => 'ZO-ZAG', 'arrondissements' => ["Agonli-Houégbo", "Banamè", "N'-Tan", "Dovi", "Kpédékpo", "Zagnanado"]],
            ['code' => 'ZO-ZOG', 'arrondissements' => ["Akiza", "Avlamè", "Cana I", "Cana II", "Domè", "Koussoukpa", "Kpokissa", "Massi", "Tanwé-Hessou", "Zogbodomey", "Zoukou"]],
        ];

        // Générer tous les arrondissements basés sur les données du CommuneSeeder
        foreach ($communes as $commune) {
            // Récupérer l'ID de la commune
            $communeRecord = DB::table('communes')->where('code', $commune['code'])->first();

            if ($communeRecord && isset($commune['arrondissements'])) {
                foreach ($commune['arrondissements'] as $index => $arrondissement) {
                    $code = $commune['code'] . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    $slug = Str::slug($arrondissement);


                    $count = DB::table('arrondissements')->where('slug', $slug)->count();

                    if($count){
                        $slug .=$count;
                    }

                    DB::table('arrondissements')->insert([
                        'code' => $code,
                        'nom' => $arrondissement,
                        'slug' => $slug,
                        'communeId' => $communeRecord->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}