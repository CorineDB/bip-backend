#!/bin/bash

# ============================================================================
# Script d'exportation des fiches de projet
# ============================================================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Fonction d'aide
show_help() {
    echo -e "${CYAN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║         SCRIPT D'EXPORTATION DES FICHES DE PROJET           ║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${GREEN}USAGE:${NC}"
    echo "  ./export-projects.sh [commande] [options]"
    echo ""
    echo -e "${GREEN}COMMANDES DISPONIBLES:${NC}"
    echo -e "  ${YELLOW}single${NC}      - Exporter un seul projet"
    echo -e "  ${YELLOW}batch${NC}       - Exporter plusieurs projets"
    echo -e "  ${YELLOW}all${NC}         - Exporter tous les projets"
    echo -e "  ${YELLOW}by-status${NC}   - Exporter par statut"
    echo -e "  ${YELLOW}by-date${NC}     - Exporter par plage de dates"
    echo -e "  ${YELLOW}today${NC}       - Exporter les projets d'aujourd'hui"
    echo -e "  ${YELLOW}week${NC}        - Exporter les projets de cette semaine"
    echo -e "  ${YELLOW}month${NC}       - Exporter les projets de ce mois"
    echo -e "  ${YELLOW}interactive${NC} - Mode interactif avec menu"
    echo ""
    echo -e "${GREEN}OPTIONS:${NC}"
    echo "  --format=[pdf|word|both]   Format d'export (défaut: pdf)"
    echo "  --zip                      Compresser en archive ZIP"
    echo "  --email=<email>            Envoyer par email"
    echo "  --queue                    Exécuter en arrière-plan"
    echo "  --output-dir=<path>        Répertoire de sortie personnalisé"
    echo ""
    echo -e "${GREEN}EXEMPLES:${NC}"
    echo "  ./export-projects.sh single --id=1 --format=pdf"
    echo "  ./export-projects.sh batch --ids=1,2,3 --format=both --zip"
    echo "  ./export-projects.sh all --format=pdf --queue"
    echo "  ./export-projects.sh by-status --status=approved --email=admin@example.com"
    echo "  ./export-projects.sh today --zip"
    echo "  ./export-projects.sh interactive"
    echo ""
}

# Fonction pour le mode interactif
interactive_mode() {
    clear
    echo -e "${CYAN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║         EXPORTATION INTERACTIVE DES FICHES DE PROJET        ║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${GREEN}Que souhaitez-vous faire ?${NC}"
    echo ""
    echo "1) Exporter un seul projet"
    echo "2) Exporter plusieurs projets (batch)"
    echo "3) Exporter tous les projets"
    echo "4) Exporter par statut"
    echo "5) Exporter par date"
    echo "6) Exporter les projets d'aujourd'hui"
    echo "7) Exporter les projets de cette semaine"
    echo "8) Exporter les projets de ce mois"
    echo "9) Quitter"
    echo ""
    
    read -p "Votre choix (1-9): " choice
    
    case $choice in
        1)
            export_single_interactive
            ;;
        2)
            export_batch_interactive
            ;;
        3)
            export_all_interactive
            ;;
        4)
            export_by_status_interactive
            ;;
        5)
            export_by_date_interactive
            ;;
        6)
            export_today
            ;;
        7)
            export_week
            ;;
        8)
            export_month
            ;;
        9)
            echo -e "${GREEN}Au revoir !${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Choix invalide${NC}"
            sleep 2
            interactive_mode
            ;;
    esac
}

# Fonction pour demander le format
ask_format() {
    echo ""
    echo -e "${YELLOW}Format d'export :${NC}"
    echo "1) PDF uniquement"
    echo "2) Word uniquement"
    echo "3) PDF et Word"
    read -p "Votre choix (1-3) [1]: " format_choice
    
    case $format_choice in
        1|"")
            FORMAT="pdf"
            ;;
        2)
            FORMAT="word"
            ;;
        3)
            FORMAT="both"
            ;;
        *)
            FORMAT="pdf"
            ;;
    esac
}

# Fonction pour demander les options supplémentaires
ask_additional_options() {
    echo ""
    read -p "Créer une archive ZIP ? (o/N): " zip_choice
    if [[ $zip_choice =~ ^[Oo]$ ]]; then
        OPTIONS="$OPTIONS --zip"
    fi
    
    echo ""
    read -p "Envoyer par email ? (o/N): " email_choice
    if [[ $email_choice =~ ^[Oo]$ ]]; then
        read -p "Adresse email: " email_address
        OPTIONS="$OPTIONS --email=$email_address"
    fi
    
    echo ""
    read -p "Exécuter en arrière-plan ? (o/N): " queue_choice
    if [[ $queue_choice =~ ^[Oo]$ ]]; then
        OPTIONS="$OPTIONS --queue"
    fi
}

# Export single interactif
export_single_interactive() {
    clear
    echo -e "${GREEN}EXPORT D'UN SEUL PROJET${NC}"
    echo ""
    
    read -p "ID du projet: " project_id
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export single --id=$project_id --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export batch interactif
export_batch_interactive() {
    clear
    echo -e "${GREEN}EXPORT DE PLUSIEURS PROJETS${NC}"
    echo ""
    
    read -p "IDs des projets (séparés par des virgules): " project_ids
    
    # Convertir les virgules en format attendu par la commande
    IFS=',' read -ra IDS <<< "$project_ids"
    ID_OPTIONS=""
    for id in "${IDS[@]}"; do
        ID_OPTIONS="$ID_OPTIONS --ids=$id"
    done
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export batch $ID_OPTIONS --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export all interactif
export_all_interactive() {
    clear
    echo -e "${YELLOW}⚠️  ATTENTION${NC}"
    echo "Vous êtes sur le point d'exporter TOUS les projets."
    echo ""
    
    read -p "Êtes-vous sûr ? (o/N): " confirm
    if [[ ! $confirm =~ ^[Oo]$ ]]; then
        interactive_mode
        return
    fi
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export all --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export par statut interactif
export_by_status_interactive() {
    clear
    echo -e "${GREEN}EXPORT PAR STATUT${NC}"
    echo ""
    
    # Récupérer les statuts disponibles
    echo "Statuts disponibles:"
    php artisan tinker --execute="App\Models\ProjectIdea::distinct()->pluck('status')->each(function(\$s) { echo \"- \$s\n\"; });"
    
    echo ""
    read -p "Statut à exporter: " status
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export by-status --status="$status" --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export par date interactif
export_by_date_interactive() {
    clear
    echo -e "${GREEN}EXPORT PAR PLAGE DE DATES${NC}"
    echo ""
    
    read -p "Date de début (YYYY-MM-DD): " date_from
    read -p "Date de fin (YYYY-MM-DD): " date_to
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export by-date --from="$date_from" --to="$date_to" --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export des projets d'aujourd'hui
export_today() {
    clear
    echo -e "${GREEN}EXPORT DES PROJETS D'AUJOURD'HUI${NC}"
    
    TODAY=$(date +%Y-%m-%d)
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export by-date --from="$TODAY" --to="$TODAY" --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export des projets de la semaine
export_week() {
    clear
    echo -e "${GREEN}EXPORT DES PROJETS DE CETTE SEMAINE${NC}"
    
    # Calculer le début et la fin de la semaine
    WEEK_START=$(date -d "monday this week" +%Y-%m-%d)
    WEEK_END=$(date -d "sunday this week" +%Y-%m-%d)
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export by-date --from="$WEEK_START" --to="$WEEK_END" --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Export des projets du mois
export_month() {
    clear
    echo -e "${GREEN}EXPORT DES PROJETS DE CE MOIS${NC}"
    
    # Calculer le début et la fin du mois
    MONTH_START=$(date -d "$(date +%Y-%m-01)" +%Y-%m-%d)
    MONTH_END=$(date -d "$(date -d "${MONTH_START} +1 month -1 day" +%Y-%m-%d)" +%Y-%m-%d)
    
    ask_format
    ask_additional_options
    
    echo ""
    echo -e "${CYAN}Exécution de la commande...${NC}"
    php artisan project:export by-date --from="$MONTH_START" --to="$MONTH_END" --format=$FORMAT $OPTIONS
    
    read -p "Appuyez sur Entrée pour continuer..."
    interactive_mode
}

# Script principal
main() {
    # Vérifier si on est dans le bon répertoire Laravel
    if [ ! -f "artisan" ]; then
        echo -e "${RED}Erreur: Ce script doit être exécuté depuis la racine de votre projet Laravel${NC}"
        exit 1
    fi
    
    # Si aucun argument, afficher l'aide
    if [ $# -eq 0 ]; then
        show_help
        exit 0
    fi
    
    # Parser la commande
    COMMAND=$1
    shift
    
    case $COMMAND in
        single|batch|all|by-status|by-date)
            php artisan project:export $COMMAND "$@"
            ;;
        today)
            TODAY=$(date +%Y-%m-%d)
            php artisan project:export by-date --from="$TODAY" --to="$TODAY" "$@"
            ;;
        week)
            WEEK_START=$(date -d "monday this week" +%Y-%m-%d)
            WEEK_END=$(date -d "sunday this week" +%Y-%m-%d)
            php artisan project:export by-date --from="$WEEK_START" --to="$WEEK_END" "$@"
            ;;
        month)
            MONTH_START=$(date -d "$(date +%Y-%m-01)" +%Y-%m-%d)
            MONTH_END=$(date -d "$(date -d "${MONTH_START} +1 month -1 day" +%Y-%m-%d)" +%Y-%m-%d)
            php artisan project:export by-date --from="$MONTH_START" --to="$MONTH_END" "$@"
            ;;
        interactive)
            interactive_mode
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            echo -e "${RED}Commande inconnue: $COMMAND${NC}"
            echo ""
            show_help
            exit 1
            ;;
    esac
}

# Exécuter le script principal
main "$@"
