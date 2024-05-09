<?php
$host = 'localhost';
$dbname = 'filesystem';
$username = 'root';
$password = '';

try {

    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}


$tableName = "utenti";
$tableExists = $db->query("SHOW TABLES LIKE '$tableName'")->rowCount() > 0;

if (!$tableExists) {
    try {
        $db->exec("CREATE TABLE utenti (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255),
            cognome VARCHAR(255),
            email VARCHAR(255)
        )");

        // Inserisci dati di esempio solo se la tabella è stata appena creata
        $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Mario', 'Rossi', 'mario@email.com')");
        $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Luigi', 'Verdi', 'luigi@email.com')");
        $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Giovanna', 'Bianchi', 'giovanna@email.com')");
    } catch (PDOException $e) {
        die("Errore nella creazione della tabella utenti: " . $e->getMessage());
    }
}
//------------------- Esporta i dati in un file CSV con campi delimitati-----------------------------------
$query = $db->query("SELECT * FROM utenti");
/* <----La prima linea esegue una query SQL per selezionare tutti i record dalla tabella "utenti". */

echo " <pre>";
var_dump($query);
echo " </pre>";

$rows = $query->fetchAll(PDO::FETCH_ASSOC);
/* Il metodo fetchAll(PDO::FETCH_ASSOC) restituisce un array contenente tutti i risultati della query */

echo " <pre>";
print_r($rows);
echo " </pre>";

 $csvFile = fopen('utenti_delimitati.csv', 'w');
// /*<---  Viene creato un file CSV in modalità di scrittura ('w') utilizzando la funzione fopen() con  Il nome :"utenti_delimitati.csv". */

fputcsv($csvFile, array_keys($rows[0])); 
/* La funzione array_keys($rows[0]) restituisce un array con i nomi delle colonne estratti dal primo record dei risultati 
ovvero i campi che noi diamo al db tipo nome id email ecc*/



foreach ($rows as $row) {
    fputcsv($csvFile, $row); 
/* Per ogni record, la funzione fputcsv() viene utilizzata per scrivere i dati nel file CSV. I dati vengono formattati e scritti nel file CSV utilizzando i campi delimitati. */
}


fclose($csvFile);
// quest fclose serve per chiudere un file aperto da fopen quindi fallo sempre quando finisci di fare le tue operazioni al file csv aperto


// ----------------------------Esporta i dati in un file CSV con campi non delimitati-----------------------------------------
$csvFile = fopen('utenti_nondelimitati.csv', 'w');

fputcsv($csvFile, array_keys($rows[0])); 
foreach ($rows as $row) {
    fputcsv($csvFile, $row, "\t"); // -------------------------Delimitatore di tabulazione-------------------------
}
fclose($csvFile);





/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// -----------------------------------------Importa i dati dal file CSV al database-------------------------------------
$csvData = array_map('str_getcsv', file('utenti_delimitati.csv'));
// qua sto facendo l'inverso di sopra ovvero mi salvo quello che ho nel file csv nella variabile da me creata csvData
echo "--------------------------------------------------------------------------------";
echo " <pre>";
print_r($csvData);
echo " </pre>";


//la & serve per passare una variabile come referenza(opposto value)
array_walk($csvData, function(&$a) use ($csvData) {
    $a = array_combine($csvData[0], $a);
});




// -----------------------------------------Query per inserire nel database-------------------------------------
$query = $db->prepare("INSERT INTO utenti ( nome, cognome, email) VALUES ( :nome, :cognome, :email)");
foreach ($csvData as $row)
 {
  $query->execute(["nome"=>$row["nome"],
    "cognome"=>$row["cognome"],
    "email"=>$row["email"]]); 
}
// -----------------------------------------fine Query per inserire nel database-------------------------------------

$db = null;

echo "Esportazione e importazione completate con successo!";


