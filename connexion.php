<?php
    try 
    {
        $bdd = new pdo('mysql:host=localhost;dbname=sps', 'root', '');
        // echo "La connexion avec la base de données est établie avec succès"."<br/>";
    }
    catch(Exception $e)
    {
        die('Erreur : '.$e->getMessage());
    } 
?>