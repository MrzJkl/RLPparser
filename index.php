<?php
    // Verbindungsdaten fuer MySQL
    $servername = '<<SERVER>>';
    $username = '<<USERNAME>>';
    $password = '<<PASSWORD>>';
    $databaseName = '<<DATABASE>>';
    $tableName = '<<TABLENAME>>';

    // XML-Datei oeffnen
    $xml = simplexml_load_file('einsatz.xml');

    // Einsatzdaten erfassen
    $einsatzdaten = array();
    $einsatzdatenKeys = $xml->Data;
    $einsatzdatenValues = $xml->Row[1]->Column;

    // Fuer jede "Spalte"
    for($i = 0; $i < count($einsatzdatenKeys); $i++) {
        $key = (string) $einsatzdatenKeys[$i]->attributes()[1];
        $value = (string) $einsatzdatenValues[$i]->attributes()[0];
        $einsatzdaten[$key] = $value;
    }

    // Einsatzmittel erfassen
    $einsatzmittel = array();
    $einsatzmittelKeys = $xml->Row[1]->Table[2]->Data;
    $einsatzmittelValues = $xml->Row[1]->Table[2]->Row;

    // Fuer jedes Einsatzmittel
    for($j = 1; $j < count($einsatzmittelValues); $j++) {
        // Fuer jede "Spalte" im Einsatzmittel
        for($i = 0; $i < count($einsatzmittelKeys); $i++) {
            $key = (string) $einsatzmittelKeys[$i]->attributes()[1];
            $value = (string) $einsatzmittelValues[$j]->Column[$i]->attributes()[0];
            $einsatzmittel[$j][$key] = $value;
        }
    }

    // Meldungen erfassen
    $meldungen = array();
    $meldungenKeys = $xml->Row[1]->Table[3]->Data;
    $meldungenValues = $xml->Row[1]->Table[3]->Row;

    // Fuer jede Meldung
    for($j = 1; $j < count($meldungenValues); $j++) {
        // Fuer jede "Spalte" in der Meldung
        for($i = 0; $i < count($meldungenKeys); $i++) {
            $key = (string) $meldungenKeys[$i]->attributes()[1];
            $value = (string) $meldungenValues[$j]->Column[$i]->attributes()[0];
            $meldungen[$j][$key] = $value;
        }
    }

    // Massnahmen erfassen
    $massnahmen = array();
    $massnahmenKeys = $xml->Row[1]->Table[4]->Data;
    $massnahmenValues = $xml->Row[1]->Table[4]->Row;

    // Fuer jede Massnahme
    for($j = 1; $j < count($massnahmenValues); $j++) {
        // Fuer jede "Spalte" in der Massnahme
        for($i = 0; $i < count($massnahmenKeys); $i++) {
            $key = (string) $massnahmenKeys[$i]->attributes()[1];
            $value = (string) $massnahmenValues[$j]->Column[$i]->attributes()[0];
            $massnahmen[$j][$key] = $value;
        }
    }

    // MySQL-Verbindung herstellen
    $conn = new mysqli($servername, $username, $password);

    // Verbindung pruefen, wenn Fehler dann Abbruch
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Einsatzdaten fuer SQL vorbereiten und zusammenbauen
    $einsatzdatenPrefix = $einsatzdatenColumnNames = $einsatzdatenValues = '';
    foreach ($einsatzdaten as $key => $value) {
        $einsatzdatenColumnNames .= $einsatzdatenPrefix . $key;
        $einsatzdatenValues .= $einsatzdatenPrefix . "'" . $value . "'";
        $einsatzdatenPrefix = ', ';
    }

    // Einsatzmittel fuer SQL vorbereiten und zusammenbauen
    $einsatzmittelPrefix = $einsatzmittelValues = '';
    foreach ($einsatzmittel as $key => $value) {
        $einsatzmittelValues .= $einsatzmittelPrefix . implode(', ', $value);
        $einsatzmittelPrefix = ', ';
    }

    // Meldungen fuer SQL vorbereiten und zusammenbauen
    $meldungenPrefix = $meldungenValues = '';
    foreach ($meldungen as $key => $value) {
        $meldungenValues .= $meldungenPrefix . implode(', ', $value);
        $meldungenPrefix = ', ';
    }

    // Massnahmen fuer SQL vorbereiten und zusammenbauen
    $massnahmenPrefix = $massnahmenValues = '';
    foreach ($massnahmen as $key => $value) {
        $massnahmenValues .= $massnahmenPrefix . implode(', ', $value);
        $massnahmenPrefix = ', ';
    }

    // SQL-Skript zusammenbauen
    $sql = "INSERT INTO $databaseName.$tableName ($einsatzdatenColumnNames, einsatzmittel, meldungen, massnahmen) . 
        VALUES ($einsatzdatenValues, '$einsatzmittelValues', '$meldungenValues', '$massnahmenValues')";

    // SQL ausfuehren
    $conn->query($sql);    

    // Verbindung schliessen
    $conn->close();
?> 