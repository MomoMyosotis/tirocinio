<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_coripodatacollection
 * @category    string
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Coripo Data Collection';
$string['coripodatacollectionname'] = 'Coripo Data Collection Name';
$string['modulename'] = 'Coripo Data Collection';
$string['modulenameplural'] = 'Coripo Data Collection';
$string['coripodatacollectionsettings'] = 'Coripo data collection settings';
$string['coripodatacollectionfieldset'] = 'Coripo data collection fieldset';
$string['no$coripodatacollectioninstances'] = 'No Coripo data collection instances';
$string['pluginadministration'] = 'Database activity administration';
$string['projectadmin'] = 'Progetti';


// ------------------------------------------------- FORM NUOVO PROGETTO -------------------------------------------------------- //
$string['coripodatacollectionname_help'] = 'Nome per identificare l\'istanza di accesso al database per la raccolta dati';
$string['academicyear'] = 'Inserire l\'anno accademico per identificare a queli dati fare riferimento';
$string['academicyear_help'] = 'Inserire l\'anno accademico per identificare a queli dati fare riferimento';



// ----------------------------------------------------- [GENERALE] ------------------------------------------------------------- //
$string['cancel'] = 'Cancella';
$string['send'] = 'Invia';
$string['modify'] = 'Modifica';
$string['information'] = 'Informazioni';
$string['erogations'] = 'Erogazioni';
$string['course_name'] = 'Nome corso';
$string['accademic_year'] = 'Anno accademico';
$string['institutes'] = 'Istituti';
$string['class'] = 'Classe';
$string['classes'] = 'Classi';
$string['students'] = 'Alunni';
$string['phase'] = 'Fase';
$string['open'] = 'Aperto';
$string['home'] = 'Home';
$string['projects'] = 'Progetti';
$string['evaluators'] = 'Valutatori';
$string['username'] = 'Username';
$string['name'] = 'Nome';
$string['surname'] = 'Cognome';
$string['email'] = 'E-mail';
$string['telephone'] = 'Telefono';
$string['message'] = 'Messaggio';
$string['addnewuser'] = 'Censisci nuovo utente';
$string['add'] = 'Aggiungi';
$string['remove'] = 'Rimuovi';
$string['denomination'] = 'Denominazione';
$string['manager'] = 'Referente';
$string['first'] = 'Primo';
$string['second'] = 'Secondo';
$string['third'] = 'Terzo';
$string['fourth'] = 'Quarto';
$string['fifth'] = 'Quinto';
$string['yes'] = 'Sì';
$string['no'] = 'No';
$string['save'] = 'Salva';
$string['director'] = 'Direttore';
$string['teachers'] = 'Insegnanti';
$string['plexes'] = 'Plessi';
$string['address'] = 'Indirizzo';
$string['year'] = 'Anno';
$string['anyone'] = 'Nessuna';
$string['anyone_m'] = 'Nessuna';
$string['anagraphic'] = 'Anagrafica';
$string['select'] = 'Select';
$string['documents'] = 'Documenti';
$string['results'] = 'Risultati';
$string['info_alunno'] = 'Info Alunno';
$string['indexes'] = 'Indici';
$string['back'] = 'Indietro';
$string['north'] = 'Nord';
$string['south'] = 'Sud';
$string['east'] = 'Est';
$string['west'] = 'Ovest';


// ---------------------------------------------------- Nuovo utente ------------------------------------------------------------ //
$string['newuser_name'] = 'Nome';
$string['newuser_name_help'] = 'Nome del nuovo utente';
$string['newuser_surname'] = 'Cognome';
$string['newuser_surname_help'] = 'Cognome del nuovo utente';
$string['newuser_email'] = 'e-mail';
$string['newuser_email_help'] = 'E-mail del nuovo utente. Sono considerate valide solo le email che contengono il simbolo @, seguito 
da un dominio valido, cioè contenente un "." almeno.';
$string['newuser_password'] = 'Password';
$string['newuser_password_help'] = 'La password per essere valida deve contenere almeno 8 caratteri, almeno una lettera minuscola e 
una maiuscola, almeno un numero e deve contenere un carattere speciale come *,-,#. ';
$string['submit_newuser'] = 'Aggiungi utente';

// ----------------------------------------- [STRINGHE PAGINE ADMIN PROGGETTO] -------------------------------------------------- //

// ------------------------------------------------ Nuovo progetto form --------------------------------------------------------- //
$string['projectname'] = 'Denominazione progetto';
$string['projectname_error'] = 'Attenzione, nome progetto già in uso, digitare un nome differente';
$string['projectname_help'] = 'La denominazione del progetto, verrà visualizzata dagli altri utenti della piattaforma. ';
$string['creationdate'] = 'Data creazione';
$string['creationdate_help'] = 'Inserire la data di creazione del progetto';
$string['corporation'] = 'Ente erogatrice';
$string['corporation_help'] = 'Inserire la denominazione completa dell\'ente erogatrice del progetto';

// ------------------------------------------------- Panoramica progetti -------------------------------------------------------- //
$string['save_project'] = 'Salva progetto';
$string['addnewproject'] = 'Nuovo progetto';
$string['manageproject'] = 'I miei progetti';

// ------------------------------------------------- Project menu items --------------------------------------------------------- //
$string['projectmenu'] = 'Progetti';
$string['overviewmenu'] = 'Panoramica';
$string['evaluatorsmenu'] = 'Utenti valutatori';
$string['institutesmenu'] = 'Istituti Progetto';
$string['periodsmenu'] = 'Gestione periodi';
$string['groups'] = 'Gruppi';
$string['group_student'] = 'Studenti gruppo';
$string['add_student_part'] = 'Aggiungi studenti';

// ---------------------------------------------- Panoramica singolo progetto -------------------------------------------------- //
$string['go_to_course'] = 'Vai al corso';
$string['delete_course'] = 'Cancella corso';
$string['no_erogation'] = 'Nessuna erogazione';
$string['new_erogation'] = 'Nuova erogazione';

$string['allert_del_erogtion'] = 'Attenzione, cancellazione di un\'erogazione, proseguire?';

// --------------------------------------------------- Utenti valutatori -------------------------------------------------------- //
$string['remove_evaluator'] = 'Rimuovi valutatore';
$string['no_evaluators'] = 'Nessun valutatore censito';
$string['new_evaluator'] = 'Nuovo valutatore';
$string['search_evaluator'] = 'Cerca valuatatore già censito';

// --------------------------------------------------- Istituti progetto -------------------------------------------------------- //
$string['no_institutes'] = 'Nessun istituto';
$string['new_institute'] = 'Nuovo istituto';
$string['addnewinstitute'] = 'Censisci nuovo istituto';
$string['newinstitute_name'] = 'Denominazione istituto';
$string['single_school'] = 'Scuola singola, esente da classificazione a istituti';
$string['newinstitute_manager_name'] = 'Nome amministratore dell\'istituto';
$string['newinstitute_manager_surname'] = 'Cognome amministratore dell\'istituto';
$string['newinstitute_manager_email'] = 'Email amministratore dell\'istituto';
$string['newinstitute_manager_password'] = 'Password amministratore dell\'istituto';
$string['direcotor_form'] = 'Dati direttore dell\'istituto';
$string['director_name'] = 'Nome direttore dell\'istituto scolastico';
$string['director_surname'] = 'Cogome direttore dell\'istituto scolastico';
$string['director_email'] = 'Email direttore dell\'istituto scolastico';
$string['dsga_form'] = 'Dati DSGA dell\'istituto';
$string['dsga_name'] = 'Nome DSGA dell\'istituto scolastico';
$string['dsga_surname'] = 'Cogome DSGA dell\'istituto scolastico';
$string['dsga_email'] = 'Email DSGA dell\'istituto scolastico';
$string['admin_istuto'] = 'Referente di progetto dell\'istituto';
$string['view_istitute'] = 'Visualizza istituto';


$string['newinstitute_name_help'] = 'Nome del nuovo istituto che si vuole censire all\'interno della piattaforma';
$string['single_school_help'] = 'Selezionare questo campo se l\'istituto che si sta censendo è costituito da una sola scuola.';
$string['newinstitute_manager_name_help'] = 'Nome dell\'amministratore dell\'istituto, che avrà accesso alle pagine della 
piattaforma per la gestione di insegnati e classi dell\'istituto';
$string['newinstitute_manager_surname_help'] = 'Cognome dell\'amministratore dell\'istituto, che avrà accesso alle pagine della 
piattaforma per la gestione di insegnati e classi dell\'istituto';
$string['newinstitute_manager_email_help'] = 'Email dell\'amministratore dell\'istituto, che avrà accesso alle pagine della 
piattaforma per la gestione di insegnati e classi dell\'istituto. Sono considerate valide solo le email che contengono il simbolo @, 
seguito da un dominio valido, cioè contenente un "." almeno.';
$string['newinstitute_manager_password_help'] = 'La password per essere valida deve contenere almeno 8 caratteri, almeno una 
lettera minuscola e una maiuscola, almeno un numero e deve contenere un carattere speciale come *,-,#. ';
$string['director_name_help'] = 'Inserire il nome del direttore dell\'istituto. Il censimento del direttore dell\'istituto non 
produrrà la creazione di un utente nella piattaforma, ma è necessario per la memorizzazione dei dati e dell\'email, usati per 
eventuali notifiche.';
$string['director_surname_help'] = 'Inserire il cognome del direttore dell\'istituto. Il censimento del direttore dell\'istituto non 
produrrà la creazione di un utente nella piattaforma, ma è necessario per la memorizzazione dei dati e dell\'email, usati per 
eventuali notifiche.';
$string['director_email_help'] = 'Inserire l\'email del direttore dell\'istituto. Il censimento del direttore dell\'istituto non 
produrrà la creazione di un utente nella piattaforma, ma è necessario per la memorizzazione dei dati e dell\'email, usati per 
eventuali notifiche.';
$string['dsga_name_help'] = 'Inserire il nome del responsabile DSGA dell\'istituto. Il censimento del responsabile DSGA 
dell\'istituto non produrrà la creazione di un utente nella piattaforma, ma è necessario per la memorizzazione dei dati 
e dell\'email, usati per eventuali notifiche.';
$string['dsga_surname_help'] = 'Inserire il cognome del responsabile DSGA dell\'istituto. Il censimento del responsabile DSGA 
dell\'istituto non produrrà la creazione di un utente nella piattaforma, ma è necessario per la memorizzazione dei dati 
e dell\'email, usati per eventuali notifiche.';
$string['dsga_email_help'] = 'Inserire l\'email del responsabile DSGA dell\'istituto. Il censimento del responsabile DSGA 
dell\'istituto non produrrà la creazione di un utente nella piattaforma, ma è necessario per la memorizzazione dei dati e 
dell\'email, usati per eventuali notifiche.';

$string['submit_newinstitute'] = 'Aggiungi istituto';
$string['searchnewinstitute_name'] = 'Cerca istituto per nome';
$string['search_registered_institute'] = 'Cerca istituto già censito';
$string['save_institute'] = 'Salva istituto';



// ----------------------------------- [STRINGHE PAGINE ADMIN EROGAZIONE PROGGETTO] --------------------------------------------- //

// --------------------------------------------------- Classes page ------------------------------------------------------------- //
$string['census_period'] = 'Periodo censimento:';
$string['pre_reinforce_period'] = 'Periodo pre-potenziamento:';
$string['post_reinforce_period'] = 'Periodo post-potenziamento:';
$string['modify_periods'] = 'Modifica periodi';
$string['numberstudents_registered'] = 'Numero studenti censiti';
$string['numberstudents_identity'] = 'Numero studenti con carta d\'identita\'';
$string['numberstudents_consensus'] = 'Numero studenti con consenso';
$string['pre-reinforce_results'] = 'Risultati pre potenziamento inseriti';
$string['post-reinforce_results'] = 'Risultati post potenziamento inseriti';
$string['getcsvbutton'] = 'Scarica CSV info';
$string['select_istitute'] = 'Selezionare l\'istituto nel quale si vuole aggiungere la classe';
$string['select_istitute_import'] = 'Selezionare l\'istituto dal quale si vogliono importare le classi';
$string['select_istitute_reported'] = 'Selezionare l\'istituto dal quale si vogliono vedere gli studenti segnalati';


// --------------------------------------------------- Students page ------------------------------------------------------------ //
$string['waiting_teacher_confrim_title'] = 'Attesa registrazione insegnante';
$string['waiting_teacher_confrim_message'] = 'La classe a cui si sta cercando di accedere è una classe che ancora non è 
stata censita. Attendere che l\'insegnante completi la fase di registrazione per poter visualizzare gli alunni.';
$string['code'] = 'Codice';
$string['consensus'] = 'Carta d\'identità e consenso';
$string['born_in_italy'] = 'Nato in italia';
$string['language_difficulty'] = 'Difficoltà nel linguaggio';
$string['home_language'] = 'Lingua parlata a casa';
$string['nursery_school_freq'] = 'Frequenza scuola infanzia';
$string['nursery_school_difficulty'] = 'Difficoltà nella scuola d\'infanzia';
$string['difficulty_noted'] = 'Familiarità per disturbi nell\'apprendimento';
$string['centoquattro_law_table'] = 'Legge 104';
$string['centoquattro_problem_table'] = 'Problematica legge 104';
$string['consensus_given'] = 'Consenso dato';
$string['consensus_not_given'] = 'Consenso non dato';
$string['carta_identita_given'] = 'Carta d\'identità data';
$string['carta_identita_not_given'] = 'Carta d\'identità non data';

$string['reopen_census'] = 'Riapri censimento classe';
$string['close_census'] = 'Chiudi censimento classe';
// -------------------------------------------------- Valutation pages ---------------------------------------------------------- //
$string['missing_results_title'] = 'Risultato mancante: ';
$string['missing_results_message'] = 'Attenzione, uno o più risultati risultano non registrati. Il calcolo dei parametri 
statistici può essere eseguito ma questi non saranno valori completi.';
$string['missing_results_message_teacher'] = 'Attenzione, uno o più risultati risultano non registrati, completare correttamente 
l\'inserimento dei dati. I dati si considerano completi quando, per ogni alunno, una di queste condizioni si verifica:';
$string['complete_text_field'] = 'Attenzione, completare il campo!';
$string['missing_results_message_teacher_case_1'] = 'Le prove di lettura, scrittura e matematica sono state inserite totalmente';
$string['missing_results_message_teacher_case_2'] = 'Le prove accessorie sono state inserite totalmente';
$string['missing_results_message_teacher_case_3'] = 'Si dichiara esplicitamente l\'inserimento parziale dei risultati';
$string['NAI'] = 'NAI'; // n < 2

$string['new_results_title'] = 'Nuovi risultati inseriti: ';
$string['new_results_message'] = 'Attenzione, sono stati inseriti nuovi risultati, eseguire ricalcolo statistiche';
$string['evaluation_incomplete_title'] = 'Valutazioni non complete: ';
$string['evaluation_incomplete_message'] = 'Attenzione, una o più valutazioni non sono state inserite. Vedere i campi evidenziati in 
rosso nella tabella sottostante. Tutte le righe della colonna "Valutazione" devono essere completate e salvate, prima di chiudere
la valutazione';
$string['evaluation_complete_title'] = 'Valutazioni registrate: ';
$string['evaluation_complete_message'] = 'Le valutazioni sono state registrate e gli alunni che presentano una classificazione
gialla o rossa, verranno inseriti nella tabella delle valutazioni finali, non appena medie e deviazioni standard della popolazioni 
saranno disponibili';
$string['ending_evaluation_complete_message'] = 'Le valutazioni finali sono state confermate.';
$string['stats_calculated_title'] = 'Statistiche calcolate';
$string['stats_calculated_message_pre'] = 'la media e la deviazione standard di tutti gli alunni della popolazione sono stati 
calcolati per la fase precedente il potenziamento. Questi valori possono essere visti all\'interno della pagina delle valutazioni finali 
degli alunni che, classe per classe, sono stati fatti passare alla seconda fase della valutazione';
$string['stats_calculated_message_post'] = 'la media e la deviazione standard di tutti gli alunni della popolazione sono stati 
calcolati per la fase seguente il potenziamento. Questi valori possono essere visti all\'interno della pagina delle valutazioni finali 
degli alunni che, classe per classe, sono stati fatti passare alla seconda fase della valutazione';
$string['index_missing_title'] = 'Indici mancanti: ';
$string['index_missing_body'] = 'è possibile procedere al colcolo di media e deviazione standard globali, tuttavia non di tutte le 
classi sono stati calcolati gli indici. Procedere con il calcolo degli indici per tutte le classi che hanno segnalato il 
completamento, poi un bottone permetterà di eseguire i calcoli globali.';


$string['reopen_allert_title'] = 'Richiesta riapertura modifica risultati: ';
$string['reopen_allert_message_pre'] = 'L\'insegnante di questa classe richiede di poter modificare la tabella risultati 
pre-potenziamento';
$string['reopen_allert_message_post'] ='L\'insegnante di questa classe richiede di poter modificare la tabella risultati 
post-potenziamento';

$string['allow_modify_results'] = 'Consenti modifica valutazioni';
$string['block_modify_results'] = 'Blocca modifica valutazioni';
$string['calculate_stats'] = 'Calcola indici e semafori';
$string['calculate_stats_no_outlier'] = 'Calcola indici e semafori con outlier';
$string['save_evaluation'] = 'Salva valutazioni';
$string['close_evaluation'] = 'Chiudi valutazione';
$string['calculate_global_stats_pre'] = 'Calcola media e deviazione standard globali';
$string['calculate_global_stats_pre_no_outlier'] = 'Calcola media e deviazione standard globali con outlier';



// ----------------------------------------------- Evaluator users pages -------------------------------------------------------- //
$string['erogation_admin_page'] = 'Pagina amministrazione';


$string['erogation_no_institutes'] = 'Nessun istituto';
$string['erogation_add_institute'] = 'Aggiungi istituto';
$string['erogation_remove_institute_err'] = 'Attenzione, cancellazione di un\'istituto. Si tratta di un\'azione irreversibile 
che porta al cancellamento di tutti i dati riguardo classi, alunni e risultati. Proseguire?';

$string['erogation_remove_evaluator'] = 'Rimuovi valutatore';
$string['erogation_no_evaluators'] = 'Nessun valutatore inserito';
$string['erogation_add_evaluator'] = 'Aggiungi valutatore';

$string['group_code'] = 'Codice gruppo';
$string['sede'] = 'Sede';
$string['dettaglio_sede'] = 'Dettaglio sede';
$string['center_address'] = 'Indirizzo';
$string['center_zone'] = 'Zona';
$string['aula'] = 'Aula';
$string['day1'] = 'Giorno 1';
$string['day2'] = 'Giorno 2';
$string['orario1'] = 'Orario 1';
$string['orario2'] = 'Orario 2';
$string['student_1'] = 'Alunno 1';
$string['student_2'] = 'Alunno 2';
$string['student_3'] = 'Alunno 3';
$string['student_4'] = 'Alunno 4';
$string['student_5'] = 'Alunno 5';

$string['new_group'] = 'Nuovo gruppo';
$string['no_centers'] = 'Nessun gruppo censito';
$string['no_groups'] = 'Nessun gruppo';
$string['no_centers_students'] = 'Nessun studente inserito nel gruppo';
$string['center_address_form'] = 'Indirizzo del centro';
$string['center_zone_form'] = 'Zona del centro';
$string['center_times'] = 'Orari centro';
$string['no_center_time'] = 'Nessun orario registrato per il centro';
$string['start_time'] = 'Orario di inizio';
$string['end_time'] = 'Orario di fine';
$string['red_students'] = 'Bambini rossi';
$string['yellow_students'] = 'Bambini gialli';
$string['green_students'] = 'Bambini verde scuro';
$string['students_time_inserted'] = 'Bambini inseriti';
$string['add_time'] = 'Aggiungi orario';
$string['day'] = 'Giorno';
$string['color'] = 'Colore';
$string['colors_time'] = 'Colori alunni';
$string['name_surname_logopedista'] = 'Cognome e nome, rispettivamente, del logopoedista';
$string['monday'] = 'Lunedì';
$string['tuesday'] = 'Martedì';
$string['wednesday'] = 'Mercoledì';
$string['thursday'] = 'Giovedì';
$string['friday'] = 'Venerdì';
$string['saturday'] = 'Sabato';
$string['sunday'] = 'Domenica';
$string['delete_center_message'] = 'La cancellazione di una zona implica l\'eliminazione di ogni orario definito e conseguentemente
degli alunni ad esso assegnati, continuare?';
$string['delete_orario_message'] = 'La cancellazione di una orario implica l\'eliminazione degli alunni ad esso assegnati, 
continuare?';
$string['error_duplicate_inserted'] = 'Attenzione, lo stesso allunno non può essere inserito in due slot differenti';
$string['error_already_inserted'] = 'Attenzione, lo stesso allunno è registrato per un recupero i cui orari  di svoglimenti 
si sovrappongono con il corrente';
$string['search_by_code'] = 'Cerca alunno per codice';
$string['search_by_code_help'] = 'Per ripristinare i parametri di ricerca, premere cerca senza digitare nulla';

$string['search_by_code_color_zone'] = 'Cerca alunno per codice, colore o zona';
$string['search_by_code_color_zone_help'] = 'Per ripristinare i parametri di ricerca, premere cerca senza digitare nulla';
$string['search_color'] = 'Cerca per colore';
$string['search_zona'] = 'Cerca per zona';
$string['group_full'] = 'Il gruppo ha raggiunto la capienza massima di alunni';
$string['no_students'] = 'Nessuno studente trovato';
$string['new_groups'] = 'Nuovo gruppo';
$string['group'] = 'Gruppo';
$string['delete_group_student_message'] = 'Si sta cercando di rimuovere l\'alunno con codice %s, 
questa azione è irreversibile, continuare?';
$string['group_note'] = 'Nota del gruppo';
$string['save_note'] = 'Salva nota';
$string['saved_note'] = 'Nota salvata correttamente';
$string['upload_csv'] = 'Aggiorna informazioni con csv';
$string['csv_file'] = 'Carica file CSV';
$string['update_downloadreport'] = 'Aggiorna prescrizioni';
$string['get_csv_report'] = 'Scarica update ultimo aggiornamento';
$string['complete'] = 'Completato';
$string['complete_group'] = 'Completa gruppo';
$string['close_group'] = 'Chiudi gruppo';
$string['open_group'] = 'Riapri gruppo';
$string['definitive_group'] = 'Gruppo definitivo';
$string['download'] = 'Scarica';
$string['add_note'] = 'Aggiungi nota per gruppo: ';
$string['global_group_view'] = 'Suddivisione studenti per gruppi';
$string['completed_groups'] = 'Gruppi completati';
$string['closed_groups'] = 'Gruppi chiusi';
$string['definitive_groups'] = 'Gruppi definitivi';

// --------------------------------------------------- Periods pages ------------------------------------------------------------ //
$string['census_of_period'] = 'Periodo di censimento';
$string['start_census'] = 'Inzio censimento';
$string['end_census'] = 'Fine censimento';
$string['prereinforce_period'] = 'Periodo registrazione valutazioni pre-potenziamento';
$string['start_reg_val_pre'] = 'Inizio registrazione valutazioni';
$string['end_reg_val_pre'] = 'Fine registrazione valutazioni';
$string['postreinforce_period'] = 'Periodo registrazione valutazioni post-potenziamento';
$string['start_reg_val_post'] = 'Inizio registrazione valutazioni';
$string['end_reg_val_post'] = 'Fine registrazione valutazioni';

$string['start_census_help'] = 'Indicare la data di apertura della fase di censimento';
$string['end_census_help'] = 'Indicare la data di chiusura della fase di censimento. Attenzione: questa data non deve essere postera
rispetto alla data di inzio della fase di registrazioine delle valutazioni pre-potenziamento.';
$string['start_reg_val_pre_help'] = 'Indicare la data di apertura della fase di registrazione dei risultati pre-potenziamento. 
Attenzione: questa data non deve essere precedente alla data di fine della fase di censimento';
$string['end_reg_val_pre_help'] = 'Indicare la data di chiusura della fase di registrazione dei risultati pre-potenziamento.
 Attenzione: questa data non deve essere postera rispetto alla data di inzio della fase di registrazioine delle valutazioni post-potenziamento.';
$string['start_reg_val_post_help'] = 'Indicare la data di apertura della fase di registrazione dei risultati post-potenziamento. 
Attenzione: questa data non deve essere precedente alla data di fine della fase di registrazione dei risultati pre-potenziamento';
$string['end_reg_val_post_help'] = 'Indicare la data di chiusura della fase di registrazione dei risultati post-potenziamento';



// ------------------------------------------------- [RESULTS TABLES] ---------------------------------------------------------- //
$string['accessories_tests'] = 'Prove accessorie';
$string['reading'] = 'Lettura';
$string['writing'] = 'Scrittura';
$string['math'] = 'Matematica';
$string['export_table'] = 'Esporta tabella';

$string['test_administration'] = 'Necessità somministrazione prove accessorie (protocollo completo non applicabile)';
$string['syllables_fusion'] = 'Fusione sillabica';
$string['cv_syllables_analyses'] = 'Analisi sillabe CV';
$string['cvc_syllables_analyses'] = 'Analisi sillabe CVC';
$string['syllables_segmentation'] = 'Segmentazione sillabe';
$string['consonant_groups_segmentation'] = 'Segmentazione gruppi consonantici';

$string['phonemes'] = 'Fonemi';
$string['syllables_cv'] = 'Sillabe CV';
$string['syllables_cvc'] = 'Sillabe CVC';
$string['nonwords'] = 'Non parole';
$string['consonant_cluster_words'] = 'Parole gruppo consonanti';
$string['flat_words'] = 'Parole piane';
$string['orthographic_cluster_words'] = 'Parole gruppo ortografico';
$string['reading_numbers'] = 'Lettura numeri';
$string['forward_enumeration'] = 'Enumerazione avanti';
$string['backward_enumeration'] = 'Enumerazione indietro';
$string['quantity_recognition'] = 'Riconoscimento quantità';
$string['additions'] = 'Addizioni';
$string['subtractions'] = 'Sottrazioni';
$string['comparison'] = 'Confronto';
$string['insert_symbol'] = 'Inserisci il simbolo';
$string['ascending_order'] = 'Ordinamento crescente';
$string['descending_order'] = 'Ordinamento decrescente';
$string['numerical_dictation'] = 'Dettato numerico';
$string['number_trasformation'] = 'Trasforma in cifre';

$string['measure'] = 'Misura';
$string['avg_res'] = 'Medie risultati';
$string['stddev_res'] = 'Deviazioni standard risultati';
$string['avg_values'] = 'Medie indici';
$string['stddev_values'] = 'Deviazioni standard indici';
$string['includes'] = 'Includi nel calcolo';
$string['evaluation'] = 'Valutazione';
$string['preliminar_eval'] = 'Valutazione preliminare';
$string['gravity_profile'] = 'Profilo di gravità';
$string['student_report'] = 'Segnalazione alunno';
$string['reported'] = 'Segnalato';
$string['reported_students'] = 'Studenti segnalati';
$string['total_passed'] = 'Totale alunni passati alla valutazione finale: ';
$string['total_reported'] = 'Totale alunni segnalati: ';
$string['not_reported'] = 'Non segnalato';
$string['specialistic_note'] = 'Nota specialistica';
$string['reading_correctness'] = 'Correttezza lettura';
$string['reading_speed'] = 'Rapidità lettura';
$string['reading_sublex'] = 'Lettura sublessicale';
$string['reading_lex'] = 'Lettura lessicale';
$string['reading_mean'] = 'Lettura media';
$string['writing_correctness'] = 'Correttezza scrittura';
$string['math_reading_correctness'] = 'Matematica transcodifica';
$string['math_enumeration_correctness'] = 'Matematica enumerazione';
$string['math_decoding_correctness'] = 'Matematica ordinamento';
$string['math_calculus_correctness'] = 'Matematica calcolo';

$string['student'] = 'Alunno';
$string['precedent_observation_difficulties'] = 'Nella precedente valutazione il bambino aveva mostrato delle difficoltà in alcune aree?';
$string['precedent_observation_difficulties_v2'] = 'Difficoltà nella precedente osservazione';
$string['partial_result'] = 'Inserimento parziale risultati';
$string['didatic_method'] = 'Metodo didattico';
$string['didatic_method_popup_desc'] = 'Inserire il metodo didattico usato per la classe. Attenzione, nel caso il metodo usato non sia presente tra i metodi elencati, selezionare "Altro" e indicare il metodo usato.
Sarà possibile modificare il metodo inserito tramite apposito pulsante dalla pagina precedente.';
$string['other_didatic_method'] = 'Specificare altro metodo';
$string['phonosyllabic'] = 'Fonosillabico';
$string['sillabic'] = 'Sillabico';
$string['global'] = 'Globale';
$string['siglo'] = 'SIGLO';
$string['other'] = 'Altro';

$string['reading_modality'] = 'Modalità prova lettura';
$string['errors'] = 'Errori';
$string['time'] = 'Tempo [secondi]';

$string['lowercase_writing'] = 'Stampatello minuscolo';
$string['uppercase_writing'] = 'Stampato maiuscolo';
$string['notify_results'] = 'Notifica completamento risultati';

$string['green'] = 'Verde';
$string['light_green'] = 'Verde chiaro';
$string['dark_green'] = 'Verde scuro';
$string['yellow'] = 'Giallo';
$string['red'] = 'Rosso';
$string['show_row_results'] = 'Mostra tabella risultati';
$string['show_stats_table'] = 'Mostra tabella valutazioni primarie';
$string['show_stats_table_global'] = 'Mostra tabella valutazioni finali';

$string['index_calculus'] = 'Calcolo indici';
$string['primary_eval'] = 'Valutazione primaria';
$string['final_eval'] = 'Valutazione definitiva';
$string['eval_ended'] = 'Valutazioni concluse';

// ----------------------------------------- [STRINGHE PAGINE ADMIN ISTITUTO] --------------------------------------------------- //

// --------------------------------------------------- Overview page ------------------------------------------------------------ //
$string['institute_infos'] = 'Informazioni istituto';
$string['plexes_number'] = 'Numero di plessi';
$string['modifyinstitute'] = 'Modifica';
$string['addplexe'] = 'Aggiungi plesso';
$string['istitute_already_present_error'] = 'Istituto già presente';
$string['new_plex'] = 'Nuovo plesso';
$string['modify_institute_info'] = 'Modifica informazioni istituto';

$string['denominazioneplesso'] = 'Denominazione plesso';
$string['denominazioneplesso_help'] = 'Denominazione completa del plesso che si vuole censire';
$string['denominazioneplesso_error'] = 'Un altro plesso con questo nome è stato censito nell\'istituto';
$string['indirizzoplesso'] = 'Indirizzo plesso';
$string['indirizzoplesso_help'] = 'Indirizzo completo del plesso, composto da via, città e paese';
$string['submit_newplesso'] = 'Aggiungi plesso';
$string['delete_plex'] = 'Cancella plessp';
$string['no_plexes'] = 'Nessun plesso';

// --------------------------------------------------- Teachers page ------------------------------------------------------------ //
$string['search_registered_teachers'] = 'Cerca insegnanti già censiti';
$string['no_teachers'] = 'Nessun insegnante';
$string['no_registered_teacher'] = 'Nessun insegnante censito';
$string['addnewteacher'] = 'Censisci nuovo insegnante';
$string['delete_teacher'] = 'Cancella insegnante';
$string['new_teacher'] = 'Nuovo insegnante';

// ----------------------------------------------- Institute admins page -------------------------------------------------------- //
$string['institute_admins'] = 'Amministratori istituto';
$string['remove_admin'] = 'Rimuovi admin';
$string['no_admins'] = 'Nessun amministratore';
$string['new_admin'] = 'Nuovo amministratore';
$string['search_registered_users'] = 'Cerca tra gli utenti già censiti';
$string['remove_dsga'] = 'Rimuovi DSGA';
$string['no_dsga'] = 'Nessun DSGA censito';
$string['new_dsga'] = 'Nuovo DSGA';

// ----------------------------------------------- Erogation class page -------------------------------------------------------- //
$string['classdenomination'] = 'Denominazione classe';
$string['classdenomination_help'] = 'Inserire la denominazione della classe, è consiglibile per motivi di leggibilità nella 
piattaforma, di non includere in quest\'area l\'anno della classe.';
$string['class_year'] = 'Anno di progressione';
$string['class_year_help'] = 'Inserire l\'anno di progressione della classe nel ciclo scolastico';
$string['searchinsegnante_name'] = 'Insegnante';
$string['searchinsegnante_name_help'] = 'Inserire l\'insegnate che si occuperà di censire alunni e risultati per la classe all\'
interno della piattaforma di raccolta dati.' ;
$string['searchplesso_name'] = 'Plesso';
$string['searchplesso_name_help'] = 'Indicare il plesso in cui è presente la classe che si sta censendo.';
$string['numberstudents'] = 'Numero studenti della classe';
$string['numberstudents_help'] = 'Indicare il numero totale di studenti presenti nella classe.';
$string['submit_newclass'] = 'Nuova classe';
$string['classalreadyexist'] = 'Classe già censita in questo plesso';
$string['modclass'] = 'Modifica classe';
$string['delclass'] = 'Cancella classe';
$string['confirmdeleteclass'] = 'Sicuro di voler cancellare questa classe?';
$string['pluri_class'] = 'Pluriclasse';
$string['pluri_class_help'] = 'Cliccando su pluriclasse, si indica che si sta censendo una classe all\'interno della quale vi sono 
alunni che presentano anno di progressione nel ciclo scolastico differente gli uni dagli altri. Selezionando questa opzione, in
automatico la selezione dell\'anno della classe viene disabilitato e non salvato.';

$string['first_1'] = '1-Primo';
$string['second_2'] = '2-Secondo';
$string['third_3'] = '3-Terzo';
$string['fourth_4'] = '4-Quarto';
$string['fifth_5'] = '5-Quinto';

$string['import_selected_classes'] = 'Importa classi selezionate';
$string['no_registered_plex'] = 'Nessun plesso censito';
$string['year_after_import'] = 'Anno dopo importanzione';
$string['refering_teacher'] = 'Insegnante di riferimento';
$string['class_union_with'] = 'Unione con classe';

$string['import_class_title'] = 'Importazione classe da erogazione a.a.';
$string['registered_classes'] = 'Classi censite';
$string['registered_classes_teaching'] = 'Classi di istituti senza privilegi di referente di progetto';

$string['error_missingplessotitle'] = 'Nessun plesso inserito per l\'istituto ';
$string['error_missingplessotext'] = 'Per poter procedere su questa pagina accedere all\'area amministrativa dell\'istituto 
                                    e inserire almeno una plesso';
$string['error_missingteachertitle'] = 'Nessun insengante censitoper l\'istituto ';
$string['error_missingteachertext'] = 'Per poter procedere su questa pagina accedere all\'area amministrativa dell\'istituto
                                    e censire almeno un insegnante';
$string['error_buttontoinstituteadmin'] = 'Amministrazione istituto';

$string['no_class_for_plex'] = 'Nessuna classe censita per il plesso';
$string['total_students'] = 'Studenti totali';
$string['partecipant_students'] = 'Studenti partecipanti';
$string['registered_results_pre'] = 'Risultati inseriti pre-potenziamento';
$string['registered_results_post'] = 'Risultati inseriti post-potenziamento';
$string['status'] = 'Status';
$string['view_class'] = 'Vedi classe';
$string['missing_students'] = 'Studenti mancanti';
$string['missing_result'] = 'Risultati mancanti';

$string['insertion_completed'] = 'Inserimento completato';
$string['insertion_results'] = 'Inserimento risultati';
$string['census'] = 'Censimento';

$string['import_classes'] = 'Importa classi da erogazione precedente';

// ----------------------------------------------- Erogation students page -------------------------------------------------------- //
$string['wait_teacher_confirm_title'] = 'Attesa registrazione insegnante';
$string['wait_teacher_confirm_message'] = 'La classe a cui si sta cercando di accedere è una classe che ancora non è stata censita. 
                Attendere che l\'insegnante completi la fase di registrazione per poter visualizzare gli alunni.';

$string['view_consensus_document'] = 'Vedi documento per consenso';



// ----------------------------------------- [STRINGHE PAGINE UTENTE VALUTATORE] --------------------------------------------------- //

// ----------------------------------------------- Erogation students page -------------------------------------------------------- //
$string['new_res_inserted_title'] = 'Nuovi risultati inseriti: ';
$string['new_res_inserted_message'] = 'attenzione, sono stati inseriti nuovi risultati, eseguire ricalcolo statistiche';
$string['evaluators_calculus'] = 'Calcola medie e deviazioni standard';
$string['avg_std_class'] = 'Media e deviazione standard della classe';
$string['avg_std_global'] = 'Media e deviazione standard globali';
$string['deviation_students'] = 'Deviazioni standard di ogni alunno';
$string['dev'] = 'Dev';
$string['gravity'] = 'Gravità';
$string['normal'] = 'Normale';
$string['weak'] = 'Fragile';
$string['bad'] = 'Grave';
$string['really_bad'] = 'Molto grave';
$string['save_gravity'] = 'Salva gravità';
$string['modify_gravity'] = 'Modifica classificazione gravità';


// ------------------------------------------- [STRINGHE PAGINE INSEGNANTE] ----------------------------------------------------- //
$string['students_delete_allert'] = 'Attenzione, eliminazione alunno, continuare?';
$string['confirm_students_number'] = 'Conferma che gli alunni della classe sono in tutto ';
$string['confirm_change_number_title'] = 'Attesa cambio numero studenti';
$string['confirm_change_number_message'] = 'Il numero di studenti è stato da voi segnalato errato, l\'amministratore dell\'istituto 
è stato avvisato e provvederà a correggere l\'errore. La pagina sarà visibile quando il numero di studenti verrà ripristinato';
$string['wrong_email_title'] = 'Email errata: ';
$string['wrong_email_message'] = 'attenzione, una o più email inserite non sono valide. Una email per considerarsi valida deve
contenere il simbolo @ seguita da un dominio valido, quindi che presenta almeno un simbolo ".". ';
$string['freq_year'] = 'Anno Frequentazione';
$string['view_file'] = 'Visualizza file';
$string['no_registered_students'] = 'Nessun studente censito';
$string['send_notification_title'] = 'Notifica completamento inviata: ';
$string['confirm_allert'] = 'Attenzione! Con la seguente azione si dichiara il completamento dei risultati e ulteriori modifiche non
saranno più apportabili se non previo contatto con l\'amministratore del progetto, possibile entro e non oltre la fine del periodo
di inserimento dati e prima che le logopediste procedano alla valutazione della classe. Continuare?';
$string['send_notification_pre_message'] = 'Il completamento dell\'inserimento dei risultati della fase pre-rinforozo è stato notificato.
Attenzione: qualsiasi modifica alla tabella ritirerà la notifica di completamento';
$string['send_notification_post_message'] = 'Il completamento dell\'inserimento dei risultati della fase pre-rinforozo è stato notificato.
Attenzione: qualsiasi modifica alla tabella ritirerà la notifica di completamento';
$string['send_reopen_title'] = 'Notifica riapertura modifica inviata: ';
$string['send_reopen_pre_message'] = 'è stata inviata una notifica a tutti gli amministratori del progetto riguardo la sua richiesta 
di modificare la tabella risultati pre-potenziamento.';
$string['get_participation_certificate'] = 'Scarica certificato di partecipazione';

//--------------------------------------------------- New student form -----------------------------------------------------------//
$string['update_infos'] = 'Aggiorna informazioni';
$string['save_student'] = 'Salva alunno';
$string['new_student_email'] = 'E-mail';
$string['new_student_surname'] = 'Cognome';
$string['new_student_name'] = 'Nome';
$string['register_number'] = 'Numero a registro';
$string['no_info'] = 'NoInfo';
$string['no_info_extended'] = 'Nessuna informazione a disposizione';
$string['difficulty_present'] = 'Presenta ad oggi difficoltà di linguaggio ( linguaggio poco comprensibile, 
                   errori nella produzione dei suoni, difficoltà nella comprensione di consegne)';
$string['select_languages'] = 'Selezionare lingua';

$string['italiano'] = 'Italiano';
$string['inglese'] = 'Inglese';
$string['francese'] = 'Francese';
$string['spagnolo'] = 'Spagnolo';
$string['portoghese'] = 'Portoghese';
$string['tedesco'] = 'Tedesco';
$string['polacco'] = 'Polacco';
$string['albanese'] = 'Albanese';
$string['rumeno'] = 'Rumeno';
$string['ucraino'] = 'Ucraino';
$string['russo'] = 'Russo';

$string['swahili'] = 'Swahili';
$string['huasa'] = 'Hausa';
$string['igbo'] = 'Igbo';
$string['yoruba'] = 'Yoruba';
$string['berbero'] = 'Berbero';
$string['oromo'] = 'Oromo';
$string['amarico'] = 'Amarico';

$string['arabo'] = 'Arabo';
$string['srilankese'] = 'Srilankese';
$string['hindi'] = 'Hindi';
$string['punjabi'] = 'Punjabi';
$string['urdu'] = 'Urdu (Pakistan)';
$string['mandarino'] = 'Cinese (mandarino)';
$string['cantonese'] = 'Cinese (Cantonese)';

$string['african_language'] = 'Lingua africana (non nota nello specifico o non in elenco)';
$string['asian_language'] = 'Lingua asiatica (non nota nello specifico o non in elenco)';
$string['indoeurpean_language'] = 'Lingua indo-europea (non nota nello specifico o non in elenco)';
$string['notinlist_language'] = 'Lingua non nota nello specifico o non in elenco';

$string['noinfo'] = 'Nessuna informazione disponibile';

$string['nursery_school_freq_insert'] = 'Ha frequentato la scuola dell\'infanzia.';
$string['nursery_difficulty_noted'] = 'Le insegnanti della scuola dell\'infanzia hanno segnalato difficoltà?';
$string['difficulty_noted_insert'] = 'È nota o è stata riferita familiarità per difficoltà di apprendimento?';
$string['centoquattro_law'] = 'L\'alunno  è titolare dei benefici previsti dalla legge 104/1992';
$string['centoquattro_problem'] = 'Indicare la problematica che porta l\'alunno ad essere titolare 
dei benefici previsiti dalla legge 104/1992';
$string['consensus_partecipation'] = 'Consenso partecipazione';
$string['confirm_button_consensus'] = 'Dichiaro che lo studente ha consegnato il consenso firmato alla partecipazione al progetto
 e di esserne in possesso';
$string['confirm_button_consensus_help'] = 'Selezionare "sì", se si è in possesso del documento ed è stato dato il consenso, altrimenti
selezionare "no", anche nel caso in cui il documento è stato consegnato ma non risulta valido o nega la partecipazione al progetto';
$string['file_consensus'] = 'Scansione consenso firmato';
$string['carta_identita'] = 'Dichiaro che lo studente ha consegnato una fotocopia della carta d\'identità e di esserne in possesso';
$string['consensus_code'] = 'Codice consenso';
$string['consensus_code_help'] = 'Il codice consenso da ricopiare sul consenso firmato consegnato dallo studente, utile per
eventuali tracciamenti';

$string['number_register_err'] = 'Numero registro non valido';
$string['file_consensus_err'] = 'Necessario inserimento di un file per il consenso';
$string['new_student_err'] = 'Alunno già presente nella classe';
$string['register_number_err_2'] = 'Il numero registro è già stato assegnato ad un altro alunno';

$string['new_student_email_help'] = 'Email di riferimento dell\'alunno, di un gentiore o di chi ne fa le veci, utilizzata per 
contattare quest\'ultimo nel caso di eventuali comunicazione. Sono considerate valide solo le email che contengono il simbolo @, 
seguito da un dominio valido, cioè contenente un "." almeno.';
$string['new_student_surname_help'] = 'Cognome dell\'alunno che si vuole censire';
$string['new_student_name_help'] = 'Nome dell\'alunno che si vuole censire';
$string['register_number_help'] = 'Inserire il numero registro corrispondente all\'elenco in vostro possesso. Gli studenti verranno 
ordinati nella pagina principale sulla base di questo numero.';
$string['annofrequentazione'] = 'Anno Frequentazione';
$string['annofrequentazione_help'] = 'Inserire l\'anno progressivo dello studente nel suo percorso scolastico';
$string['born_in_italy_help'] = 'Indicare "Sì" se lo studente è nato in italia, "No" altrimenti. Se non sono disponibili 
informazioni a riguardo, non lasciare il campo non compilato, ma selezionare "Nessuna informazione disponibile".';
$string['difficulty_present_help'] = 'Indicare "Sì" se lo studente presenta difficoltà nel linguaggio, "No" altrimenti. Se non sono 
disponibili informazioni a riguardo, non lasciare il campo non compilato, ma selezionare "Nessuna informazione disponibile".';
$string['home_language_help'] = 'Indicare la lingua che lo studente che si sta censendo è usuale parlare a casa. Se non sono 
disponibili informazioni a riguardo, non lasciare il campo non compilato, ma selezionare "Nessuna informazione disponibile".';
$string['nursery_school_freq_insert_help'] = 'Indicare "Sì" se lo studente ha frequentato la scuola dell\'infanzia, "No" altrimenti.
 Se non sono disponibili informazioni a riguardo, non lasciare il campo non compilato, ma selezionare "Nessuna informazione 
 disponibile".';
$string['nursery_difficulty_noted_help'] = 'Indicare "Sì" se lo studente ha mostrato difficoltà durante la frequentazione della 
scuola dell\'infanzia, "No" altrimenti. Se non sono disponibili informazioni a riguardo, non lasciare il campo non compilato, 
ma selezionare "Nessuna informazione disponibile". Selezionare questa opzione anche quando lo studente risulta non aver frequentato
la scuola dell\'infanzia.';
$string['difficulty_noted_insert_help'] = 'Indicare "Sì" se lo studente presenta delle difficoltà, "No" altrimenti.
 Se non sono disponibili informazioni a riguardo, non lasciare il campo non compilato, ma selezionare 
 "Nessuna informazione disponibile".';
$string['centoquattro_law_help'] = 'Indicare se l\'alunno usurfruisce o meno delle aggevolazione concesse dalla legge 104 del 1992. 
Se sì, indicare il tipo di problematica del bambino nel riquadro che apparirà di seguito.';
$string['carta_identita_help'] = 'Inserire un singolo file .pdf contenente la scansione della carta d\'identità dello studente che
si sta censendo';



// ----------------------------------------------- INSTANCE MENU ELEMENTS ------------------------------------------------------- //
$string['classi'] = 'Classi';
$string['addnewclass'] = 'Nuova classe';

// ----------------------------------------------------- CLASS PAGE ------------------------------------------------------------- //
$string['alunni'] = 'Alunni della classe';
$string['primevalutazioni'] = 'Valutazioni pre-potenziamento';
$string['ultimevalutazioni'] = 'Valutazioni post-potenziamento';
$string['addnewalunno'] = 'Aggiungi alunno';
$string['pdf_alunni'] = 'Scarica tabella alunni';
$string['director_notification'] = 'Notifica dirigenti scolastici';
$string['pdf_reported'] = 'Scarica PDF';
$string['pdf_risultati'] = 'Scarica tabella risultati';
$string['result_reopen'] = 'Richiedi modifica risultati';

$string['fileconsenso'] = 'Il documento del consenso può essere aggiunto in un secondo momento ma fin tanto che non sarà aggiunto
                                non si potrà registrare alcun dato riguardante le sue prove';
$string['fileconsenso_help'] = 'Il documento del consenso può essere aggiunto in un secondo momento ma fin tanto che non sarà aggiunto
                                non si potrà registrare alcun dato riguardante le sue prove';
$string['show_reported'] = 'Mostra alunni segnalati';


// ----------------------------------------------- OPERATOR PAGE ELEMENTS ------------------------------------------------------- //
$string['confirm_students'] = 'Conferma studenti';
$string['assign_student'] = 'Gruppi';
$string['consensus_reinforce'] = 'Prescrizione';
$string['email_insegante'] = 'Email insegnante';
$string['email_object'] = 'Oggetto email';


// --------------------------------------------------- [CAPABILITIES] ----------------------------------------------------------- //
$string['coripodatacollection:addinstance'] = 'Aggiunge istanza del plugin CoRiPo data collection';
$string['coripodatacollection:view'] = 'Visualizza un\' istanza del plugin CoRiPo data collection';

$string['coripodatacollection:projectadmin'] = 'Accesso alle pagine per la creazione e aministrazione progetti';
$string['coripodatacollection:evaluator'] = 'Accesso alla visualizzazione dei dati presenti nel database alunni';
$string['coripodatacollection:schoolmanager'] = 'Accesso alle pagine per gestione istituti scolastici';
$string['coripodatacollection:teacher'] = 'Accesso alle pagine per gestione classi e inserimento esito prove alunni';

$string['coripodatacollection:sendtestemail'] = 'Test emailssssss';


// ------------------------------------------------------ [ROLES] --------------------------------------------------------------- //
$string['adminproject_role'] = 'Admin progetto';
$string['evaluator_role'] = 'Utente valutatore';
$string['institutemanager_role'] = 'Dirigente scolastico';
$string['teacher_role'] = 'Insegnante';
$string['operator'] = 'Operatore';


// --------------------------------------------------- [FORM STRINGS] ----------------------------------------------------------- //
$string['mandatoryformelement'] = 'Questo campo è obbligatorio';
$string['emailformelement'] = 'Email non valida';


// ---------------------------------------------- [INSTITUTE MENU ELEMENTS] ----------------------------------------------------- //
$string['istitutomenu'] = 'Istituto';
$string['progettimenu'] = 'Progetti';
$string['insegnantimenu'] = 'Insegnanti';
$string['instituteadminmenu'] = 'Admin istituto';
$string['dsgamenu'] = 'DSGA istituto';


// -------------------------------------------- INSTITUTE ADMIN PAGE ELEMENTS --------------------------------------------------- //
$string['schoolmanager'] = 'Amministrazione istituti';
$string['institutes_admin_overview'] = 'Panoramica istituti';
$string['submit_institutemodified'] = 'Modifica istituto';


// ---------------------------------------------------- EMAIL SENDING ----------------------------------------------------------- //
$string['messageprovider:sendtestemail'] = 'Test emailsssssssssssssssss';

// ------------------------------------------------------ [EMAILS] -------------------------------------------------------------- //
$string['add_project_evaluator_object'] = 'Avviso aggiunta a progetto come utente valutatore';
$string['add_project_evaluator_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore del progetto %s 
        l\'ha aggiunta come utente valutatore al progetto, presso il nostro sito
        %s. Login disponibile alla pagina: 
        
            %s
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_project_istitute_object'] = 'Avviso aggiunta a progetto dell\' istituto';
$string['add_project_istitute_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore del progetto %s 
        ha aggiunto l\'istituto %s, di cui lei censito come amministratore, presso il nostro sito
        %s. Login disponibile alla pagina: 
        
            %s
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_project_principal_object'] = 'Avviso aggiunta a progetto raccolta dati';
$string['add_project_principal_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore del progetto %s 
        l\'ha censita come direttore dell\'istituto %s, presso il nostro sito
        %s. Login disponibile alla pagina: 
        
            %s
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_istitute_teacher_object'] = 'Avviso aggiunta come insegnante di istituto';
$string['add_istitute_teacher_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore dell\'istituto %s 
        l\'ha aggiunta come insengnante dell\'istituto, registrato presso il nostro sito
        %s. Login disponibile alla pagina: 
        
            %s
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_newedition_evaluator_object'] = 'Avviso nuova erogazione progetto';
$string['add_newedition_evaluator_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore del progetto %s 
        ha creato una nuova edizione del progetto, presso il nostro sito
        %s. Login disponibile alla pagina: 
        
            %s
            
        La nuova edizione è denominata come %s.
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_newedition_istitute_object'] = 'Avviso nuova erogazione progetto';
$string['add_newedition_istitute_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore del progetto "%s" 
        ha aggiunto l\'istituto "%s", alla nuova edizione del proggeto, presso il nostro sito
        "%s". Login disponibile alla pagina: 
        
            %s
            
        La nuova edizione è denominata come "%s".
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_newedition_istitute_object'] = 'Avviso nuova erogazione progetto';
$string['add_newedition_istitute_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore del progetto "%s" 
        ha aggiunto l\'istituto "%s", alla nuova edizione del proggeto, presso il nostro sito
        "%s". Login disponibile alla pagina: 
        
            %s
            
        La nuova edizione è denominata come "%s".
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['add_edition_teacher_object'] = 'Avviso aggiunta come referente di classe';
$string['add_edition_teacher_body'] =
        'Gentile %s, 
        
        la seguente email per avvisarla che l\'amministratore dell\'istituto %s 
        l\'ha aggiunta come insengnante referente per la classe %s, plesso %s, 
        registrata nell\'edizione %s, presso il nostro sito %s. 
        Login disponibile alla pagina: 
        
            %s
        
        Saluti dall\'amministratore del sito, 
        Admin User.';

$string['open_result_request_object'] = 'Richiesta di riapertura registrazione risultati';
$string['open_result_request_body'] =
        'Gentile %s, 
        
        è stata inviata una richiesta per riaprire la registrazione dei risultati %s
        per la classe %s dell\'istituto comprensivo %s plesso %s 
        all\'interno dell\'erogazione %s del progetto %s, 
        di cui lei è amministratore. 
        
        Cordiali saluti.       
        ';

$string['evaluation_completed_object'] = 'Lettere per genitori bambini ricerca Predisa';
$string['evaluation_completed_body'] =
        'Gentile %s, 
        
        la seguente per informarla che è ora visualizzabile sulla piattaforma %s, 
        l\'elenco degli alunni dell\'istituto %s, di cui lei è stato registrato come direttore, 
        per l\'erogazione %s del progetto %s. 
        
        Clickando al seguente link sarà possibile visualizzare l\'elenco e scaricare i file PDF da consegnare alle famiglie
        
        %s       
        
        Cordiali saluti.       
        ';

$string['oggetto_email_genitore'] = 'Oggetto: Progetto PreDisA, partecipazione al corso di potenziamento %s';

$string['email_genitore'] = 'RIFERIMENTO: 
scuola: %s, %s
classe: %s
codice_alunno: %s

Gentile genitore,

il suo bambino/a è stato inserito al corso di potenziamento nel gruppo %s:
-	sede:	 %s, 	plesso %s, %s
-	giorni:	 %s e %s
-	orario:  %s

I corsi di potenziamento inizieranno a partire da lunedì 7 aprile.

Compili al più presto il modulo di risposta al seguente link:
https://sypdfcloud.aulss9.veneto.it:8444/rest/services/ModulisticaOnline/GetForm?in_string_formid=PreDisA&in_int_id_dipartimento=91&in_int_id_ente=20&in_string_extension=AF

e potrà confermare definitivamente l’iscrizione a questo gruppo proposto

oppure rifiutare

oppure segnalare sue eventuali difficoltà.

Nel caso di mancata risposta entro 24 ore dal ricevimento di questa mail l’iscrizione al gruppo non sarà più valida. 

Cordiali saluti

Il team PreDisA
';

$string['search_nonin_student'] = 'Cerca info studente';
$string['no_student_found'] = 'Nessuno studente trovato';