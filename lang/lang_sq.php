<?php

/*
ucfirst(lang_get($_SESSION['language'],'ERROR_NO_INGREDIENT_SELECTED'))

lang_get($_SESSION['language'],'ERROR_NO_INGREDIENT_SELECTED')
*/
define('GLOBALMSG_CONFIG_FILE_NOT_WRITEABLE','File i konfugurimit (conf/config.inc.php) nuk mund te shkruhet. My Handy Restaurant non può funzionare senza quel file.<br>Controllare che il file esista e sia scrivibile o che la directory conf/ sia scrivibile.<br>Ricorda che il file o la directory devono essere scrivibile per l\'utente sotto il quale gira il web server.');
define('GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE','i file di log degli errori o di debug non sono scrivibili.<br>Per funzionare correttamente, My handy Restaurant (l\'utente sotto il quale gira il web server) deve poter scrivere quei file.<br>Per favore, controllare che i file siano esistenti e scrivibili, o che la directory in cui dovrebber essere non sia protetta da scrittura, così che My handy Restaurant li possa creare.');
define('GLOBALMSG_CONFIG_SYSTEM','<a href="../conf/index.php">Konfiguro My handy Restaurant</a>');
define('GLOBALMSG_CONFIGURE_DATABASES','<a href="../admin/admin.php?class=accounting_database&amp;command=none"><br/>Konfiguro database te My handy Restaurant</a>');
define('GLOBALMSG_DB_CONNECTION_ERROR','Errore: Si &egrave; verificato un errore nella connessione al server del database: provare a controllare il file config.php e se il database sia attivo.');
define('GLOBALMSG_DB_NO_TABLES_ERROR','Errore: Non &egrave; presente alcuna tabella nel database, impossibile procedere.');
define('GLOBALMSG_NO_ACCOUNTING_DB_FOUND','Errore: non c\'&egrave; alcun database per la contaiblit&agrave;, impossbile procedere.<br>My Handy Restaurant ha bisogno di un database comune e almeno un database contabilit&agrave;.');

define('GLOBALMSG_ACTION_IS_DEFINITIVE','Aksioni eshte <b>definitiv</b>');

define('GLOBALMSG_FROM','nga');
define('GLOBALMSG_FROM_TIME','nga');
define('GLOBALMSG_FROM_DAY','nga');

define('GLOBALMSG_GO_BACK','kthehu prapa');

define('GLOBALMSG_INSERTING','Jam duke ruajtur');
define('GLOBALMSG_ITEM','Produkti');
define('GLOBALMSG_INVOICE','Fatura');
define('GLOBALMSG_INVOICE_ASSOCIATED','Fatuara u asociua');
define('GLOBALMSG_INVOICE_PAID','Paguar');

define('GLOBALMSG_INDEX_WHO_ARE_YOU','Kush jeni?');
define('GLOBALMSG_INDEX_SUBMIT','Hyr');

define('GLOBALMSG_NAME','Emri');
define('GLOBALMSG_NO','Jo');
define('GLOBALMSG_NONE_FEMALE','Asnjera');
define('GLOBALMSG_NOTE','Shenim');
define('GLOBALMSG_NOTE_UPDATE','Azhorno shenimin');

define('GLOBALMSG_ONLY','vetem');
define('GLOBALMSG_OF_DAY','te');
define('GLOBALMSG_OR','ose');
define('GLOBALMSG_OTHER_FILE','Skeda tjeter');
define('GLOBALMSG_OUTGOING_MANY','dalje');

define('GLOBALMSG_PAGE_TIME','sekonda per gjenerimin e faqes');
define('GLOBALMSG_PHONE','Telefoni');
define('GLOBALMSG_PLACE','vendi');
define('GLOBALMSG_POS_CIRCUIT_FILE','Skeda POS');
define('GLOBALMSG_PRICE','Cmimi'); 
define('MSG_PAPER_PRINT_REMOVE','FSHIRE');
define('MSG_PAPER_PRINT_TABLE','Tavolina');
define('MSG_PAPER_PRINT_PRIORITY','Prioriteti');
define('MSG_PAPER_PRINT_WAITER','Kamerieri');
define('MSG_PAPER_PRINT_DISCOUNT','Skonto');
define('MSG_PAPER_PRINT_TAXABLE','Taksueshem');
define('MSG_PAPER_PRINT_TAX','Taksa');
define('MSG_PAPER_PRINT_TAX_TOTAL','Totali taksa');
define('MSG_PAPER_PRINT_CURRENCY','Lek');
define('MSG_PAPER_PRINT_TOTAL','Totali');
define('MSG_PAPER_PRINT_BILL','Deftese');
define('MSG_PAPER_PRINT_INVOICE','Fatura');
define('MSG_PAPER_PRINT_RECEIPT','Skeda');
define('MSG_PAPER_PRINT_NUMBER_ABBREVIATED','N.');
define('MSG_PAPER_PRINT_A_LOT','SHUM');
define('MSG_PAPER_PRINT_FEW','PAK');
define('MSG_PAPER_PRINT_ATTENTION','KUJDES');
define('MSG_PAPER_PRINT_WAIT','PRIT');
define('MSG_PAPER_PRINT_GO','Fillo');
define('MSG_PAPER_PRINT_GO_NOW','Fillo Menjehere');
define('GLOBALMSG_PAPER_PRINT_TAKEAWAY','Aksport');
define('GLOBALMSG_PERIOD','periudha');

define('GLOBALMSG_QUANTITY','Sasia');

define('GLOBALMSG_RECEIPT_ID','Id');
define('GLOBALMSG_RECEIPT_ID_INTERNAL','Id brendshem');
define('GLOBALMSG_RECEIPT_ANNULLED_RECEIPT','Deftesa u anullua');
define('GLOBALMSG_RECEIPT_ANNULLED_INVOICE','Fatura u anullua');
define('GLOBALMSG_RECEIPT_ANNULLED_BILL','Skeda u anullua');
define('GLOBALMSG_RECEIPT_ANNULL_CONFIRM','Jeni i sigurt se doni ti fshini te gjitha ?');
define('GLOBALMSG_RECEIPT_ID_INTERNAL','Id brendshem');

define('GLOBALMSG_RECORD_ANNULL','Anullo');
define('GLOBALMSG_RECORD_ANNULLED','U anullua');
define('GLOBALMSG_RECORD_ANNULLED_ABBREVIATED','AN');
define('GLOBALMSG_RECORD_NONE_SELECTED_ERROR','Nuk zgjollet asgje');
define('GLOBALMSG_RECORD_NONE_FOUND_ERROR','Nuk eshte gjetur asgje');
define('GLOBALMSG_RECORD_NONE_FOUND_PERIOD_ERROR','Nuk u gjend asgje ne periudhen e kerkuar');
define('GLOBALMSG_RECORD_CHANGE_SEARCH','Provoni te ndryshoni periudhe');
define('GLOBALMSG_RECORD_DELETE_CONFIRM','Sigurt se doni ta fshini?');
define('GLOBALMSG_RECORDS_DELETE_CONFIRM','Sigurt se doni ti fshini?');
define('GLOBALMSG_RECORD_DELETE','Fshij zerin');
define('GLOBALMSG_RECORD_DELETE_SELECTED','Fshij zerat e zgjedhur');
define('GLOBALMSG_RECORD_EDIT','Modifiko zerin');
define('GLOBALMSG_RECORD_INSERT','Levizje Lek');
define('GLOBALMSG_RECORD_OUTGOING','Ne Dalje');
define('GLOBALMSG_RECORD_INCOMING','Ne Hyrje');
define('GLOBALMSG_RECORD_INVOICE','Fatura');
define('GLOBALMSG_RECORD_POS','POS');
define('GLOBALMSG_RECORD_BILL','Skeda');
define('GLOBALMSG_RECORD_CHEQUE','Cek');
define('GLOBALMSG_RECORD_RECEIPT','Deftese');
define('GLOBALMSG_RECORD_DEPOSIT','Derdhje');
define('GLOBALMSG_RECORD_WIRE_TRANSFER','Derdhje bankare');
define('GLOBALMSG_RECORD_PAYMENT','Pagese');
define('GLOBALMSG_RECORD_PAYMENT_DATE','Data e pageses');
define('GLOBALMSG_RECORD_PAID','Paguar');
define('GLOBALMSG_RECORD_THE_MANY','Zerat');
define('GLOBALMSG_RECORD_DELETE_OK_MANY','jane fshire me sukses');
define('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY','jane fshire me sukses nga log');
define('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY_2','Zerat e log jane fshire');
define('GLOBALMSG_RECORD_THE','Zeri');
define('GLOBALMSG_RECORD_DELETE_OK','eshte fshire');
define('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG','eshte fshire nga log');
define('GLOBALMSG_RECORD_DELETE_SELECTED','Fshij zerat e zgjedhur');
define('GLOBALMSG_RECORD_DELETE_NONE','Nuk eshte zgjedhur asnje ze');
define('GLOBALMSG_RECORD_ADD_OK','eshte shtuar me sukses');
define('GLOBALMSG_RECORD_ADD_NONE','Nuk eshte shtuar asnje ze');
define('GLOBALMSG_RECORD_EDIT_OK','modifikimi u krye me sukses');
define('GLOBALMSG_RECORD_EDIT_NONE','Nuk eshte modifikuar asnje ze');
define('GLOBALMSG_RECORD_EDIT_NOT_DONE','Nuk eshte modifikuar asnje ze');
define('GLOBALMSG_RECORD_TITLE_FOR','Zerat per');
define('GLOBALMSG_RECORD_TITLE_FOR_NOT_IN_ADDRESSBOOK','Zerat per kontaktin nuk jane ne rubrike');
define('GLOBALMSG_RECORD_TITLE_FOR_TYPE','Zerat per kontaktet e llojit');
define('GLOBALMSG_RECORD_TITLE_INCOME_TYPE','Xhiroja i llojit');
define('GLOBALMSG_RECORD_TITLE_INCOME','Xhiroja');
define('GLOBALMSG_RECORD_TITLE_ALL','Te gjitha zerat');
define('GLOBALMSG_RECORD_PRINTABLE','Versioni i printueshem ( ne prove )');
define('GLOBALMSG_RECORD_TABLE_','Versioni i printueshem ( ne prove )');
define('GLOBALMSG_REPORT_ACCOUNT','Llogarija');
define('GLOBALMSG_REPORT_GENERATE','Gjenero raportin');
define('GLOBALMSG_REPORT_PERIOD','Periudha e raportit');

define('GLOBALMSG_STATS','Statistika');
define('GLOBALMSG_STATS_DISHES_ORDERED','Gjelle te porositura');
define('GLOBALMSG_STATS_INGREDIENTS_ADDED','Perberesit e shtuar');
define('GLOBALMSG_STATS_INGREDIENTS_REMOVED','Perberesit e hequr');
define('GLOBALMSG_STATS_MYSQL_TIME','sekonda per query mySQL');
define('GLOBALMSG_STATS_RECORDS_SCANNED','zera te kontrolluar');
define('GLOBALMSG_STATS_TOTAL_DEPTS','Totali sektorit');
define('GLOBALMSG_STATS_TOTAL_PERIOD','Totale periudhes');
define('GLOBALMSG_STOCK_ADD_OK','Produkti i ri u shtua');
define('GLOBALMSG_STOCK_ADD_ERROR','Gabim gjate ruatjes se produktit');
define('GLOBALMSG_STOCK_ITEM_ADD','Shto produkt');
define('GLOBALMSG_STOCK_ITEM_NAME','Emri i produktit');
define('GLOBALMSG_STOCK_ITEM_INITIAL_QUANTITY','Sasia fillestare');
define('GLOBALMSG_STOCK_MOVEMENTS','Levizjet ne magazine');
define('GLOBALMSG_STOCK_MOVEMENT_INSERT','Shto nje levizje ne magazine');
define('GLOBALMSG_STOCK_MOVEMENT_INSERT_ERROR','Gabim gjate ruajtjes se levizjes ne magazine');
define('GLOBALMSG_STOCK_MOVEMENT_NONE_ASSOCIATED_TO_INVOICE','Asnje levizje ne magazine e asociuar me faturen');
define('GLOBALMSG_STOCK_SEND_TO','Dergo ne magazine');
define('GLOBALMSG_STOCK_SITUATION','Gjendja e magazines');
define('GLOBALMSG_STOCK_DATA_UPDATE','Azhorno te dhenat');
define('GLOBALMSG_STOCK_UPDATE_ERROR','Gabim gjate azhornimit');
define('GLOBALMSG_STOCK_UPDATE_OK','Magazina u azhornuar');
define('GLOBALMSG_SUPPLIER_FILE','Skeda e fornitorit');

define('GLOBALMSG_TABLE','Tavolina'); 
define('GLOBALMSG_TABLES','Tavolinat'); 
define('GLOBALMSG_TABLE_NONE_FOUND','Nuk gjendet asnje tavoline'); 
define('GLOBALMSG_TABLE_NONE_SELECTED','Nuk eshte zgjedhur asnje tavoline'); 
define('GLOBALMSG_TABLE_THE','Tavolina'); 
define('GLOBALMSG_TABLE_ID','Id (numri i llogarise)'); 
define('GLOBALMSG_TABLE_INSERT_NEW','Jep nje tavoline te re'); 
define('GLOBALMSG_TABLE_INSERT','Jep nje tavoline'); 
define('GLOBALMSG_TABLE_UPDATE','Modifiko tavolinen'); 
define('GLOBALMSG_TABLE_DELETE','Fshij tavolinen'); 
define('GLOBALMSG_TABLE_NUMBER','Numri ose emri (i treguar)'); 
define('GLOBALMSG_TABLE_TABLE_ID','Id'); 
define('GLOBALMSG_TABLE_TABLE_NUMBER','Numri/Emri'); 
define('GLOBALMSG_TABLE_TAKEAWAY','Takeaway'); 
define('GLOBALMSG_TAXABLE','Taksueshem');
define('GLOBALMSG_TAX','Taksa');
define('GLOBALMSG_TAX_NUMBER','NIPT');
define('GLOBALMSG_TAX_MANY','Taksa');
define('GLOBALMSG_TAX_TO_PAY','Taksa per tu paguar jane');
define('GLOBALMSG_TAX_TO_PAY_INVOICE_EXCLUDED','pervec faturat e papaguara');
define('GLOBALMSG_TAX_TO_PAY_INVOICE_INCLUDED','me faturat e papaguara');
define('GLOBALMSG_TIME','Ora');
define('GLOBALMSG_TYPE','Lloji');
define('GLOBALMSG_TO','ne');
define('GLOBALMSG_TO_DAY','ne');
define('GLOBALMSG_TO_TIME','ne');
define('GLOBALMSG_TOTAL','totali');

define('GLOBALMSG_VAT_ACCOUNT','NIPT');
define('GLOBALMSG_VAT_CALCULATION','Llogarit Taksat');

define('MSG_WAITER_NOT_CONNECTED_ERROR','Nuk je i lidhur.');

define('GLOBALMSG_WAITER','Kamerieri'); 
define('GLOBALMSG_WAITERS','Kamerieret'); 
define('GLOBALMSG_WAITER_NONE_FOUND','Nuk gjendet asnje kamerier'); 
define('GLOBALMSG_WAITER_NONE_SELECTED','Nuk eshte seleksionuar asnje kamerier'); 
define('GLOBALMSG_WAITER_THE','Kamerieri'); 
define('GLOBALMSG_WAITER_NAME','Emri'); 
define('GLOBALMSG_WAITER_LANGUAGE','Gjuha'); 
define('GLOBALMSG_WAITER_CAN_OPEN_CLOSED_TABLES','Mund te hape tavolinat e mbyllura (dhe modifikoje cmimin e pjatave te pergjithshme)'); 
define('GLOBALMSG_WAITER_INSERT_NEW','Ruaj kamerierin e ri'); 
define('GLOBALMSG_WAITER_INSERT','Jep kamerierin'); 
define('GLOBALMSG_WAITER_UPDATE','Modifiko kamerierin'); 
define('GLOBALMSG_WAITER_DELETE','Fshij kamerierin'); 
define('GLOBALMSG_WAITER_TABLE_NAME','Emri'); 
define('GLOBALMSG_WAITER_TABLE_LANGUAGE','Gjuha'); 
define('GLOBALMSG_WAITER_TABLE_CAN_OPEN_CLOSED_TABLES','Hap tavolinat e mbyllura'); 
define('GLOBALMSG_WEBSITE','Siti web');

define('GLOBALMSG_YES','Po');



$msg_admin_confirm_reset_orders="
<b>Doni vertet te fshih te gjitha urdherat?</b><br>
Ky veprim eshte i <b>perhershem</b> dhe shkakton
 humbjen e te gjitha komandave.";
$msg_admin_confirm_reset_sources="
<b>Doni me te vertete te fshini te gjitha tavolinat?</b><br>
Ky veprim eshte i <b>perhershem</b> dhe shkakton
 <b>dhe</b> humbjen e te gjitha komandave.";
$msg_admin_confirm_reset_access_times="
<b>Doni me te vertete te fshini te gjithe oret e hyrjes</b><br>
Ky veprim eshte i <b>perhershem</b> dhe shkakton 
 nderprerjen momentale te mbrotjes se tavolinave.<br>
Keshillohet te perdoret vetem nqs ndryshohet ora e sistemit.";
$msg_reset_orders="Fshij urdherat";
$msg_reset_access_times="Fshij oret e hyrjes";
$msg_reset_sources="Azzera tutti i tavoli";
$but_reset_access_times="Fshij";
$but_reset_orders="Fshij";
$but_reset_sources="Fshij";
$msg_reset_access_times_ok="Te gjithe kohet e hyrjes u fshine";
$msg_reset_orders_ok="Te gjithe urdherat u fshine";
$msg_reset_sources_ok="Te gjitha tavolinat dhe urdherat u fshine";
$msg_admin_confirmhalt="Doni te fikni kompjuterin qendror?";
$msg_halt="Fik kopjuterin PC";
$but_halt="Fik";
$msg_halt_ok="Procedura e fikjes filloi. Fikja brenda $halttime minutash";


?>