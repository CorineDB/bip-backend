<?php

namespace Database\Seeders;

use App\Helpers\SlugHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $odds = [
            "Pas de pauvreté",
            "Faim « zéro »",
            "Bonne santé et bien-être",
            "Éducation de qualité",
            "Égalité entre les sexes",
            "Eau propre et assainissement",
            "Énergie propre et d'un coût abordable",
            "Travail décent et croissance économique",
            "Industrie, innovation et infrastructure",
            "Inégalités réduites",
            "Villes et communautés durables",
            "Consommation et production responsables",
            "Mesures relatives à la lutte contre les changements climatiques",
            "Vie aquatique",
            "Vie terrestre",
            "Paix, justice et institutions efficaces",
            "Partenariats pour la réalisation des objectifs"
        ];

        $odds = [
            [
                'odd' => "Éliminer l'extrême pauvreté et la faim",
                'cibles' => [
                    "D'ici à 2030, éliminer complètement l'extrême pauvreté dans le monde entier (s'entend actuellement du fait de vivre avec moins de 1,25 dollars par jour).",
                    "D'ici à 2030, réduire de moitié au moins la proportion d'hommes, de femmes et d'enfants de tout âge qui vivent dans la pauvreté sous tous ses aspects, telle que définie par chaque pays et quelles qu'en soient les formes.",
                    "Mettre en place des systèmes et mesures de protection sociale pour tous, adaptés au contexte national, y compris des socles de protection sociale, et faire en sorte que, d'ici à 2030, une part importante des pauvres et des personnes vulnérables en bénéficient.",
                    "D'ici à 2030, faire en sorte que tous les hommes et les femmes, en particulier les pauvres et les personnes vulnérables, aient les mêmes droits aux ressources économiques et qu'ils aient accès aux services de base, à la propriété et au contrôle des terres et à d'autres formes de propriété, à l'héritage et aux ressources naturelles et à des nouvelles technologies et des services financiers adéquats, y compris la microfinance.",
                    "D'ici à 2030, renforcer la résilience des pauvres et des personnes en situation vulnérable et réduire leur exposition et leur vulnérabilité aux phénomènes climatiques extrêmes et à d'autres chocs et catastrophes d'ordre économique, social ou environnemental.",
                    "Garantir une mobilisation importante de ressources provenant de sources multiples, y compris par le renforcement de la coopération pour le développement, afin de doter les pays en développement, en particulier les pays les moins avancés, de moyens adéquats et prévisibles de mettre en œuvre des programmes et politiques visant à mettre fin à la pauvreté sous toutes ses formes.",
                    "Mettre en place aux niveaux national, régional et international des principes de politique générale viables, qui se fondent sur des stratégies de développement favorables aux pauvres et soucieuses de la problématique hommes-femmes, d'accélérer l'investissement dans des mesures d'élimination de la pauvreté.",
                ]
            ],
            [
                'odd' => "Éliminer la faim, assurer la sécurité alimentaire, améliorer et promouvoir l'agriculture durable",
                'cibles' => [
                    "D'ici à 2030, éliminer la faim et faire en sorte que chacun, en particulier les pauvres et les personnes en situation vulnérable, y compris les nourrissons, ait accès tout au long de l'année à une alimentation saine, nutritive et suffisante.",
                    "D'ici à 2030, mettre fin à toutes les formes de malnutrition, y compris en réalisant d'ici à 2025 les objectifs arrêtés à l'échelle internationale relatifs aux retards de croissance et à l'émaciation parmi les enfants de moins de 5 ans, et répondre aux besoins nutritionnels des adolescentes, des femmes enceintes ou allaitantes et des personnes âgées.",
                    "D'ici à 2030, doubler la productivité agricole et les revenus des petits producteurs alimentaires, en particulier les femmes, les autochtones, les exploitants familiaux, les éleveurs et les pêcheurs, y compris en assurant l'égalité d'accès aux terres, aux autres ressources productives et intrants, au savoir, aux services financiers, aux marchés et aux possibilités d'ajout de valeur et d'emploi autres qu'agricoles.",
                    "D'ici à 2030, assurer la viabilité des systèmes de production alimentaire et mettre en œuvre des pratiques agricoles résilientes qui permettent d'accroître la productivité et la production, contribuent à la préservation des écosystèmes, renforcent les capacités d'adaptation aux changements climatiques, aux phénomènes météorologiques extrêmes, à la sécheresse, aux inondations et à d'autres catastrophes et améliorent progressivement la qualité des terres et des sols.",
                    "D'ici à 2020, préserver la diversité génétique des semences, des cultures et des animaux d'élevage ou domestiqués et des espèces sauvages apparentées, y compris au moyen de banques de semences et de plantes bien gérées et diversifiées aux niveaux national, régional et international, et favoriser l'accès aux avantages que présentent l'utilisation des ressources génétiques et du savoir traditionnel associé et le partage juste et équitable de ces avantages, ainsi que cela a été décidé à l'échelle internationale.",
                    "Accroître, notamment dans le cadre du renforcement de la coopération internationale, l'investissement en faveur de l'infrastructure rurale, des services de recherche et de vulgarisation agricoles et de la mise au point de technologies et de banques de gènes de plantes et d'animaux d'élevage, afin de renforcer les capacités productives agricoles des pays en développement, en particulier des pays les moins avancés.",
                    "Corriger et prévenir les restrictions et distorsions commerciales sur les marchés agricoles mondiaux, y compris par l'élimination parallèle de toutes les formes de subventions aux exportations agricoles et de toutes les mesures relatives aux exportations aux effets similaires, conformément au mandat du Cycle de développement de Doha.",
                    "Adopter des mesures visant à assurer le bon fonctionnement des marchés de denrées alimentaires et des produits dérivés et faciliter l'accès rapide aux informations relatives aux marchés, y compris les réserves alimentaires, afin de contribuer à limiter l'extrême volatilité du prix des denrées alimentaire.",
                ]
            ],
            [
                'odd' => "Permettre à tous de vivre en bonne santé et promouvoir le bien-être de tous à tout âge",
                'cibles' => [
                    "D'ici à 2030, faire passer le taux mondial de mortalité maternelle au-dessous de 70 pour 100 000 naissances vivantes.",
                    "D'ici à 2030, éliminer les décès évitables de nouveau-nés et d'enfants de moins de 5 ans, tous les pays devant chercher à ramener la mortalité néonatale à 12 pour 1 000 naissances vivantes au plus et la mortalité des enfants de moins de 5 ans à 25 pour 1 000 naissances vivantes au plus.",
                    "D'ici à 2030, mettre fin à l'épidémie de sida, à la tuberculose, au paludisme et aux maladies tropicales négligées et combattre l'hépatite, les maladies transmises par l'eau et autres maladies transmissibles.",
                    "D'ici à 2030, réduire d'un tiers, par la prévention et le traitement, le taux de mortalité prématurée due à des maladies non transmissibles et promouvoir la santé mentale et le bien-être.",
                    "Renforcer la prévention et le traitement de l'abus de substances psychoactives, notamment de stupéfiants et d'alcool",
                    "D'ici à 2020, diminuer de moitié à l'échelle mondiale le nombre de décès et de blessures dus à des accidents de la route",
                    "D'ici à 2030, assurer l'accès de tous à des services de soins de santé sexuelle et procréative, y compris à des fins de planification familiale, d'information et d'éducation, et la prise en compte de la santé procréative dans les stratégies et programmes nationaux",
                    "Faire en sorte que chacun bénéficie d'une couverture sanitaire universelle, comprenant une protection contre les risques financiers et donnant accès à des services de santé essentiels de qualité et à des médicaments et vaccins essentiels sûrs, efficaces, de qualité et d'un coût abordable",
                    "D'ici à 2030, réduire nettement le nombre de décès et de maladies dus à des substances chimiques dangereuses et la pollution et à la contamination de l'air, de l'eau et du sol.",
                    "Renforcer dans tous les pays, selon qu'il convient, l'application de la Convention-cadre de l'Organisation mondiale de la Santé pour la lutte antitabac.",
                    "Appuyer la recherche et la mise au point de vaccins et de médicaments contre les maladies, transmissibles ou non, qui touchent principalement les habitants des pays en développement, donner accès, à un coût abordable, à des médicaments et vaccins essentiels, conformément à la Déclaration de Doha sur l'Accord sur les ADPIC et la santé publique, qui réaffirme le droit qu'ont les pays en développement de tirer pleinement parti des dispositions de l'Accord sur les aspects des droits de propriété intellectuelle qui touchent au commerce relatives à la marge de manœuvre nécessaire pour protéger la santé publique et, en particulier, assurer l'accès universel aux médicaments.",
                    "Accroître considérablement le budget de la santé et le recrutement, le perfectionnement, la formation et le maintien en poste du personnel de santé dans les pays en développement, notamment dans les pays les moins avancés et les petits États insulaires en développement.",
                    "Renforcer les moyens dont disposent tous les pays, en particulier les pays en développement, en matière d'alerte rapide, de réduction des risques et de gestion des risques sanitaires nationaux et mondiaux.",
                ]
            ],
            [
                'odd' => "Assurer l'accès de tous à une éducation de qualité, sur un pied d'égalité, et promouvoir les possibilités d'apprentissage tout au long de la vie",
                'cibles' => [
                    "D'ici à 2030, faire en sorte que toutes les filles et tous les garçons suivent, sur un pied d'égalité, un cycle complet d'enseignement primaire et secondaire gratuit et de qualité, qui débouche sur un apprentissage véritablement utile.",
                    "D'ici à 2030, faire en sorte que toutes les filles et tous les garçons aient accès à des activités de développement et de soins de la petite enfance et à une éducation préscolaire de qualité qui les préparent à suivre un enseignement primaire.",
                    "D'ici à 2030, faire en sorte que les femmes et les hommes aient tous accès dans des conditions d'égalité à un enseignement technique, professionnel ou tertiaire, y compris universitaire, de qualité et d'un coût abordable.",
                    "D'ici à 2030, augmenter considérablement le nombre de jeunes et d'adultes disposant des compétences, notamment techniques et professionnelles, nécessaires à l'emploi, à l'obtention d'un travail décent et à l'entrepreneuriat.",
                    "D'ici à 2030, éliminer les inégalités entre les sexes dans le domaine de l'éducation et assurer l'égalité d'accès des personnes vulnérables, y compris les personnes handicapées, les autochtones et les enfants en situation vulnérable, à tous les niveaux d'enseignement et de formation professionnelle.",
                    "D'ici à 2030, veiller à ce que tous les jeunes et une proportion considérable d'adultes, hommes et femmes, sachent lire, écrire et compter.",
                    "D'ici à 2030, faire en sorte que tous les élèves acquièrent les connaissances et compétences nécessaires pour promouvoir le développement durable, notamment par l'éducation en faveur du développement et de modes de vie durables, des droits de l'homme, de l'égalité des sexes, de la promotion d'une culture de paix et de non-violence, de la citoyenneté mondiale et de l'appréciation de la diversité culturelle et de la contribution de la culture au développement durable.",
                    "Faire construire des établissements scolaires qui soient adaptés aux enfants, aux personnes handicapées et aux deux sexes ou adapter les établissements existants à cette fin et fournir un cadre d'apprentissage effectif qui soit sûr, exempt de violence et accessible à tous.",
                    "D'ici à 2020, augmenter considérablement à l'échelle mondiale le nombre de bourses d'études offertes aux pays en développement, en particulier aux pays les moins avancés, aux petits États insulaires en développement et aux pays d'Afrique, pour financer le suivi d'études supérieures, y compris la formation professionnelle, les cursus informatiques, techniques et scientifiques et les études d'ingénieur, dans des pays développés et d'autres pays en développement.",
                    "D'ici à 2030, accroître considérablement le nombre d'enseignants qualifiés, notamment au moyen de la coopération internationale pour la formation d'enseignants dans les pays en développement, surtout dans les pays les moins avancés et les petits États insulaires en développement.",
                ]
            ],
            [
                'odd' => "Parvenir à l'égalité des sexes et autonomiser toutes les femmes et les filles",
                'cibles' => [
                    "Mettre fin, dans le monde entier, à toutes les formes de discrimination à l'égard des femmes et des filles.",
                    "Éliminer de la vie publique et de la vie privée toutes les formes de violence faite aux femmes et aux filles, y compris la traite et l'exploitation sexuelle et d'autres types d'exploitation.",
                    "Éliminer toutes les pratiques préjudiciables, telles que le mariage des enfants, le mariage précoce ou forcé et la mutilation génitale féminine.",
                    "Faire une place aux soins et travaux domestiques non rémunérés et les valoriser, par l'apport de services publics, d'infrastructures et de politiques de protection sociale et la promotion du partage des responsabilités dans le ménage et la famille, en fonction du contexte national.",
                    "Garantir la participation entière et effective des femmes et leur accès en toute égalité aux fonctions de direction à tous les niveaux de décision, dans la vie politique, économique et publique.",
                    "Assurer l'accès de tous aux soins de santé sexuelle et procréative et faire en sorte que chacun puisse exercer ses droits en matière de procréation, ainsi qu'il a été décidé dans le Programme d'action de la Conférence internationale sur la population et le développement et le Programme d'action de Beijing et les documents finals des conférences d'examen qui ont suivi.",
                    "Entreprendre des réformes visant à donner aux femmes les mêmes droits aux ressources économiques, ainsi qu'à l'accès à la propriété et au contrôle des terres et d'autres formes de propriété, aux services financiers, à l'héritage et aux ressources naturelles, dans le respect du droit interne.",
                    "Renforcer l'utilisation des technologies clefs, en particulier l'informatique et les communications, pour promouvoir l'autonomisation des femmes.",
                    "Adopter des politiques bien conçues et des dispositions législatives applicables en faveur de la promotion de l'égalité des sexes et de l'autonomisation de toutes les femmes et de toutes les filles à tous les niveaux et renforcer celles qui existent.",
                ]
            ],
            [
                'odd' => "Garantir l'accès de tous à l'eau et à l'assainissement et assurer une gestion durable des ressources en eau",
                'cibles' => [
                    "D'ici à 2030, assurer l'accès universel et équitable à l'eau potable, à un coût abordable.",
                    "D'ici à 2030, assurer l'accès de tous, dans des conditions équitables, à des services d'assainissement et d'hygiène adéquats et mettre fin à la défécation en plein air, en accordant une attention particulière aux besoins des femmes et des filles et des personnes en situation vulnérable.",
                    "D'ici à 2030, améliorer la qualité de l'eau en réduisant la pollution, en éliminant l'immersion de déchets et en réduisant au minimum les émissions de produits chimiques et de matières dangereuses, en diminuant de moitié la proportion d'eaux usées non traitées et en augmentant considérablement à l'échelle mondiale le recyclage et la réutilisation sans danger de l'eau.",
                    "D'ici à 2030, augmenter considérablement l'utilisation rationnelle des ressources en eau dans tous les secteurs et garantir la viabilité des retraits et de l'approvisionnement en eau douce afin de tenir compte de la pénurie d'eau et de réduire nettement le nombre de personnes qui souffrent du manque d'eau.",
                    "D'ici à 2030, mettre en œuvre une gestion intégrée des ressources en eau à tous les niveaux, y compris au moyen de la coopération transfrontière selon qu'il convient.",
                    "D'ici à 2020, protéger et restaurer les écosystèmes liés à l'eau, notamment les montagnes, les forêts, les zones humides, les rivières, les aquifères et les lacs.",
                    "D'ici à 2030, développer la coopération internationale et l'appui au renforcement des capacités des pays en développement en ce qui concerne les activités et programmes relatifs à l'eau et à l'assainissement, y compris la collecte de l'eau, la désalinisation, l'utilisation rationnelle de l'eau, le traitement des eaux usées, le recyclage et les techniques de réutilisation.",
                    "Appuyer et renforcer la participation de la population locale à l'amélioration de la gestion de l'eau et de l'assainissement.",
                ]
            ],
            [
                'odd' => "Garantir l'accès de tous à des services énergétiques fiables, durables et modernes, à un coût abordable",
                'cibles' => [
                    "D'ici à 2030, garantir l'accès de tous à des services énergétiques fiables et modernes, à un coût abordable.",
                    "D'ici à 2030, accroître nettement la part de l'énergie renouvelable dans le bouquet énergétique mondial.",
                    "D'ici à 2030, multiplier par deux le taux mondial d'amélioration de l'efficacité énergétique.",
                    "D'ici à 2030, renforcer la coopération internationale en vue de faciliter l'accès à la recherche et aux technologies relatives à l'énergie propre, notamment l'énergie renouvelable, l'efficacité énergétique et les nouvelles technologies relatives aux combustibles fossiles propres, et promouvoir l'investissement dans l'infrastructure énergétique et les technologies relatives à l'énergie propre.",
                    "D'ici à 2030, développer l'infrastructure et améliorer la technologie afin d'approvisionner en services énergétiques modernes et durables tous les habitants des pays en développement, en particulier des pays les moins avancés, des petits États insulaires en développement et des pays en développement sans littoral, dans le respect des programmes d'aide qui les concernent.",
                ]
            ],
            [
                'odd' => "Promouvoir une croissance économique soutenue, partagée et durable, le plein emploi productif et un travail décent pour tous",
                'cibles' => [
                    "Maintenir un taux de croissance économique par habitant adapté au contexte national et, en particulier, un taux de croissance annuelle du produit intérieur brut d'au moins 7 % dans les pays les moins avancés.",
                    "Parvenir à un niveau élevé de productivité économique par la diversification, la modernisation technologique et l'innovation, notamment en mettant l'accent sur les secteurs à forte valeur ajoutée et à forte intensité de main-d'œuvre.",
                    "Promouvoir des politiques axées sur le développement qui favorisent des activités productives, la création d'emplois décents, l'entrepreneuriat, la créativité et l'innovation et stimulent la croissance des microentreprises et des petites et moyennes entreprises et facilitent leur intégration dans le secteur formel, y compris par l'accès aux services financiers.",
                    "Améliorer progressivement, jusqu'en 2030, l'efficience de l'utilisation des ressources mondiales du point de vue de la consommation comme de la production et s'attacher à ce que la croissance économique n'entraîne plus la dégradation de l'environnement, comme prévu dans le cadre décennal de programmation relatif à la consommation et à la production durables, les pays développés montrant l'exemple en la matière.",
                    "D'ici à 2030, parvenir au plein emploi productif et garantir à toutes les femmes et à tous les hommes, y compris les jeunes et les personnes handicapées, un travail décent et un salaire égal pour un travail de valeur égale.",
                    "D'ici à 2020, réduire considérablement la proportion de jeunes non scolarisés et sans emploi ni formation.",
                    "Prendre des mesures immédiates et efficaces pour supprimer le travail forcé, mettre fin à l'esclavage moderne et à la traite d'êtres humains, interdire et éliminer les pires formes de travail des enfants, y compris le recrutement et l'utilisation d'enfants soldats et, d'ici à 2025, mettre fin au travail des enfants sous toutes ses formes.",
                    "Défendre les droits des travailleurs, promouvoir la sécurité sur le lieu de travail et assurer la protection de tous les travailleurs, y compris les migrants, en particulier les femmes, et ceux qui ont un emploi précaire.",
                    "D'ici à 2030, élaborer et mettre en œuvre des politiques visant à développer un tourisme durable qui crée des emplois et mette en valeur la culture et les produits locaux.",
                    "Renforcer la capacité des institutions financières nationales de favoriser et généraliser l'accès de tous aux services bancaires et financiers et aux services d'assurance.",
                    "Accroître l'appui apporté dans le cadre de l'initiative Aide pour le commerce aux pays en développement, en particulier aux pays les moins avancés, y compris par l'intermédiaire du cadre intégré renforcé pour l'assistance technique liée au commerce en faveur des pays les moins avancés.",
                    "D'ici à 2020, élaborer et mettre en œuvre une stratégie mondiale en faveur de l'emploi des jeunes et appliquer le Pacte mondial pour l'emploi de l'Organisation internationale du Travail.",
                ]
            ],
            [
                'odd' => "Bâtir une infrastructure résiliente, promouvoir une industrialisation durable qui profite à tous et encourager l'innovation",
                'cibles' => [
                    "Mettre en place une infrastructure de qualité, fiable, durable et résiliente, y compris une infrastructure régionale et transfrontière, pour favoriser le développement économique et le bien-être de l'être humain, en mettant l'accent sur un accès universel, à un coût abordable et dans des conditions d'équité.",
                    "Promouvoir une industrialisation durable qui profite à tous et, d'ici à 2030, augmenter nettement la contribution de l'industrie à l'emploi et au produit intérieur brut, en fonction du contexte national, et la multiplier par deux dans les pays les moins avancés.",
                    "Accroître, en particulier dans les pays en développement, l'accès des entreprises, notamment des petites entreprises industrielles, aux services financiers, y compris aux prêts consentis à des conditions abordables, et leur intégration dans les chaînes de valeur et sur les marchés.",
                    "D'ici à 2030, moderniser l'infrastructure et adapter les industries afin de les rendre durables, par une utilisation plus rationnelle des ressources et un recours accru aux technologies et procédés industriels propres et respectueux de l'environnement, chaque pays agissant dans la mesure de ses moyens.",
                    "Renforcer la recherche scientifique, perfectionner les capacités technologiques des secteurs industriels de tous les pays, en particulier des pays en développement, notamment en encourageant l'innovation et en augmentant considérablement le nombre de personnes travaillant dans le secteur de la recherche et du développement pour 1 million d'habitants et en accroissant les dépenses publiques et privées consacrées à la recherche et au développement d'ici à 2030.",
                    "Faciliter la mise en place d'une infrastructure durable et résiliente dans les pays en développement en renforçant l'appui financier, technologique et technique apporté aux pays d'Afrique, aux pays les moins avancés, aux pays en développement sans littoral et aux petits États insulaires en développement.",
                    "Soutenir la recherche-développement et l'innovation technologiques nationales dans les pays en développement, notamment en instaurant des conditions propices, entre autres, à la diversification industrielle et à l'ajout de valeur aux marchandises.",
                    "Accroître nettement l'accès aux technologies de l'information et de la communication et faire en sorte que tous les habitants des pays les moins avancés aient accès à Internet à un coût abordable d'ici à 2020.",
                ]
            ],
            [
                'odd' => "Réduire les inégalités dans les pays et d'un pays à l'autre",
                'cibles' => [
                    "D'ici à 2030, faire en sorte, au moyen d'améliorations progressives, que les revenus des 40 % les plus pauvres de la population augmentent plus rapidement que le revenu moyen national, et ce de manière durable.",
                    "D'ici à 2030, autonomiser toutes les personnes et favoriser leur intégration sociale, économique et politique, indépendamment de leur âge, de leur sexe, de leur handicap, de leur race, de leur appartenance ethnique, de leurs origines, de leur religion ou de leur statut économique ou autre.",
                    "Assurer l'égalité des chances et réduire l'inégalité des résultats, notamment en éliminant les lois, politiques et pratiques discriminatoires et en promouvant l'adoption de lois, politiques et mesures adéquates en la matière.",
                    "Adopter des politiques, notamment sur les plans budgétaire, salarial et dans le domaine de la protection sociale, et parvenir progressivement à une plus grande égalité.",
                    "Améliorer la réglementation et la surveillance des institutions et marchés financiers mondiaux et renforcer l'application des règles.",
                    "Faire en sorte que les pays en développement soient davantage représentés et entendus lors de la prise de décisions dans les institutions économiques et financières internationales, afin que celles-ci soient plus efficaces, crédibles, transparentes et légitimes.",
                    "Faciliter la migration et la mobilité de façon ordonnée, sans danger, régulière et responsable, notamment par la mise en œuvre de politiques de migration planifiées et bien gérées.",
                    "Mettre en œuvre le principe d'un traitement spécial et différencié pour les pays en développement, en particulier les pays les moins avancés, conformément aux accords de l'Organisation mondiale du commerce.",
                    "Stimuler l'aide publique au développement et les flux financiers, y compris les investissements étrangers directs, pour les États qui en ont le plus besoin, en particulier les pays les moins avancés, les pays d'Afrique, les petits États insulaires en développement et les pays en développement sans littoral, conformément à leurs plans et programmes nationaux.",
                    "D'ici à 2030, faire baisser au-dessous de 3 pour cent les coûts de transaction des envois de fonds effectués par les migrants et éliminer les couloirs de transfert de fonds dont les coûts sont supérieurs à 5 %.",
                ]
            ],
            [
                'odd' => "Faire en sorte que les villes et les établissements humains soient ouverts à tous, sûrs, résilients et durables",
                'cibles' => [
                    "D'ici à 2030, assurer l'accès de tous à un logement et des services de base adéquats et sûrs, à un coût abordable, et assainir les quartiers de taudis.",
                    "D'ici à 2030, assurer l'accès de tous à des systèmes de transport sûrs, accessibles et viables, à un coût abordable, en améliorant la sécurité routière, notamment en développant les transports publics, une attention particulière devant être accordée aux besoins des personnes en situation vulnérable, des femmes, des enfants, des personnes handicapées et des personnes âgées.",
                    "D'ici à 2030, renforcer l'urbanisation durable pour tous et les capacités de planification et de gestion participatives, intégrées et durables des établissements humains dans tous les pays.",
                    "Renforcer les efforts de protection et de préservation du patrimoine culturel et naturel mondial.",
                    "D'ici à 2030, réduire considérablement le nombre de personnes tuées et le nombre de personnes touchées par les catastrophes, y compris celles d'origine hydrique, et réduire considérablement le montant des pertes économiques qui sont dues directement à ces catastrophes exprimé en proportion du produit intérieur brut mondial, l'accent étant mis sur la protection des pauvres et des personnes en situation vulnérable.",
                    "D'ici à 2030, réduire l'impact environnemental négatif des villes par habitant, y compris en accordant une attention particulière à la qualité de l'air et à la gestion, notamment municipale, des déchets.",
                    "D'ici à 2030, assurer l'accès de tous, en particulier des femmes et des enfants, des personnes âgées et des personnes handicapées, à des espaces verts et des espaces publics sûrs.",
                    "Favoriser l'établissement de liens économiques, sociaux et environnementaux positifs entre zones urbaines, périurbaines et rurales en renforçant la planification du développement à l'échelle nationale et régionale.",
                    "D'ici à 2020, accroître considérablement le nombre de villes et d'établissements humains qui adoptent et mettent en œuvre des politiques et plans d'action intégrés en faveur de l'insertion de tous, de l'utilisation rationnelle des ressources, de l'adaptation aux effets des changements climatiques et de leur atténuation et de la résilience face aux catastrophes, et élaborer et mettre en œuvre, conformément au Cadre de Sendai pour la réduction des risques de catastrophe (2015-2030), une gestion globale des risques de catastrophe à tous les niveaux.",
                    "Aider les pays les moins avancés, y compris par une assistance financière et technique, à construire des bâtiments durables et résilients en utilisant des matériaux locaux.",
                ]
            ],
            [
                'odd' => "Établir des modes de consommation et de production durables",
                'cibles' => [
                    "Mettre en œuvre le Cadre décennal de programmation concernant les modes de consommation et de production durables avec la participation de tous les pays, les pays développés montrant l'exemple en la matière, compte tenu du degré de développement et des capacités des pays en développement.",
                    "D'ici à 2030, parvenir à une gestion durable et à une utilisation rationnelle des ressources naturelles.",
                    "D'ici à 2030, réduire de moitié à l'échelle mondiale le volume de déchets alimentaires par habitant au niveau de la distribution comme de la consommation et réduire les pertes de produits alimentaires tout au long des chaînes de production et d'approvisionnement, y compris les pertes après récolte.",
                    "D'ici à 2020, instaurer une gestion écologiquement rationnelle des produits chimiques et de tous les déchets tout au long de leur cycle de vie, conformément aux principes directeurs arrêtés à l'échelle internationale, et réduire considérablement leur déversement dans l'air, l'eau et le sol, afin de minimiser leurs effets négatifs sur la santé et l'environnement.",
                    "D'ici à 2030, réduire considérablement la production de déchets par la prévention, la réduction, le recyclage et la réutilisation.",
                    "Encourager les entreprises, en particulier les grandes et les transnationales, à adopter des pratiques viables et à intégrer dans les rapports qu'elles établissent des informations sur la viabilité.",
                    "Promouvoir des pratiques durables dans le cadre de la passation des marchés publics, conformément aux politiques et priorités nationales.",
                    "D'ici à 2030, faire en sorte que toutes les personnes, partout dans le monde, aient les informations et connaissances nécessaires au développement durable et à un style de vie en harmonie avec la nature.",
                    "Aider les pays en développement à se doter des moyens scientifiques et technologiques qui leur permettent de s'orienter vers des modes de consommation et de production plus durables.",
                    "Mettre au point et utiliser des outils de contrôle des impacts sur le développement durable, pour un tourisme durable qui crée des emplois et met en valeur la culture et les produits locaux.",
                    "Rationaliser les subventions aux combustibles fossiles qui sont source de gaspillage, en éliminant les distorsions du marché, selon le contexte national, y compris par la restructuration de la fiscalité et l'élimination progressive des subventions nuisibles, afin de mettre en évidence leur impact sur l'environnement, en tenant pleinement compte des besoins et de la situation propres aux pays en développement et en réduisant au minimum les éventuels effets pernicieux sur le développement de ces pays tout en protégeant les pauvres et les collectivités concernées.",
                ]
            ],
            [
                'odd' => "Prendre d'urgence des mesures pour lutter contre les changements climatiques et leurs répercussions",
                'cibles' => [
                    "Renforcer, dans tous les pays, la résilience et les capacités d'adaptation face aux aléas climatiques et aux catastrophes naturelles liées au climat.",
                    "Incorporer des mesures relatives aux changements climatiques dans les politiques, les stratégies et la planification nationales.",
                    "Améliorer l'éducation, la sensibilisation et les capacités individuelles et institutionnelles en ce qui concerne l'adaptation aux changements climatiques, l'atténuation de leurs effets et la réduction de leur impact et les systèmes d'alerte rapide.",
                    "Mettre en œuvre l'engagement que les pays développés parties à la Convention-cadre des Nations Unies sur les changements climatiques ont pris de mobiliser ensemble auprès de multiples sources 100 milliards de dollars des États-Unis par an d'ici à 2020 pour répondre aux besoins des pays en développement en ce qui concerne les mesures concrètes d'atténuation et la transparence de leur mise en œuvre et rendre le Fonds vert pour le climat pleinement opérationnel en le dotant dans les plus brefs délais des moyens financiers nécessaires.",
                    "Promouvoir des mécanismes de renforcement des capacités afin que les pays les moins avancés et les petits États insulaires en développement se dotent de moyens efficaces de planification et de gestion pour faire face aux changements climatiques, l'accent étant mis notamment sur les femmes, les jeunes, la population locale et les groupes marginalisés.",
                ]
            ],
            [
                'odd' => "Conserver et exploiter de manière durable les océans, les mers et les ressources marines aux fins du développement durable",
                'cibles' => [
                    "D'ici à 2025, prévenir et réduire nettement la pollution marine de tous types, en particulier celle résultant des activités terrestres, y compris les déchets en mer et la pollution par les nutriments.",
                    "D'ici à 2020, gérer et protéger durablement les écosystèmes marins et côtiers, notamment en renforçant leur résilience, afin d'éviter les graves conséquences de leur dégradation et prendre des mesures en faveur de leur restauration pour rétablir la santé et la productivité des océans.",
                    "Réduire au maximum l'acidification des océans et lutter contre ses effets, notamment en renforçant la coopération scientifique à tous les niveaux.",
                    "D'ici à 2020, réglementer efficacement la pêche, mettre un terme à la surpêche, à la pêche illicite, non déclarée et non réglementée et aux pratiques de pêche destructrices et exécuter des plans de gestion fondés sur des données scientifiques, l'objectif étant de rétablir les stocks de poissons le plus rapidement possible, au moins à des niveaux permettant d'obtenir un rendement constant maximal compte tenu des caractéristiques biologiques.",
                    "D'ici à 2020, préserver au moins 10 % des zones marines et côtières, conformément au droit national et international et compte tenu des meilleures informations scientifiques disponibles.",
                    "D'ici à 2020, interdire les subventions à la pêche qui contribuent à la surcapacité et à la surpêche, supprimer celles qui favorisent la pêche illicite, non déclarée et non réglementée et s'abstenir d'en accorder de nouvelles, sachant que l'octroi d'un traitement spécial et différencié efficace et approprié aux pays en développement et aux pays les moins avancés doit faire partie intégrante des négociations sur les subventions à la pêche menées dans le cadre de l'Organisation mondiale du commerce.",
                    "D'ici à 2030, faire mieux bénéficier les petits États insulaires en développement et les pays les moins avancés des retombées économiques de l'exploitation durable des ressources marines, notamment grâce à une gestion durable des pêches, de l'aquaculture et du tourisme.",
                    "Approfondir les connaissances scientifiques, renforcer les capacités de recherche et transférer les techniques marines, conformément aux Critères et principes directeurs de la Commission océanographique intergouvernementale concernant le transfert de techniques marines, l'objectif étant d'améliorer la santé des océans et de renforcer la contribution de la biodiversité marine au développement des pays en développement, en particulier des petits États insulaires en développement et des pays les moins avancés.",
                    "Garantir aux petits pêcheurs l'accès aux ressources marines et aux marchés.",
                    "Améliorer la conservation des océans et de leurs ressources et les exploiter de manière plus durable en application des dispositions du droit international, énoncées dans la Convention des Nations Unies sur le droit de la mer, qui fournit le cadre juridique requis pour la conservation et l'exploitation durable des océans et de leurs ressources, comme il est rappelé au paragraphe 158 de « L'avenir que nous voulons ».",
                ]
            ],
            [
                'odd' => "Préserver et restaurer les écosystèmes terrestres, gérer durablement les forêts, lutter contre la désertification et enrayer la perte de biodiversité",
                'cibles' => [
                    "D'ici à 2020, garantir la préservation, la restauration et l'exploitation durable des écosystèmes terrestres et des écosystèmes d'eau douce et des services connexes, en particulier les forêts, les zones humides, les montagnes et les zones arides, conformément aux obligations découlant des accords internationaux.",
                    "D'ici à 2020, promouvoir la gestion durable de tous les types de forêt, mettre un terme à la déforestation, restaurer les forêts dégradées et accroître considérablement le boisement et le reboisement au niveau mondial.",
                    "D'ici à 2030, lutter contre la désertification, restaurer les terres et sols dégradés, notamment les terres touchées par la désertification, la sécheresse et les inondations, et s'efforcer de parvenir à un monde sans dégradation des sols.",
                    "D'ici à 2030, assurer la préservation des écosystèmes montagneux, notamment de leur biodiversité, afin de mieux tirer parti de leurs bienfaits essentiels pour le développement durable.",
                    "Prendre d'urgence des mesures énergiques pour réduire la dégradation du milieu naturel, mettre un terme à l'appauvrissement de la biodiversité et, d'ici à 2020, protéger les espèces menacées et prévenir leur extinction.",
                    "Favoriser le partage juste et équitable des bénéfices découlant de l'utilisation des ressources génétiques et promouvoir un accès approprié à celles-ci, ainsi que cela a été décidé à l'échelle internationale.",
                    "Prendre d'urgence des mesures pour mettre un terme au braconnage et au trafic d'espèces végétales et animales protégées et s'attaquer au problème sous l'angle de l'offre et de la demande.",
                    "D'ici à 2020, prendre des mesures pour empêcher l'introduction d'espèces exotiques envahissantes, atténuer sensiblement les effets que ces espèces ont sur les écosystèmes terrestres et aquatiques et contrôler ou éradiquer les espèces prioritaires.",
                    "D'ici à 2020, intégrer la protection des écosystèmes et de la biodiversité dans la planification nationale, dans les mécanismes de développement, dans les stratégies de réduction de la pauvreté et dans la comptabilité",
                    "Mobiliser des ressources financières de toutes provenances et les augmenter nettement pour préserver la biodiversité et les écosystèmes et les exploiter durablement",
                    "Mobiliser d'importantes ressources de toutes provenances et à tous les niveaux pour financer la gestion durable des forêts et inciter les pays en développement à privilégier ce type de gestion, notamment aux fins de la préservation des forêts et du reboisement",
                    "Apporter, à l'échelon mondial, un soutien accru à l'action menée pour lutter contre le braconnage et le trafic d'espèces protégées, notamment en donnant aux populations locales d'autres moyens d'assurer durablement leur subsistance",
                ]
            ],
            [
                'odd' => "Promouvoir l'avènement de sociétés pacifiques et ouvertes à tous, assurer l'accès à la justice et mettre en place des institutions efficaces, responsables et ouvertes à tous",
                'cibles' => [
                    "Réduire nettement, partout dans le monde, toutes les formes de violence et les taux de mortalité qui y sont associés.",
                    "Mettre un terme à la maltraitance, à l'exploitation et à la traite, et à toutes les formes de violence et de torture dont sont victimes les enfants.",
                    "Promouvoir l'état de droit aux niveaux national et international et donner à tous accès à la justice dans des conditions d'égalité.",
                    "D'ici à 2030, réduire nettement les flux financiers illicites et le trafic d'armes, renforcer les activités de récupération et de restitution des biens volés et lutter contre toutes les formes de criminalité organisée.",
                    "Réduire nettement la corruption et la pratique des pots-de-vin sous toutes leurs formes.",
                    "Mettre en place des institutions efficaces, responsables et transparentes à tous les niveaux.",
                    "Faire en sorte que le dynamisme, l'ouverture, la participation et la représentation à tous les niveaux caractérisent la prise de décisions.",
                    "Élargir et renforcer la participation des pays en développement aux institutions chargées de la gouvernance au niveau mondial.",
                    "D'ici à 2030, garantir à tous une identité juridique, notamment grâce à l'enregistrement des naissances.",
                    "Garantir l'accès public à l'information et protéger les libertés fondamentales, conformément à la législation nationale et aux accords internationaux.",
                    "Appuyer, notamment dans le cadre de la coopération internationale, les institutions nationales chargées de renforcer, à tous les niveaux, les moyens de prévenir la violence et de lutter contre le terrorisme et la criminalité, en particulier dans les pays en développement.",
                    "Promouvoir et appliquer des lois et politiques non discriminatoires pour le développement durable",
                ]
            ],
            [
                'odd' => "Renforcer les moyens de mettre en œuvre le Partenariat mondial pour le développement durable et le revitaliser",
                'cibles' => [
                    "Améliorer, notamment grâce à l'aide internationale aux pays en développement, la mobilisation de ressources nationales en vue de renforcer les capacités nationales de collecte de l'impôt et d'autres recettes.",
                    "Faire en sorte que les pays développés honorent tous leurs engagements en matière d'aide publique au développement, notamment celui pris par nombre d'entre eux de consacrer 0,7% de leur revenu national brut à l'aide aux pays en développement et entre 0,15% et 0,20% à l'aide aux pays les moins avancés, les bailleurs de fonds étant encouragés à envisager de se fixer pour objectif de consacrer au moins 0,20% de leur revenu national brut à l'aide aux pays les moins avancés.",
                    "Mobiliser des ressources financières supplémentaires de diverses provenances en faveur des pays en développement.",
                    "Aider les pays en développement à assurer la viabilité à long terme de leur dette au moyen de politiques concertées visant à favoriser le financement de la dette, son allègement ou sa restructuration, selon le cas, et réduire le surendettement en réglant le problème de la dette extérieure des pays pauvres très endettés.",
                    "Adopter et mettre en œuvre des dispositifs visant à encourager l'investissement en faveur des pays les moins avancés.",
                    "Renforcer l'accès à la science, à la technologie et à l'innovation et la coopération Nord-Sud et Sud-Sud et la coopération triangulaire régionale et internationale dans ces domaines et améliorer le partage des savoirs selon des modalités arrêtées d'un commun accord, notamment en coordonnant mieux les mécanismes existants, en particulier au niveau des organismes des Nations Unies, et dans le cadre d'un mécanisme mondial de facilitation des technologies.",
                    "Promouvoir la mise au point, le transfert et la diffusion de technologies respectueuses de l'environnement en faveur des pays en développement, à des conditions favorables, y compris privilégiées et préférentielles, arrêtées d'un commun accord.",
                    "Faire en sorte que la banque de technologies et le mécanisme de renforcement des capacités scientifiques et technologiques et des capacités d'innovation des pays les moins avancés soient pleinement opérationnels d'ici à 2017 et renforcer l'utilisation des technologies clefs, en particulier de l'informatique et des communications.",
                    "Apporter, à l'échelon international, un soutien accru pour assurer le renforcement efficace et ciblé des capacités des pays en développement et appuyer ainsi les plans nationaux visant à atteindre tous les objectifs de développement durable, notamment dans le cadre de la coopération Nord-Sud et Sud-Sud et de la coopération triangulaire.",
                    "Promouvoir un système commercial multilatéral universel, réglementé, ouvert, non discriminatoire et équitable sous l'égide de l'Organisation mondiale du commerce, notamment grâce à la tenue de négociations dans le cadre du Programme de Doha pour le développement.",
                    "Accroître nettement les exportations des pays en développement, en particulier en vue de doubler la part des pays les moins avancés dans les exportations mondiales d'ici à 2020.",
                    "Permettre l'accès rapide de tous les pays les moins avancés aux marchés en franchise de droits et sans contingent, conformément aux décisions de l'Organisation mondiale du commerce, notamment en veillant à ce que les règles préférentielles applicables aux importations provenant des pays les moins avancés soient transparentes et simples et facilitent l'accès aux marchés.",
                    "Renforcer la stabilité macroéconomique mondiale, notamment en favorisant la coordination et la cohérence des politiques.",
                    "Renforcer la cohérence des politiques de développement durable.",
                    "Respecter la marge de manœuvre et l'autorité de chaque pays en ce qui concerne l'élaboration et l'application des politiques d'élimination de la pauvreté et de développement durable.",
                    "Renforcer le Partenariat mondial pour le développement durable, associé à des partenariats multipartites permettant de mobiliser et de partager des savoirs, des connaissances spécialisées, des technologies et des ressources financières, afin d'aider tous les pays, en particulier les pays en développement, à atteindre les objectifs de développement durable.",
                    "Encourager et promouvoir les partenariats publics, les partenariats public-privé et les partenariats avec la société civile, en faisant fond sur l'expérience acquise et les stratégies de financement appliquées en la matière",
                    "D'ici à 2020, apporter un soutien accru au renforcement des capacités des pays en développement, notamment des pays les moins avancés et des petits États insulaires en développement, l'objectif étant de disposer d'un beaucoup plus grand nombre de données de qualité, actualisées et exactes, ventilées par niveau de revenu, sexe, âge, race, appartenance ethnique, statut migratoire, handicap et emplacement géographique, et selon d'autres caractéristiques propres à chaque pays",
                    "D'ici à 2030, tirer parti des initiatives existantes pour établir des indicateurs de progrès en matière de développement durable qui viendraient compléter le produit intérieur brut, et appuyer le renforcement des capacités statistiques des pays en développement",
                ]
            ],
        ];

        //\App\Models\Odd::truncate();
        foreach ($odds as $key => $odd) {

            $objectif = $odd["odd"];

            $objectif = \App\Models\Odd::updateOrCreate(
                ['odd' => $objectif],
                ["slug" => Str::slug(SlugHelper::generateUnique($objectif, \App\Models\Odd::class))]
            );

            $cibles = $odd["cibles"];
            // SlugHelper::generateUnique($name, static::class, 'slug', $this->id ?? null);
            foreach ($cibles as $key => $cible) {
                \App\Models\Cible::updateOrCreate(
                    ['cible' => $cible],
                    ["slug" => Str::slug(SlugHelper::generateUnique($cible, \App\Models\Cible::class)), "oddId" => $objectif->id]
                );
            }
        }
    }
}
