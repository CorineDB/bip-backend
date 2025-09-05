<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanevasAppreciationTdrPrefaisabiliteSeeder extends Seeder
{
    protected $documentData = [
        "nom" => "Canevas d'appréciation des TDRs de préfaisabilité",
        "slug" => "canevas-appreciation-tdrs-prefaisabilite",
        "description" => "Canevas standardisé pour l'appréciation et l'évaluation des Termes de Référence de préfaisabilité",
        "type" => "formulaire",
        "evaluation_configs" => [
            "guide_notation" => [
                [
                    "appreciation" => "passe",
                    "label" => "Passé",
                    "description" => "Répond aux critères d'acceptation",
                    "couleur" => "#22c55e"
                ],
                [
                    "appreciation" => "retour",
                    "label" => "Retour",
                    "description" => "Nécessite des améliorations ou éclaircissements",
                    "couleur" => "#f59e0b"
                ],
                [
                    "appreciation" => "non_accepte",
                    "label" => "Non accepté",
                    "description" => "Ne répond pas aux critères minimums",
                    "couleur" => "#ef4444"
                ]
            ],
            "criteres_evaluation" => [
                "commentaire_obligatoire" => true,
                "seuil_acceptation" => 0,
                "regles_decision" => [
                    "passe" => [
                        "condition" => "all_passed",
                        "description" => "La présélection a été un succès (passes reçues dans toutes les questions)",
                        "message" => "Toutes les questions ont été évaluées positivement",
                        "statut_final" => "validé"
                    ],
                    "retour" => [
                        "condition" => "has_retour_but_acceptable",
                        "description" => "Retour pour un travail supplémentaire",
                        "message" => "Des améliorations sont nécessaires avant validation",
                        "statut_final" => "retour_travail_supplementaire",
                        "max_retour_allowed" => 9
                    ],
                    "non_accepte" => [
                        "condition" => "has_rejection_or_too_many_retour",
                        "description" => "Non accepté",
                        "message" => "Le TDR ne répond pas aux critères minimums",
                        "statut_final" => "rejete",
                        "triggers" => [
                            "incomplete_questions" => "Si des questions n'ont pas été complétées",
                            "has_non_accepte" => "Si une réponse à une question a été évaluée comme « Non acceptée »",
                            "too_many_retour" => "Si 10 ou plus des réponses ont été évaluées comme « Retour »"
                        ]
                    ]
                ],
                "algorithme_decision" => [
                    "etapes" => [
                        [
                            "ordre" => 1,
                            "condition" => "check_completude",
                            "description" => "Vérifier que toutes les questions ont une évaluation",
                            "action_si_echec" => "non_accepte"
                        ],
                        [
                            "ordre" => 2,
                            "condition" => "check_non_accepte",
                            "description" => "Vérifier qu'aucune question n'a été évaluée comme 'non_accepte'",
                            "action_si_echec" => "non_accepte"
                        ],
                        [
                            "ordre" => 3,
                            "condition" => "count_retour",
                            "description" => "Compter le nombre de 'retour'",
                            "seuil_max" => 9,
                            "action_si_depassement" => "non_accepte",
                            "action_si_respecte" => "check_final"
                        ],
                        [
                            "ordre" => 4,
                            "condition" => "check_final",
                            "description" => "Déterminer le résultat final",
                            "logique" => [
                                "si_aucun_retour" => "passe",
                                "si_retour_dans_limite" => "retour"
                            ]
                        ]
                    ]
                ],
                "compteurs" => [
                    "total_questions" => 0,
                    "passe" => 0,
                    "retour" => 0,
                    "non_accepte" => 0,
                    "non_evaluees" => 0
                ],
                "notifications" => [
                    "passe" => [
                        "titre" => "TDR Validé",
                        "message" => "Le TDR de préfaisabilité a été validé avec succès",
                        "type" => "success"
                    ],
                    "retour" => [
                        "titre" => "TDR à Améliorer",
                        "message" => "Le TDR nécessite des améliorations avant validation",
                        "type" => "warning"
                    ],
                    "non_accepte" => [
                        "titre" => "TDR Rejeté",
                        "message" => "Le TDR ne répond pas aux critères minimums requis",
                        "type" => "error"
                    ]
                ]
            ],
            "workflow" => [
                "etapes" => [
                    "soumission" => "Soumission du TDR pour appréciation",
                    "appreciation" => "Évaluation par l'évaluateur",
                    "decision" => "Décision finale basée sur les règles"
                ]
            ],
            "accept_text" => "En remplissant et en transmettant cette note conceptuelle de projet, je confirme que les informations sont complètes et exactes à ma connaissance. Je reconnais également que le fait de fournir intentionnellement des informations inexactes ou trompeuses peut entraîner des sanctions à mon encontre."
        ],
        "forms" => [
            // Section 1: Objectif et justification d'un nouveau projet
            [
                "element_type" => "section",
                "ordre_affichage" => 1,
                "label" => "Objectif et justification d'un nouveau projet",
                "attribut" => "section_objectif_justification",
                "key" => "section_objectif_justification",
                "description" => "Évaluation des objectifs et de la justification du nouveau projet",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Comment le projet répondrait-il aux objectifs spécifiques du Plan national de développement, du Plan sectoriel, de la Stratégie de croissance verte et de résilience climatique ou de toute autre politique gouvernementale ?",
                        "info" => "Votre réponse doit inclure des références à des programmes spécifiques et inclure des références de documents spécifiques, avec le numéro de page et le paragraphe pertinents. Des critères politiques spécifiques tels que la création d'emplois, le genre et le changement climatique doivent également être mentionnés ici. Les questions liées au changement climatique en particulier sont une caractéristique de plus en plus importante des priorités de dépenses d'investissement du gouvernement, cela doit donc être pris en compte dans votre réponse. Indiquez si le projet est lié aux contributions déterminées au niveau national",
                        "attribut" => "reponse_objectifs_politiques",
                        "type_champ" => "textarea",
                        "placeholder" => "Comment le projet répondrait-il aux objectifs spécifiques du Plan national de développement, du Plan sectoriel, de la Stratégie de croissance verte et de résilience climatique ou de toute autre politique gouvernementale ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Il a été fait référence aux stratégies nationales ou sectorielles et aux stratégies de lutte contre le changement climatique et une explication claire a été fournie sur les liens avec le projet proposé."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Il a été fait référence à des stratégies nationales ou sectorielles et aux stratégies de lutte contre le changement climatique, mais les liens vers le projet proposé sont insuffisants"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucun lien n'a été établi entre le projet proposé et les stratégies nationales ou sectorielles en matière de changement climatique"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 5000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Pourquoi ce nouveau projet est-il nécessaire ? Et quelle est l'ampleur de ce problème ou de cette opportunité ?",
                        "info" => "Décrivez le problème à résoudre ou l'opportunité à exploiter. Incluez quelques estimations numériques de base pour étayer la justification",
                        "attribut" => "necessite_nouveau_projet",
                        "type_champ" => "textarea",
                        "placeholder" => "Pourquoi ce nouveau projet est-il nécessaire ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Le problème et ses causes sont expliqués en détail (ou les opportunités sont expliquées en détail) avec quelques preuves d'ampleur."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Il n'existe pas suffisamment d'informations numériques sur l'ampleur du problème ou de l'opportunité."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Ne décrit pas le problème ou l'opportunité ou le problème/l'opportunité est décrit mais n'est pas significatif"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Ce problème ou cette opportunité pourraient-ils être traités par les districts, le secteur privé ou une ONG ?",
                        "info" => "Écrivez « Oui » ou « Non » et expliquez ensuite votre réponse en précisant pourquoi le ministère sectoriel est le seul responsable possible de la mise en œuvre du projet.",
                        "attribut" => "traitement_autres_acteurs",
                        "type_champ" => "textarea",
                        "placeholder" => "Ce problème ou cette opportunité pourraient-ils être traités par les districts, le secteur privé ou une ONG ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Il est clair que le ministère sectoriel serait le mieux placé pour mettre en œuvre cette proposition de projet."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Il s'agit de savoir si le projet pourrait être mieux mis en œuvre par le ministère sectoriel / le secteur privé / l'ONG."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative sérieuse de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Ce projet pourrait-il être mis en œuvre dans le cadre d'un PPP ?",
                        "info" => "Répondez « oui » ou « non ». Expliquez la raison de la réponse, y compris les discussions tenues avec les responsables ou les experts concernés",
                        "attribut" => "possibilite_ppp",
                        "type_champ" => "textarea",
                        "placeholder" => "Ce projet pourrait-il être mis en œuvre dans le cadre d'un PPP ? (Répondre \"oui\" ou \"non\". Expliquer la raison de la réponse, y compris les discussions tenues avec les responsables ou les experts concernés)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "La réponse fournie est crédible – si la réponse est « oui », un résumé des discussions avec des responsables ou des experts autorisés est inclus."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Une réponse est fournie mais sans explication"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "La réponse n'est pas crédible – si la réponse est « oui » mais sans aucune preuve à l'appui."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 5,
                        "label" => "Pourquoi cette proposition devrait-elle être une priorité désormais ?",
                        "info" => "Expliquez l'urgence du projet et pourquoi il devrait être mis en œuvre au cours du prochain exercice financier plutôt que du suivant",
                        "attribut" => "priorite_actuelle",
                        "type_champ" => "textarea",
                        "placeholder" => "Pourquoi cette proposition devrait-elle être une priorité désormais ? (Expliquer l'urgence du projet et pourquoi il devrait être mis en œuvre au cours du prochain exercice financier plutôt que du suivant)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "La réponse fournie montre clairement que le projet doit être mis en œuvre de toute urgence."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Le projet semble assez urgent mais les raisons ne sont pas bien expliquées."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Il ressort clairement de la réponse que le projet n'est pas urgent."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 6,
                        "label" => "Quelles seraient les conséquences si cette proposition de projet n'était pas mise en œuvre ?",
                        "info" => "Expliquez ce qui se passerait si le projet n'était pas approuvé. Les réponses pourraient inclure des scénarios allant de « peu de choses changeraient » à « des gens mourraient ». Considérez spécifiquement si le projet a des conséquences liées au changement climatique",
                        "attribut" => "consequences_non_mise_oeuvre",
                        "type_champ" => "textarea",
                        "placeholder" => "Quelles seraient les conséquences si cette proposition de projet n'était pas mise en œuvre ? (Expliquer ce qui se passerait si le projet n'était pas approuvé. Considérer spécifiquement si le projet a des conséquences liées au changement climatique)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les conséquences sont clairement décrites et sont importantes"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les conséquences sont décrites mais sont insuffisantes"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Les conséquences ne sont pas décrites ou si elles le sont, elles ne sont pas significatives"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 7,
                        "label" => "Le projet fait-il partie d'un programme et des dépenses supplémentaires sont-elles nécessaires pour rendre le projet pleinement fonctionnel et opérationnel ?",
                        "info" => "Répondez « Oui » ou « Non ». Si la réponse est « oui », veuillez fournir des détails. Si la réponse est « non », veuillez expliquer pourquoi.",
                        "attribut" => "projet_programme",
                        "type_champ" => "textarea",
                        "placeholder" => "Le projet fait-il partie d'un programme et des dépenses supplémentaires sont-elles nécessaires pour rendre le projet pleinement fonctionnel et opérationnel ? (Répondre \"Oui\" ou \"Non\". Si oui, fournir des détails. Si non, expliquer pourquoi)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "La réponse fournie est claire et crédible"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "La réponse fournie est insuffisante ou est ambiguë. Si la réponse à la question est <Oui> et qu'aucune information supplémentaire n'a été fournie comme demandé"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "La question n'a pas été répondue"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 8,
                        "label" => "Des projets similaires ont-ils déjà été développés dans votre secteur ?",
                        "info" => "Répondez « oui » ou « non ». Si la réponse est « oui », veuillez expliquer les leçons tirées des projets précédents. Si la réponse est « non », veuillez expliquer quelles mesures supplémentaires seront prises pour gérer ce risque d'inconnu",
                        "attribut" => "projets_similaires",
                        "type_champ" => "textarea",
                        "placeholder" => "Des projets similaires ont-ils déjà été développés dans votre secteur ? (Répondre \"oui\" ou \"non\". Si oui, expliquer les leçons tirées des projets précédents. Si non, expliquer quelles mesures supplémentaires seront prises pour gérer ce risque d'inconnu)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit : Il n'existe aucun précédent pour le projet dans la zone/le secteur, mais des mesures d'atténuation crédibles ont été décrites OU : Il existe des précédents et les résultats ont été positifs."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les mesures d'atténuation ne sont pas incluses ni décrites."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Soit : il n'existe aucun précédent pour le projet dans la zone/le secteur et aucune mesure d'atténuation crédible n'a été décrite, OU : il existe des précédents dans la zone/le secteur mais les résultats ont été médiocres et aucune mesure d'atténuation évidente n'a été décrite."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 9,
                        "label" => "Quels sont les objectifs du projet ?",
                        "info" => "Décrivez ce que le projet vise à réaliser et dans quel délai",
                        "attribut" => "objectifs_projet",
                        "type_champ" => "textarea",
                        "placeholder" => "Quels sont les objectifs du projet ? (Décrire ce que le projet vise à réaliser et dans quel délai)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Objectifs clairement décrits"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Objectifs décrits mais pas suffisamment précis ou réalisables"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Objectifs non décrits"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 10,
                        "label" => "Quelles seront les activités et les résultats du projet ?",
                        "info" => "Les activités peuvent inclure la conception, la construction, l'acquisition d'équipements et de services. Les extrants sont souvent des preuves physiques que les activités ont été réalisées",
                        "attribut" => "activites_resultats",
                        "type_champ" => "textarea",
                        "placeholder" => "Quelles seront les activités et les résultats du projet ? (Les activités peuvent inclure la conception, la construction, l'acquisition d'équipements et de services. Les extrants sont souvent des preuves physiques que les activités ont été réalisées)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les activités et les résultats prévus sont décrits clairement et se rapportent à l'objectif du projet"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les activités et les résultats sont décrits mais ne sont pas clairement liés à l'objectif du projet"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Les activités et les résultats ne sont pas décrits"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 11,
                        "label" => "Quels sont les résultats attendus du projet ?",
                        "info" => "Les résultats peuvent être décrits comme les éléments qui ont été améliorés suite à la mise en œuvre réussie du projet. Veuillez indiquer ce qui doit être amélioré, dans quelle mesure et à quel moment",
                        "attribut" => "resultats_attendus",
                        "type_champ" => "textarea",
                        "placeholder" => "Quels sont les résultats attendus du projet ? (Les résultats peuvent être décrits comme les éléments qui ont été améliorés suite à la mise en œuvre réussie du projet. Indiquer ce qui doit être amélioré, dans quelle mesure et à quel moment)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les résultats attendus sont clairement décrits et sont liés aux objectifs du projet."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les résultats attendus sont décrits mais ne contiennent pas suffisamment d'informations sur la quantité et le temps"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative sérieuse de répondre pleinement à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 12,
                        "label" => "Décrivez la méthodologie prévue pour réaliser le projet.",
                        "info" => "Décrivez les processus par lesquels les résultats seront obtenus. Commencez par une description de tous les travaux d'évaluation et de conception qui devront être réalisés et continuez jusqu'à la mise en service et l'opérationnalisation du projet",
                        "attribut" => "methodologie_prevue",
                        "type_champ" => "textarea",
                        "placeholder" => "Décrire la méthodologie prévue pour réaliser le projet. (Décrire les processus par lesquels les résultats seront obtenus. Commencer par une description de tous les travaux d'évaluation et de conception qui devront être réalisés et continuer jusqu'à la mise en service et l'opérationnalisation du projet)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "La méthodologie est clairement décrite et comprend tous les processus attendus pour un projet de ce type."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "La méthodologie est décrite mais il n'y a pas suffisamment d'informations ou les processus attendus manquent"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative sérieuse de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 13,
                        "label" => "Décrivez l'emplacement ou les emplacements géographiques inclus dans la portée du projet et tous les risques liés à l'emplacement.",
                        "info" => "Expliquez si le projet fait référence à un ou plusieurs emplacements et expliquez l'importance de cet ou ces emplacements par rapport aux vulnérabilités aux changements climatiques",
                        "attribut" => "emplacement_geographique",
                        "type_champ" => "textarea",
                        "placeholder" => "Décrire l'emplacement ou les emplacements géographiques inclus dans la portée du projet et tous les risques liés à l'emplacement. (Expliquer si le projet fait référence à un ou plusieurs emplacements et expliquer l'importance de cet ou ces emplacements par rapport aux vulnérabilités aux changements climatiques)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "L'emplacement exact du projet est clair"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "L'emplacement général du projet a été mentionné mais l'emplacement précis est insuffisant"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 14,
                        "label" => "Qui sont les bénéficiaires directs du projet et combien seront-ils ?",
                        "info" => "Les bénéficiaires directs sont les personnes dont la vie devrait être améliorée par le projet. Veuillez préciser quels groupes bénéficieront de ces mesures selon les classifications PEDS, et combien. Assurez-vous que les bénéficiaires correspondent aux objectifs énoncés au point 1.9. Expliquez combien d'emplois seront créés dans le secteur de la construction et plus tard dans le cadre des activités du projet. Combien de ces emplois peuvent être classés comme « emplois verts » ?",
                        "attribut" => "beneficiaires_directs",
                        "type_champ" => "textarea",
                        "placeholder" => "Qui sont les bénéficiaires directs du projet et combien seront-ils ? (Les bénéficiaires directs sont les personnes dont la vie devrait être améliorée par le projet. Préciser quels groupes bénéficieront selon les classifications PEDS, et combien. S'assurer que les bénéficiaires correspondent aux objectifs énoncés au point 1.9. Expliquer combien d'emplois seront créés dans le secteur de la construction et plus tard dans le cadre des activités du projet. Combien de ces emplois peuvent être classés comme \"emplois verts\" ?)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les types de bénéficiaires directs (y compris les emplois créés) sont répertoriés avec une approximation du nombre de personnes concernées."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Soit le type de bénéficiaires directs, soit les chiffres manquent."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Ni le type de bénéficiaires directs ni leur nombre ne sont inclus."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 15,
                        "label" => "Qui sont les bénéficiaires indirects et combien seront-ils ?",
                        "info" => "Semblable à la question précédente qui concerne les bénéficiaires directs ; répondez maintenant à cette question concernant les bénéficiaires indirects",
                        "attribut" => "beneficiaires_indirects",
                        "type_champ" => "textarea",
                        "placeholder" => "Qui sont les bénéficiaires indirects et combien seront-ils ? (Semblable à la question précédente qui concerne les bénéficiaires directs ; répondre maintenant à cette question concernant les bénéficiaires indirects)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les types de bénéficiaires indirects sont répertoriés avec une approximation du nombre concerné."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Soit le type de bénéficiaires indirects, soit les chiffres manquent."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Ni le type de bénéficiaires indirects ni leur nombre ne sont inclus."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 16,
                        "label" => "À quels impacts liés au climat peut-on s'attendre si le projet est mis en œuvre ?",
                        "info" => "Lorsque vous répondez à cette question, tenez compte non seulement des émissions de gaz à effet de serre (GES) du projet, mais aussi de sa construction. D'autres impacts peuvent concerner le choix spécifique de la technologie qui devrait être pris en compte dans le cadre de l'évaluation initiale des options. Pour les petits projets ne nécessitant pas de FS, indiquez si le projet est positif en carbone, neutre en carbone ou négatif en carbone et pourquoi. En cas de doute, demandez conseil à un professionnel et joignez-le à ce PCN",
                        "attribut" => "impacts_climat",
                        "type_champ" => "textarea",
                        "placeholder" => "À quels impacts liés au climat peut-on s'attendre si le projet est mis en œuvre ? (Tenir compte non seulement des émissions de gaz à effet de serre (GES) du projet, mais aussi de sa construction. D'autres impacts peuvent concerner le choix spécifique de la technologie qui devrait être pris en compte dans le cadre de l'évaluation initiale des options. Pour les petits projets ne nécessitant pas de FS, indiquer si le projet est positif en carbone, neutre en carbone ou négatif en carbone et pourquoi. En cas de doute, demander conseil à un professionnel et le joindre à ce PCN)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit : les problèmes ont été identifiés et les mesures d'atténuation possibles expliquées, nécessitant éventuellement des études supplémentaires, soit : le projet ne présente clairement que des impacts climatiques minimes"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Des problèmes ont été identifiés, mais aucune mesure d'atténuation n'a été expliquée ou aucune preuve n'a été fournie."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 17,
                        "label" => "Quelles vulnérabilités au changement climatique doivent être prises en compte lors de la conception du projet et comment seront-elles atténuées ?",
                        "info" => "Le changement climatique peut provoquer des phénomènes météorologiques extrêmes et d'autres conséquences telles que des risques accrus d'incendies, d'inondations et d'autres dégradations de l'environnement. Veuillez considérer comment tout événement extrême éventuel pourrait avoir un impact sur le projet et quelle résilience supplémentaire pourrait devoir être conçue et intégrée au projet afin de protéger le projet physiquement et d'éviter les interruptions de service. S'il n'y a que peu ou pas de vulnérabilités, vous devrez expliquer pourquoi.",
                        "attribut" => "vulnerabilites_climat",
                        "type_champ" => "textarea",
                        "placeholder" => "Quelles vulnérabilités au changement climatique doivent être prises en compte lors de la conception du projet et comment seront-elles atténuées ? (Le changement climatique peut provoquer des phénomènes météorologiques extrêmes et d'autres conséquences telles que des risques accrus d'incendies, d'inondations et d'autres dégradations de l'environnement. Considérer comment tout événement extrême éventuel pourrait avoir un impact sur le projet et quelle résilience supplémentaire pourrait devoir être conçue et intégrée au projet afin de protéger le projet physiquement et d'éviter les interruptions de service. S'il n'y a que peu ou pas de vulnérabilités, expliquer pourquoi)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit : les vulnérabilités ont été identifiées et des travaux supplémentaires prévus sur les mesures d'atténuation ont été expliqués, soit : le projet ne présente aucune vulnérabilité spécifique et des raisons crédibles ont été expliquées"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Des problèmes ont été identifiés, mais aucune mesure d'atténuation ni aucun plan d'enquête supplémentaire n'ont été expliqués."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 18,
                        "label" => "Quels autres impacts environnementaux ont été identifiés dans la proposition et quelles autres recherches sur les mesures d'atténuation sont prévues ?",
                        "info" => "La plupart des projets de construction causent des dommages environnementaux. Décrivez ces impacts. Décrivez également tout dommage environnemental qui pourrait résulter de l'exploitation à long terme du projet. Décrivez toutes les études ou travaux d'évaluation d'impact prévus visant à minimiser les dommages",
                        "attribut" => "impacts_environnementaux",
                        "type_champ" => "textarea",
                        "placeholder" => "Quels autres impacts environnementaux ont été identifiés dans la proposition et quelles autres recherches sur les mesures d'atténuation sont prévues ? (La plupart des projets de construction causent des dommages environnementaux. Décrire ces impacts. Décrire également tout dommage environnemental qui pourrait résulter de l'exploitation à long terme du projet. Décrire toutes les études ou travaux d'évaluation d'impact prévus visant à minimiser les dommages)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "rows" => 6,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit : Des problèmes environnementaux ont été identifiés et un plan d'action pour les étudier et les atténuer a été décrit ou : Le projet ne présente aucune préoccupation particulière concernant les dommages environnementaux."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Des problèmes ont été identifiés, mais aucune mesure d'atténuation ni aucun travail supplémentaire n'ont été décrits."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 3000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 19,
                        "label" => "Expliquez les problèmes de genre qui ont été identifiés dans la proposition et comment ils seront traités.",
                        "info" => "Considérez l'effet du projet proposé sur les questions de genre, expliquez comment elles ont été identifiées, qui a été consulté et quelles stratégies pourraient aider à les résoudre",
                        "attribut" => "problemes_genre",
                        "type_champ" => "textarea",
                        "placeholder" => "Expliquer les problèmes de genre qui ont été identifiés dans la proposition et comment ils seront traités. (Considérer l'effet du projet proposé sur les questions de genre, expliquer comment elles ont été identifiées, qui a été consulté et quelles stratégies pourraient aider à les résoudre)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit : les problématiques de genre ont été identifiées et des travaux supplémentaires sur les résolutions sont expliqués, soit : le projet ne présente aucune problématique de genre spécifique"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Des problèmes ont été identifiés, mais aucune stratégie de résolution ni aucun plan de travail supplémentaire n'ont été expliqués."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 20,
                        "label" => "Quelles autres questions d'égalité et d'équité sont soulevées par ce projet proposé et comment seront-elles traitées ?",
                        "info" => "Tenez compte de l'effet du projet proposé sur les questions de genre, sur les personnes à mobilité réduite et handicapées, ainsi que sur tous les autres groupes qui pourraient devoir être pris en compte pour un accès égal à l'installation ou aux services proposés. Réfléchissez à la manière dont ces besoins peuvent être pris en compte dans la planification et mise en œuvre du projet",
                        "attribut" => "questions_egalite_equite",
                        "type_champ" => "textarea",
                        "placeholder" => "Quelles autres questions d'égalité et d'équité sont soulevées par ce projet proposé et comment seront-elles traitées ? (Tenir compte de l'effet du projet proposé sur les questions de genre, sur les personnes à mobilité réduite et handicapées, ainsi que sur tous les autres groupes qui pourraient devoir être pris en compte pour un accès égal à l'installation ou aux services proposés. Réfléchir à la manière dont ces besoins peuvent être pris en compte dans la planification et mise en œuvre du projet)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit : Des problèmes d'égalité ont été identifiés et des travaux supplémentaires sur les résolutions sont expliqués, soit : le projet ne présente aucun problème d'égalité spécifique"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Des problèmes d'égalité ont été identifiés, mais aucune stratégie de résolution ni aucun plan de travail supplémentaire n'ont été expliqués."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true,
                                "max" => 2000
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ],

            // Section 2: Informations financières
            [
                "element_type" => "section",
                "ordre_affichage" => 2,
                "label" => "Informations financières",
                "attribut" => "section_informations_financieres",
                "key" => "section_informations_financieres",
                "description" => "Évaluation des aspects financiers du projet et de sa viabilité économique",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Coût total estimé en capital pour achever le projet",
                        "info" => "Inclure tous les éléments de coût nécessaires pour rendre le projet fonctionnel. Inclure toutes les études, travaux de conception, construction, terrain, équipement ou tout autre coût pour rendre le projet opérationnel. L'estimation complète du capital doit être incluse même lorsque le projet pourrait être financé par des ODA ou des PPP et doit inclure une estimation provisoire pour assurer la résilience climatique lorsque des vulnérabilités ont été identifiées",
                        "attribut" => "cout_total_capital",
                        "type_champ" => "number",
                        "placeholder" => "Montant en Francs CFA",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "min" => 0,
                                "step" => 1,
                                "currency" => "XAF",
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les données sont claires et les estimations fournies semblent réalistes pour un projet de ce type et de cette envergure."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "L'information peut être crédible mais nécessite des éclaircissements"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Les informations sont incomplètes ou ne sont pas réalistes/crédibles"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "numeric" => true,
                                "required" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Besoins en capital pour chaque année de mise en œuvre du projet",
                        "info" => "Si le projet peut être réalisé en un seul exercice financier, écrivez l'année avec le montant du capital étant le même que le nombre en 2.1. Si le projet s'étend sur plus d'un exercice financier, écrivez l'année et le montant demandé pour chaque année jusqu'à l'achèvement.",
                        "attribut" => "besoins_capital_annee",
                        "type_champ" => "textarea",
                        "placeholder" => "Année 1: [Montant], Année 2: [Montant], etc.",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les données sont claires et les estimations fournies semblent réalistes. Les estimations totales ne sont pas simplement divisées par le nombre d'années"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "L'information peut être crédible mais nécessite des éclaircissements"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Les informations sont incomplètes ou ne sont pas réalistes/crédibles"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Sources proposées de financement des capitaux",
                        "info" => "D'où viendra le capital du projet ? S'il doit provenir de plusieurs sources, énumérez-les toutes. Indiquez le montant monétaire réel et le pourcentage des coûts totaux de chaque source. Lorsque le financement est libellé en devise étrangère, indiquez clairement laquelle et quel montant. Si un partenaire de développement finance partiellement ou totalement le projet, vous devez présenter une lettre d'intention",
                        "attribut" => "sources_financement",
                        "type_champ" => "radio",
                        "placeholder" => "Les sources de financement sont-elles complètes et cohérentes ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les données sont complètes et claires. Les estimations totales s'élèvent au même chiffre dans la section 2.1. Lorsque le financement des partenaires de développement est réclamé, la lettre d'intention fait référence exactement au même projet"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les informations peuvent être complètes mais nécessitent des éclaircissements"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Les informations sont incomplètes ou ne sont pas réalistes/crédibles ou les totaux ne correspondent pas"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Ce projet nécessiterait-il des dépenses d'investissement sur d'autres projets afin d'être pleinement opérationnel et efficace ?",
                        "info" => "Répondez « oui » ou « non ». Certains projets dépendent d'autres projets connexes avant de pouvoir être pleinement efficaces. Si la réponse donnée est « oui », expliquez quel capital supplémentaire est requis et quand.",
                        "attribut" => "depenses_autres_projets",
                        "type_champ" => "radio",
                        "placeholder" => "La dépendance à d'autres projets est-elle bien expliquée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit une réponse <NON>, soit une réponse <OUI> entièrement expliquée et qui semble crédible"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les informations sont complètes mais nécessitent des éclaircissements."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Soit l'information n'est pas fournie en cas de réponse <OUI>, soit en cas de réponse <NON> la réponse n'apparaît pas crédible"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 5,
                        "label" => "Quels sont les coûts récurrents estimés lorsque le projet est opérationnel ?",
                        "info" => "Estimez les coûts annuels de fonctionnement du projet lorsqu'il sera opérationnel. Cela devrait inclure les salaires, les coûts des services publics, la maintenance et d'autres biens et services.",
                        "attribut" => "couts_recurrents_operation",
                        "type_champ" => "number",
                        "placeholder" => "Coût annuel en Francs CFA",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "min" => 0,
                                "step" => 1,
                                "currency" => "XAF",
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les données sont complètes, claires et les estimations fournies semblent réalistes."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les informations sont complètes mais nécessitent des éclaircissements."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Soit : L'information n'est pas complète, soit : elle est complète mais pas réaliste ou crédible"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 6,
                        "label" => "Le projet est-il déjà inclus dans le CDMT du MINECOFIN ?",
                        "info" => "Le MINECOFIN maintient un Cadre de dépenses à moyen terme (CDMT) pour aider à la planification budgétaire pour les années à venir. La question est donc de savoir si le projet proposé est déjà envisagé dans le CDMT actuel. Si vous ne le savez pas, demandez à un fonctionnaire compétent du MINECOFIN",
                        "attribut" => "inclusion_cdmt",
                        "type_champ" => "radio",
                        "placeholder" => "La réponse concernant l'inclusion dans le CDMT est-elle claire ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "La réponse est claire. Si la réponse est « oui », une référence ou une explication appropriée a été incluse."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Si la réponse est « oui », aucune référence ou explication n'a été incluse."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "La question n'a pas été répondue"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 7,
                        "label" => "Sources de coûts récurrents supplémentaires",
                        "info" => "La réponse la plus probable à cette question est soit le budget de l'État, soit le budget local - veuillez indiquer laquelle. Dans le cas d'une source de financement alternative, telle que « l'autofinancement », veuillez en indiquer la source. Si un financement non budgétaire est disponible, indiquez son montant et sa durée",
                        "attribut" => "sources_couts_recurrents",
                        "type_champ" => "radio",
                        "placeholder" => "Les sources de financement récurrent sont-elles réalistes ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les données sont complètes, claires et les sources semblent réalistes."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les informations sont complètes mais nécessitent des éclaircissements."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "L'information n'est pas complète ou elle est complète mais pas réaliste ou crédible"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 8,
                        "label" => "Une expropriation des terres et/ou une réinstallation avec indemnisation sont-elles nécessaires ?",
                        "info" => "Répondez « oui » ou « non ». Dans le cas de « oui », veuillez indiquer les coûts impliqués - qui auraient dû être inclus dans le point 2.1 - et le statut juridique de l'expropriation",
                        "attribut" => "expropriation_reinstallation",
                        "type_champ" => "radio",
                        "placeholder" => "Les aspects fonciers sont-ils bien traités ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les informations sont complètes, claires et semblent réalistes"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "L'information peut être fournie mais nécessite des éclaircissements"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "L'information n'a pas été fournie ou elle l'a été mais ne semble pas réaliste"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 9,
                        "label" => "Revenu annuel direct estimé (le cas échéant)",
                        "info" => "Si le projet génère des revenus grâce à ses activités, veuillez indiquer combien au cours de sa première année complète de fonctionnement et donner une explication ou une preuve pour le justifier. S'il n'y a pas de revenus, veuillez écrire « Aucun ».",
                        "attribut" => "revenu_annuel_direct",
                        "type_champ" => "textarea",
                        "placeholder" => "Montant estimé et justification, ou 'Aucun'",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Soit le projet ne génère pas de revenus, soit : s'il en génère, le chiffre indiqué est justifié par des preuves et est crédible."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Le projet génère des revenus mais le montant affiché n'est pas justifié"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Soit aucune tentative de réponse à la question, soit le projet génère des revenus mais la réponse donnée manque de crédibilité"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ],

            // Section 3: Planification de la mise en œuvre
            [
                "element_type" => "section",
                "ordre_affichage" => 3,
                "label" => "Planification de la mise en œuvre",
                "attribut" => "section_planification_mise_oeuvre",
                "key" => "section_planification_mise_oeuvre",
                "description" => "Évaluation de la planification et de la faisabilité de mise en œuvre du projet",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Calendrier prévu du projet",
                        "info" => "Donnez les dates estimées - mois/année - pour les étapes suivantes : Préparation (y compris l'achèvement de toutes les études) / Évaluation terminée ; Début du processus d'approvisionnement ; Attribution du contrat ; Début des travaux ; Finalisation des travaux ; Le projet devient opérationnel",
                        "attribut" => "calendrier_projet",
                        "type_champ" => "textarea",
                        "placeholder" => "Préparation: MM/AAAA, Approvisionnement: MM/AAAA, Attribution: MM/AAAA, etc.",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "rows" => 4,
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Le plan est clair et semble réaliste"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Le plan présenté paraît crédible mais nécessite des éclaircissements"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Les horaires ne sont pas crédibles ou aucune information n'a été fournie."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Examen précoce des options de mise en œuvre",
                        "info" => "Bien qu'une évaluation complète des options ne soit pas possible au stade de la présélection, il convient d'envisager dès le début d'exclure les options techniques qui peuvent représenter des « zones interdites » pour le gouvernement. Il peut s'agir d'options qui risqueraient de violer les protocoles et politiques environnementaux et de changement climatique. Par conséquent, décrivez tout travail qui a déjà été effectué sur l'évaluation précoce des options",
                        "attribut" => "options_mise_oeuvre",
                        "type_champ" => "radio",
                        "placeholder" => "L'évaluation des options techniques a-t-elle été effectuée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Des preuves du travail d'évaluation des options sont présentées et n'entraînent aucun conflit avec les politiques et protocoles gouvernementaux"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Une évaluation des options a été effectuée mais les résultats sont insuffisants"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative n'a été faite pour examiner les options de mise en œuvre."
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Énumérez les autres ministères du secteur, agences gouvernementales, autorités de district, services publics ou institutions de réglementation qui devront être directement impliqués dans la planification et la mise en œuvre du projet, y compris les problèmes juridiques qui devront être résolus.",
                        "info" => "Tous les projets dépendent de la coopération d'autres organismes. Sans cette coopération, les projets sont souvent retardés. Veuillez énumérer les organismes qui doivent être impliqués et dans quelle mesure ils ont déjà été consultés.",
                        "attribut" => "autres_institutions",
                        "type_champ" => "radio",
                        "placeholder" => "Les institutions partenaires nécessaires sont-elles identifiées ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Toutes les institutions susceptibles d'être concernées sont répertoriées avec des informations claires sur les questions juridiques/institutionnelles à traiter."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Institutions répertoriées mais présentant des lacunes évidentes"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative sérieuse de fournir les informations requises ou réponse non crédible"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Énumérez toutes les autres parties prenantes du projet et expliquez comment chacune d'entre elles sera consultée.",
                        "info" => "Les parties prenantes sont des individus ou des entités qui sont soit affectés par le projet, soit par des intérêts ou des connaissances spécifiques qui peuvent être utilisés dans la conception et la planification du projet",
                        "attribut" => "parties_prenantes",
                        "type_champ" => "radio",
                        "placeholder" => "Les parties prenantes et leur consultation sont-elles planifiées ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Toutes les personnes et entités susceptibles d'être impliquées sont répertoriées et des plans d'engagement sont fournis."
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Les parties prenantes sont répertoriées mais présentent des lacunes évidentes ou aucun plan réel d'engagement"
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative sérieuse de fournir les informations requises ou réponse non crédible"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 5,
                        "label" => "Quels sont les principaux risques pour mener à bien un projet et comment peuvent-ils être gérés ?",
                        "info" => "Un risque est un événement inattendu qui pourrait ralentir le projet et/ou entraîner des coûts supplémentaires. Veuillez énumérer les éléments qui pourraient mal se passer et qui auraient un impact négatif sur le projet. De plus, en vous appuyant sur les réponses de la question 1.17, concentrez-vous sur les vulnérabilités au changement climatique, expliquez l'ampleur et les impacts de la matérialisation de ces risques. Expliquez comment les risques peuvent être gérés",
                        "attribut" => "risques_gestion",
                        "type_champ" => "radio",
                        "placeholder" => "Les risques du projet et leur gestion sont-ils bien identifiés ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Passe",
                                        "value" => "passe",
                                        "description" => "Les risques les plus probables sont répertoriés avec des mesures de gestion crédibles décrites, qui peuvent inclure la commande d'études/enquêtes supplémentaires"
                                    ],
                                    [
                                        "label" => "Retour",
                                        "value" => "retour",
                                        "description" => "Soit : certains risques sont répertoriés mais d'autres risques probables ne le sont pas, soit : la voie à suivre est loin d'être claire."
                                    ],
                                    [
                                        "label" => "Non accepté",
                                        "value" => "non_accepte",
                                        "description" => "Aucune tentative de répondre à la question"
                                    ]
                                ]
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "string" => true,
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ]
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Récupérer ou créer la catégorie de document pour l'appréciation des TDRs
            $categorieDocument = CategorieDocument::where('slug', 'canevas-appreciation-tdrs-prefaisabilite')->first();

            if (!$categorieDocument) {
                $categorieDocument = CategorieDocument::create([
                    'slug' => "canevas-appreciation-tdrs-prefaisabilite",
                    'nom' => "Canevas d'appréciation d'appreciation d'un TDR de préfaisabilité",
                    "description" => "Canevas standardisé pour l'évaluation et l'appréciation des Termes de Référence de préfaisabilité",
                    "format" => "formulaire"
                ]);
            }

            // Extraire les données relationnelles avant création
            $formsData = $this->documentData['forms'] ?? [];

            // Nettoyer les données du document principal
            $documentData = collect($this->documentData)->except(['forms', 'champs', 'id'])->toArray();

            $documentData = array_merge($documentData, [
                "categorieId" => $categorieDocument->id
            ]);

            // Créer ou récupérer le document principal par nom
            $document = Document::updateOrCreate([
                'nom' => $documentData['nom']
            ], $documentData);

            // Traiter les sections et leurs éléments
            if (!empty($formsData)) {
                foreach ($formsData as $elementData) {
                    $this->createElementRecursive($elementData, $document, null);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Créer un élément (section ou champ) de manière récursive
     */
    private function createElementRecursive(array $elementData, $document, $parentSection = null): void
    {
        if ($elementData['element_type'] === 'section') {
            $this->createSection($elementData, $document, $parentSection);
        } elseif ($elementData['element_type'] === 'field') {
            $this->createChamp($elementData, $document, $parentSection);
        }
    }

    /**
     * Créer une section avec ses éléments enfants
     */
    private function createSection(array $sectionData, $document, $parentSection = null): void
    {
        $sectionAttributes = [
            'intitule' => $sectionData['label'],
            'slug' => $sectionData['attribut'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
        ];

        // Créer la section en utilisant intitule et documentId pour l'unicité
        $section = $document->sections()->updateOrCreate([
            'intitule' => $sectionData['label'],
            'documentId' => $document->id
        ], $sectionAttributes);

        // Traiter les éléments enfants de la section
        if (isset($sectionData['elements']) && !empty($sectionData['elements'])) {
            foreach ($sectionData['elements'] as $childElement) {
                $this->createElementRecursive($childElement, $document, $section);
            }
        }
    }

    /**
     * Créer un champ avec validation des données
     */
    private function createChamp(array $champData, $document, $parentSection = null): void
    {
        $champAttributes = [
            'label' => $champData['label'],
            'info' => $champData['info'] ?? null,
            'attribut' => $champData['attribut'] ?? null,
            'placeholder' => $champData['placeholder'] ?? null,
            'is_required' => $champData['is_required'] ?? false,
            'champ_standard' => $champData['champ_standard'] ?? false,
            'isEvaluated' => $champData['isEvaluated'] ?? false,
            'default_value' => $champData['default_value'] ?? null,
            'ordre_affichage' => $champData['ordre_affichage'],
            'type_champ' => $champData['type_champ'],
            'meta_options' => $champData['meta_options'] ?? [],
            'startWithNewLine' => $champData['startWithNewLine'] ?? false,
            'documentId' => $document->id,
            'sectionId' => $parentSection ? $parentSection->id : null
        ];

        // Créer le champ en utilisant la contrainte d'unicité complète
        \App\Models\Champ::updateOrCreate([
            'attribut' => $champData['attribut'],
            'sectionId' => $parentSection ? $parentSection->id : null,
            'documentId' => $document->id
        ], $champAttributes);
    }
}