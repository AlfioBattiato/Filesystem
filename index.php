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


try {
    $db->exec("CREATE TABLE IF NOT EXISTS utenti ( /*  exec Questa funzione esegue la query SQL e restituisce il numero di righe interessate dall'operazione,  */
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(255),
                    cognome VARCHAR(255),
                    email VARCHAR(255)
                )");
} catch (PDOException $e) {
    die("Errore nella creazione della tabella utenti: " . $e->getMessage());
}

try {
    $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Mario', 'Rossi', 'mario@email.com')");
    $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Luigi', 'Verdi', 'luigi@email.com')");
    $db->exec("INSERT INTO utenti (nome, cognome, email) VALUES ('Giovanna', 'Bianchi', 'giovanna@email.com')");
} catch (PDOException $e) {
    die("Errore nell'inserimento dei dati di esempio: " . $e->getMessage());
}

//------------------- Esporta i dati in un file CSV con campi delimitati-----------------------------------
$query = $db->query("SELECT * FROM utenti"); 
/* <----La prima linea esegue una query SQL per selezionare tutti i record dalla tabella "utenti". */
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
 /* Il metodo fetchAll(PDO::FETCH_ASSOC) restituisce un array contenente tutti i risultati della query */
$csvFile = fopen('utenti_delimitati.csv', 'w');
/*<---  Viene aperto un file CSV in modalità di scrittura ('w') utilizzando la funzione fopen(). Il nome del file è "utenti_delimitati.csv". */
fputcsv($csvFile, array_keys($rows[0])); 
/* La funzione array_keys($rows[0]) restituisce un array con i nomi delle colonne estratti dal primo record dei risultati */
foreach ($rows as $row) {
    fputcsv($csvFile, $row); 
/* Per ogni record, la funzione fputcsv() viene utilizzata per scrivere i dati nel file CSV. I dati vengono formattati e scritti nel file CSV utilizzando i campi delimitati. */
}
fclose($csvFile);

// ----------------------------Esporta i dati in un file CSV con campi non delimitati-----------------------------------------
$csvFile = fopen('utenti_nondelimitati.csv', 'w');
foreach ($rows as $row) {
    fputcsv($csvFile, $row, "\t"); // -------------------------Delimitatore di tabulazione-------------------------
}
fclose($csvFile);

// -----------------------------------------Importa i dati dal file CSV al database-------------------------------------
$csvData = array_map('str_getcsv', file('utenti_delimitati.csv'));
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


