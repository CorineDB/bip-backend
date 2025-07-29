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
        //Commune::truncate();
        DB::table('communes')->truncate();

        $communes = [
            // Département Alibori
            [
                'code' => 'AL-BAN',
                'nom' => 'Banikoara',
                'slug' => 'banikoara',
                'departement_code' => 'AL',
                'arrondissements' => [
                    "Banikoara",
                    "Founougo",
                    "Gomparou",
                    "Goumori",
                    "Kokey",
                    "Kokiborou",
                    "Ounet",
                    "Sompérékou",
                    "Soroko",
                    "Toura"
                ]
            ],
            [
                'code' => 'AL-GOG',
                'nom' => 'Gogounou',
                'slug' => 'gogounou',
                'departement_code' => 'AL',
                'arrondissements' => ["Bagou", "Gogounou", "Gounarou", "Ouara", "Sori", "Zoungou-Pantrossi"]
            ],
            [
                'code' => 'AL-KAN',
                'nom' => 'Kandi',
                'slug' => 'kandi',
                'departement_code' => 'AL',
                'arrondissements' => ["Angaradébou", "Bensékou", "Donwari", "Kandi I", "Kandi II", "Kandi III", "Kassakou", "Saah", "Sam", "Sonsoro"]
            ],
            [
                'code' => 'AL-KAR',
                'nom' => 'Karimama',
                'slug' => 'karimama',
                'departement_code' => 'AL',
                'arrondissements' => ["Birni Lafia", "Bogo-Bogo", "Karimama", "Kompa", "Monsey"]
            ],
            [
                'code' => 'AL-MAL',
                'nom' => 'Malanville',
                'slug' => 'malanville',
                'departement_code' => 'AL',
                'arrondissements' => ["Garou", "Guéné", "Malanville", "Madécali", "Toumboutou"]
            ],
            [
                'code' => 'AL-SEG',
                'nom' => 'Segbana',
                'slug' => 'segbana',
                'departement_code' => 'AL',
                'arrondissements' => ["Libantè", "Liboussou", "Lougou", "Segbana", "Sokotindji"]
            ],

            // Département Atacora
            [
                'code' => 'AK-BOU',
                'nom' => 'Boukoumbé',
                'slug' => 'boukoumbe',
                'departement_code' => 'AK',
                'arrondissements' => ["Boukoumbé", "Dipoli", "Korontière", "Kossoucoingou", "Manta", "Natta", "Tabota"]
            ],
            [
                'code' => 'AK-COB',
                'nom' => 'Cobly',
                'slug' => 'cobly',
                'departement_code' => 'AK',
                'arrondissements' => ["Cobly", "Datori", "Kountori", "Tapoga"]
            ],
            [
                'code' => 'AK-KER',
                'nom' => 'Kérou',
                'slug' => 'kerou',
                'departement_code' => 'AK',
                'arrondissements' => ["Brignamaro", "Firou", "Kérou", "Koabagou"]
            ],
            [
                'code' => 'AK-KOU',
                'nom' => 'Kouandé',
                'slug' => 'kouande',
                'departement_code' => 'AK',
                'arrondissements' => ["Birni", "Chabi-Couma", "Fô-Tancé", "Guilmaro", "Kouandé", "Oroukayo"]
            ],
            [
                'code' => 'AK-MAT',
                'nom' => 'Matéri',
                'slug' => 'materi',
                'departement_code' => 'AK',
                'arrondissements' => ["Dassari", "Gouandé", "Matéri", "Nodi", "Tantéga", "Tchianhoun-Cossi"]
            ],
            [
                'code' => 'AK-NAT',
                'nom' => 'Natitingou',
                'slug' => 'natitingou',
                'departement_code' => 'AK',
                'arrondissements' => ["Kotopounga", "Kouaba", "Koundata", "Natitingou I", "Natitingou II", "Natitingou III", "Natitingou IV", "Perma", "Tchoumi-Tchoumi"]
            ],
            [
                'code' => 'AK-PEH',
                'nom' => 'Péhunco',
                'slug' => 'pehunco',
                'departement_code' => 'AK',
                'arrondissements' => ["Gnémasson", "Péhunco", "Tobré"]
            ],
            [
                'code' => 'AK-TAN',
                'nom' => 'Tanguiéta',
                'slug' => 'tanguieta',
                'departement_code' => 'AK',
                'arrondissements' => ["Cotiakou", "N'Dahonta", "Taiakou", "Tanguiéta", "Tanongou"]
            ],
            [
                'code' => 'AK-TOU',
                'nom' => 'Toucountouna',
                'slug' => 'toucountouna',
                'departement_code' => 'AK',
                'arrondissements' => ["Kouarfa", "Tampégré", "Toucountouna"]
            ],

            // Département Atlantique
            [
                'code' => 'AT-ABC',
                'nom' => 'Abomey-Calavi',
                'slug' => 'abomey-calavi',
                'departement_code' => 'AT',
                'arrondissements' => ["Abomey-Calavi", "Akassato", "Godomey", "Glo-Djigbé", "Hêvié", "Kpanroun", "Ouèdo", "Togba", "Zinvié"]
            ],
            [
                'code' => 'AT-ALL',
                'nom' => 'Allada',
                'slug' => 'allada',
                'departement_code' => 'AT',
                'arrondissements' => ["Agbanou", "Ahouannonzoun", "Allada", "Attogon", "Avakpa", "Ayou", "Hinvi", "Lissègazoun", "Lon-Agonmey", "Sekou", "Togoudo", "Tokpa-Avagoudo"]
            ],
            [
                'code' => 'AT-KPO',
                'nom' => 'Kpomassè',
                'slug' => 'kpomasse',
                'departement_code' => 'AT',
                'arrondissements' => ["Aganmalomè", "Agbanto", "Agonkanmè", "Dédomè", "Dékanmè", "Kpomassè", "Sègbèya", "Sègbohouè", "Tokpa-Domè"]
            ],
            [
                'code' => 'AT-OUI',
                'nom' => 'Ouidah',
                'slug' => 'ouidah',
                'departement_code' => 'AT',
                'arrondissements' => ["Avlékété", "Djègbadji", "Gakpé", "Houakpè-Daho", "Ouidah I", "Ouidah II", "Ouidah III", "Ouidah IV", "Pahou", "Savi"]
            ],
            [
                'code' => 'AT-SAV',
                'nom' => 'Sô-Ava',
                'slug' => 'so-ava',
                'departement_code' => 'AT',
                'arrondissements' => ["Ahomey-Lokpo", "Dékanmey", "Ganvié I", "Ganvié II", "Houédo-Aguékon", "Sô-Ava", "Vekky"]
            ],
            [
                'code' => 'AT-TOF',
                'nom' => 'Toffo',
                'slug' => 'toffo',
                'departement_code' => 'AT',
                'arrondissements' => ["Agué", "Colli-Agbamè", "Coussi", "Damè", "Djanglanmè", "Houègbo", "Kpomè", "Sè", "Sèhouè", "Toffo-Agué"]
            ],
            [
                'code' => 'AT-TOR',
                'nom' => 'Tori-Bossito',
                'slug' => 'tori-bossito',
                'departement_code' => 'AT',
                'arrondissements' => ["Avamè", "Azohouè-Aliho", "Azohouè-Cada", "Tori-Bossito", "Tori-Cada", "Tori-Gare Tori aïdohoue", "Tori acadjamè"]
            ],
            [
                'code' => 'AT-ZE',
                'nom' => 'Zè',
                'slug' => 'ze',
                'departement_code' => 'AT',
                'arrondissements' => ["Adjan", "Dawè", "Djigbé", "Dodji-Bata", "Hèkanmé", "Koundokpoé", "Sèdjè-Dénou", "Sèdjè-Houégoudo", "Tangbo-Djevié", "Yokpo", "Zè"]
            ],

            // Département Borgou
            [
                'code' => 'BO-BEM',
                'nom' => 'Bembéréké',
                'slug' => 'bembereke',
                'departement_code' => 'BO',
                'arrondissements' => ["Bembéréké", "Béroubouay", "Bouanri", "Gamia", "Ina"]
            ],
            [
                'code' => 'BO-KAL',
                'nom' => 'Kalalé',
                'slug' => 'kalale',
                'departement_code' => 'BO',
                'arrondissements' => ["Basso", "Bouka", "Dérassi", "Dunkassa", "Kalalé", "Péonga"]
            ],
            [
                'code' => 'BO-NDA',
                'nom' => 'N\'Dali',
                'slug' => 'ndali',
                'departement_code' => 'BO',
                'arrondissements' => ["Bori", "Gbégourou", "N'Dali", "Ouénou", "Sirarou"]
            ],
            [
                'code' => 'BO-NIK',
                'nom' => 'Nikki',
                'slug' => 'nikki',
                'departement_code' => 'BO',
                'arrondissements' => ["Biro", "Gnonkourakali", "Nikki", "Ouénou", "Sérékalé", "Suya", "Tasso"]
            ],
            [
                'code' => 'BO-PAR',
                'nom' => 'Parakou',
                'slug' => 'parakou',
                'departement_code' => 'BO',
                'arrondissements' => ["1er arrondissement de Parakou", "2e arrondissement de Parakou", "3e arrondissement de Parakou"]
            ],
            [
                'code' => 'BO-PER',
                'nom' => 'Pèrèrè',
                'slug' => 'perere',
                'departement_code' => 'BO',
                'arrondissements' => ["Gninsy", "Guinagourou", "Kpané", "Pébié", "Pèrèrè", "Sontou"]
            ],
            [
                'code' => 'BO-SIN',
                'nom' => 'Sinendé',
                'slug' => 'sinende',
                'departement_code' => 'BO',
                'arrondissements' => ["Fô-Bourè", "Sèkèrè", "Sikki", "Sinendé"]
            ],
            [
                'code' => 'BO-TCH',
                'nom' => 'Tchaourou',
                'slug' => 'tchaourou',
                'departement_code' => 'BO',
                'arrondissements' => ["Alafiarou", "Bétérou", "Goro", "Kika", "Sanson", "Tchaourou", "Tchatchou"                ]
            ],

            // Département Collines
            [
                'code' => 'CO-BAN',
                'nom' => 'Bantè',
                'slug' => 'bante',
                'departement_code' => 'CO',
                'arrondissements' => ["Agoua", "Akpassi", "Atokoligbé", "Bantè", "Bobè", "Gouka", "Koko", "Lougba", "Pira"]
            ],
            [
                'code' => 'CO-DAS',
                'nom' => 'Dassa-Zoumé',
                'slug' => 'dassa-zoume',
                'departement_code' => 'CO',
                'arrondissements' => ["Akofodjoulè", "Dassa I", "Dassa II", "Gbaffo", "Kerè", "Kpingni", "Lèma", "Paouignan", "Soclogbo", "Tré"]
            ],
            [
                'code' => 'CO-GLA',
                'nom' => 'Glazoué',
                'slug' => 'glazoue',
                'departement_code' => 'CO',
                'arrondissements' => ["Aklankpa", "Assanté", "Glazoué", "Gomè", "Kpakpaza", "Magoumi", "Ouèdèmè", "Sokponta", "Thio", "Zaffé"                ]
            ],
            [
                'code' => 'CO-OUE',
                'nom' => 'Ouèssè',
                'slug' => 'ouesse',
                'departement_code' => 'CO',
                'arrondissements' => ["Challa-Ogoi", "Djègbè", "Gbanlin", "Kémon", "Kilibo", "Laminou", "Odougba", "Ouèssè", "Toui"]
            ],
            [
                'code' => 'CO-SAV',
                'nom' => 'Savalou',
                'slug' => 'savalou',
                'departement_code' => 'CO',
                'arrondissements' => ["Djaloukou", "Doumè", "Gobada", "Kpataba", "Lahotan", "Lèma", "Logozohè", "Monkpa", "Ottola", "Ouèssè", "Savalou-aga", "Savalou-agbado", "Savalou-attakè", "Tchetti"]
            ],
            [
                'code' => 'CO-SAV2',
                'nom' => 'Savè',
                'slug' => 'save',
                'departement_code' => 'CO',
                'arrondissements' => ["Adido", "Bèssè", "Boni", "Kaboua", "Ofè", "Okpara", "Plateau", "Sakin"]
            ],

            // Département Couffo
            [
                'code' => 'KO-APL',
                'nom' => 'Aplahoué',
                'slug' => 'aplahoue',
                'departement_code' => 'KO',
                'arrondissements' => ["Aplahoué", "Atomè", "Azovè", "Dekpo", "Godohou", "Kissamey", "Lonkly"]
            ],
            [
                'code' => 'KO-DJA',
                'nom' => 'Djakotomey',
                'slug' => 'djakotomey',
                'departement_code' => 'KO',
                'arrondissements' => ["Adjintimey", "Bètoumey", "Djakotomey I", "Djakotomey II", "Gohomey", "Houègamey", "Kinkinhoué", "Kokohoué", "Kpoba", "Sokouhoué"]
            ],
            [
                'code' => 'KO-DOG',
                'nom' => 'Dogbo-Tota',
                'slug' => 'dogbo-tota',
                'departement_code' => 'KO',
                'arrondissements' => ["Ayomi", "Dèvè", "Honton", "Lokogohoué", "Madjrè", "Tota", "Totchagni"]
            ],
            [
                'code' => 'KO-KLO',
                'nom' => 'Klouékanmè',
                'slug' => 'klouekanme',
                'departement_code' => 'KO',
                'arrondissements' => ["Adjanhonmè", "Ahogbèya", "Aya-Hohoué", "Djotto", "Hondji", "Klouékanmè", "Lanta", "Tchikpé"]
            ],
            [
                'code' => 'KO-LAL',
                'nom' => 'Lalo',
                'slug' => 'lalo',
                'departement_code' => 'KO',
                'arrondissements' => ["Adoukandji", "Ahondjinnako", "Ahomadegbe", "Banigbé", "Gnizounmè", "Hlassamè", "Lalo", "Lokogba", "Tchito", "Tohou", "Zalli"]
            ],
            [
                'code' => 'KO-TOV',
                'nom' => 'Toviklin',
                'slug' => 'toviklin',
                'departement_code' => 'KO',
                'arrondissements' => ["Adjido", "Avédjin", "Doko", "Houédogli", "Missinko", "Tannou-Gola", "Toviklin"]
            ],

            // Département Donga
            [
                'code' => 'DO-BAS',
                'nom' => 'Bassila',
                'slug' => 'bassila',
                'departement_code' => 'DO',
                'arrondissements' => ["Alédjo", "Bassila", "Manigri", "Pénéssoulou"]
            ],
            [
                'code' => 'DO-COP',
                'nom' => 'Copargo',
                'slug' => 'copargo',
                'departement_code' => 'DO',
                'arrondissements' => ["Anandana", "Copargo", "Pabégou", "Singré"]
            ],
            [
                'code' => 'DO-DJO',
                'nom' => 'Djougou',
                'slug' => 'djougou',
                'departement_code' => 'DO',
                'arrondissements' => ["Barei", "Bariénou", "Bélléfoungou", "Bougou", "Djougou I", "Djougou II", "Djougou III", "Kolokondé", "Onklou", "Patargo", "Pélébina", "Sérou"]
            ],
            [
                'code' => 'DO-OUA',
                'nom' => 'Ouaké',
                'slug' => 'ouake',
                'departement_code' => 'DO',
                'arrondissements' => ["Badjoudè", "Kondé", "Ouaké", "Sèmèrè I", "Sèmèrè II", "Tchalinga"]
            ],

            // Département Littoral
            [
                'code' => 'LI-COT',
                'nom' => 'Cotonou',
                'slug' => 'cotonou',
                'departement_code' => 'LI',
                'arrondissements' => ["1er arrondissement de Cotonou", "2e arrondissement de Cotonou", "3e arrondissement de Cotonou", "4e arrondissement de Cotonou", "5e arrondissement de Cotonou", "6e arrondissement de Cotonou", "7e arrondissement de Cotonou", "8e arrondissement de Cotonou", "9e arrondissement de Cotonou", "10e arrondissement de Cotonou", "11e arrondissement de Cotonou", "12e arrondissement de Cotonou", "13e arrondissement de Cotonou"]
            ],

            // Département Mono
            [
                'code' => 'MO-ATH',
                'nom' => 'Athiémé',
                'slug' => 'atheme',
                'departement_code' => 'MO',
                'arrondissements' => ["Adohoun", "Atchannou", "Athiémé", "Dédékpoé", "Kpinnou"]
            ],
            [
                'code' => 'MO-BOP',
                'nom' => 'Bopa',
                'slug' => 'bopa',
                'departement_code' => 'MO',
                'arrondissements' => ["Agbodji", "Badazoui", "Bopa", "Gbakpodji", "Lobogo", "Possotomè", "Yégodoé"]
            ],
            [
                'code' => 'MO-COM',
                'nom' => 'Comè',
                'slug' => 'come',
                'departement_code' => 'MO',
                'arrondissements' => ["Agatogbo", "Akodéha", "Comè", "Ouèdèmè-Pédah", "Oumako"]
            ],
            [
                'code' => 'MO-GPO',
                'nom' => 'Grand-Popo',
                'slug' => 'grand-popo',
                'departement_code' => 'MO',
                'arrondissements' => ["Adjaha", "Agoué", "Avloh", "Djanglanmey", "Gbéhoué", "Grand-Popo", "Sazoué"]
            ],
            [
                'code' => 'MO-HOU',
                'nom' => 'Houéyogbé',
                'slug' => 'houeyogbe',
                'departement_code' => 'MO',
                'arrondissements' => ["Dahé", "Doutou", "Honhoué", "Houéyogbé", "Sè", "Zoungbonou"]
            ],
            [
                'code' => 'MO-LOK',
                'nom' => 'Lokossa',
                'slug' => 'lokossa',
                'departement_code' => 'MO',
                'arrondissements' => ["Agamé", "Houin", "Koudo", "Lokossa et Ouèdèmè"]
            ],

            // Département Ouémé
            [
                'code' => 'OU-ADJ',
                'nom' => 'Adjarra',
                'slug' => 'adjarra',
                'departement_code' => 'OU',
                'arrondissements' => ["Adjarra I", "Adjarra II", "Aglogbé", "Honvié", "Malanhoui", "Médédjonou"]
            ],
            [
                'code' => 'OU-ADH',
                'nom' => 'Adjohoun',
                'slug' => 'adjohoun',
                'departement_code' => 'OU',
                'arrondissements' => ["Adjohoun", "Akpadanou", "Awonou", "Azowlissè", "Dèmè", "Gangban", "Kodè", "Togbota"]
            ],
            [
                'code' => 'OU-AGU',
                'nom' => 'Aguégués',
                'slug' => 'aguegues',
                'departement_code' => 'OU',
                'arrondissements' => ["Avagbodji", "Houédomè", "Zoungamè"]
            ],
            [
                'code' => 'OU-AKM',
                'nom' => 'Akpro-Missérété',
                'slug' => 'akpro-misserete',
                'departement_code' => 'OU',
                'arrondissements' => ["Akpro-Missérété", "Gomè-Sota", "Katagon", "Vakon", "Zodogbomey"]
            ],
            [
                'code' => 'OU-AVR',
                'nom' => 'Avrankou',
                'slug' => 'avrankou',
                'departement_code' => 'OU',
                'arrondissements' => ["Atchoukpa", "Avrankou", "Djomon", "Gbozounmè", "Kouty", "Ouanho", "Sado"]
            ],
            [
                'code' => 'OU-BON',
                'nom' => 'Bonou',
                'slug' => 'bonou',
                'departement_code' => 'OU',
                'arrondissements' => ["Affamè", "Atchonsa", "Bonou", "Damè-Wogon", "Houinviguè"]
            ],
            [
                'code' => 'OU-DAN',
                'nom' => 'Dangbo',
                'slug' => 'dangbo',
                'departement_code' => 'OU',
                'arrondissements' => ["Dangbo", "Dèkin", "Gbéko", "Houédomey", "Hozin", "Késsounou", "Zounguè"]
            ],
            [
                'code' => 'OU-PNV',
                'nom' => 'Porto-Novo',
                'slug' => 'porto-novo',
                'departement_code' => 'OU',
                'arrondissements' => ["1er arrondissement", "2e arrondissement", "3e arrondissement", "4e arrondissement", "5e arrondissement"]
            ],
            [
                'code' => 'OU-SKP',
                'nom' => 'Sèmè-Kpodji',
                'slug' => 'seme-kpodji',
                'departement_code' => 'OU',
                'arrondissements' => ["Agblangandan", "Aholouyèmè", "Djèrègbè", "Ekpè", "Sèmè-Kpodji", "Tohouè"]
            ],

            // Département Plateau
            [
                'code' => 'PL-AOU',
                'nom' => 'Adja-Ouèrè',
                'slug' => 'adja-ouere',
                'departement_code' => 'PL',
                'arrondissements' => ["Adja-Ouèrè", "Ikpinlè", "Kpoulou", "Massè", "Oko-Akarè", "Totonnoukon"]
            ],
            [
                'code' => 'PL-IFA',
                'nom' => 'Ifangni',
                'slug' => 'ifangni',
                'departement_code' => 'PL',
                'arrondissements' => ["Banigbé", "Daagbé", "Ifangni", "Ko-Koumolou", "Lagbé", "Tchaada"]
            ],
            [
                'code' => 'PL-KET',
                'nom' => 'Kétou',
                'slug' => 'ketou',
                'departement_code' => 'PL',
                'arrondissements' => ["Adakplamé", "Idigny", "Kpankou", "Kétou", "Odometa", "Okpometa"]
            ],
            [
                'code' => 'PL-POB',
                'nom' => 'Pobè',
                'slug' => 'pobe',
                'departement_code' => 'PL',
                'arrondissements' => ["Ahoyéyé", "Igana", "Issaba", "Pobè", "Towé"]
            ],
            [
                'code' => 'PL-SAK',
                'nom' => 'Sakété',
                'slug' => 'sakete',
                'departement_code' => 'PL',
                'arrondissements' => ["Aguidi", "Ita-Djèbou", "Sakété I", "Sakété II", "Takon", "Yoko"]
            ],

            // Département Zou
            [
                'code' => 'ZO-ABO',
                'nom' => 'Abomey',
                'slug' => 'abomey',
                'departement_code' => 'ZO',
                'arrondissements' => ["Agbokpa", "Dètohou", "Djègbè", "Hounli", "Sèhoun", "Vidolè", "Zounzounmè"]
            ],
            [
                'code' => 'ZO-AGB',
                'nom' => 'Agbangnizoun',
                'slug' => 'agbangnizoun',
                'departement_code' => 'ZO',
                'arrondissements' => ["Adahondjigon", "Adingningon", "Agbangnizoun", "Kinta", "Kpota", "Lissazounmè", "Sahé", "Siwé", "Tanvé", "Zoungoudo"]
            ],
            [
                'code' => 'ZO-BOH',
                'nom' => 'Bohicon',
                'slug' => 'bohicon',
                'departement_code' => 'ZO',
                'arrondissements' => ["Agongointo", "Avogbanna", "Bohicon I", "Bohicon II", "Gnidjazoun", "Lissèzoun", "Ouassaho", "Passagon", "Saclo", "Sodohomè"]
            ],
            [
                'code' => 'ZO-COV',
                'nom' => 'Covè',
                'slug' => 'cove',
                'departement_code' => 'ZO',
                'arrondissements' => ["Adogbé", "Gounli", "Houéko", "Houen-Hounso", "Lainta-Cogbè", "Naogon", "Soli", "Zogba"]
            ],
            [
                'code' => 'ZO-DJI',
                'nom' => 'Djidja',
                'slug' => 'djidja',
                'departement_code' => 'ZO',
                'arrondissements' => ["Agondji", "Agouna", "Dan", "Djidja", "Dohouimè", "Gobaix", "Monsourou", "Mougnon", "Oungbègamè", "Houto", "Setto", "Zoukon"]
            ],
            [
                'code' => 'ZO-OUIN',
                'nom' => 'Ouinhi',
                'slug' => 'ouinhi',
                'departement_code' => 'ZO',
                'arrondissements' => ["Dasso", "Ouinhi", "Sagon", "Tohoué"]
            ],
            [
                'code' => 'ZO-ZKP',
                'nom' => 'Za-Kpota',
                'slug' => 'za-kpota',
                'departement_code' => 'ZO',
                'arrondissements' => ["Allahé", "Assalin", "Houngomey", "Kpakpamè", "Kpozoun", "Za-Kpota", "Za-Tanta", "Zèko"]
            ],
            [
                'code' => 'ZO-ZAG',
                'nom' => 'Zagnanado',
                'slug' => 'zagnanado',
                'departement_code' => 'ZO',
                'arrondissements' => ["Agonli-Houégbo", "Banamè", "N'-Tan", "Dovi", "Kpédékpo", "Zagnanado"]
            ],
            [
                'code' => 'ZO-ZOG',
                'nom' => 'Zogbodomey',
                'slug' => 'zogbodomey',
                'departement_code' => 'ZO',
                'arrondissements' => ["Akiza", "Avlamè", "Cana I", "Cana II", "Domè", "Koussoukpa", "Kpokissa", "Massi", "Tanwé-Hessou", "Zogbodomey", "Zoukou"]
            ],
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
