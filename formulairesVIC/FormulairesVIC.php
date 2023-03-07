<!DOCTYPE html>
<?php
  // Initialiser la session
  session_start();
  // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
  if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit(); 
  }
?>
<html>
    <head>
        <title>Formulaires VIC</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1">
        <link rel = "stylesheet" href = "style.css">
        <script src="html2pdf.bundle.min.js"></script>
        <script src="affichage.js"></script>
        <script src="signature.js"></script>
    </head>
    <button id = "pdf" value="Click"> download PDF </button>
    <script type="text/javascript">
    input = document.getElementById('pdf');
    input.addEventListener('click', function(){
        input.style.display = 'none';
    });
    document.getElementById('pdf').onclick = function (){
        var element = document.getElementById('formVIC');

        var opt = {
            margin:       1,
            filename:     'Formulaire-VIC.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'ex', format: 'a4', orientation: 'portrait' }
        };
        html2pdf(element,opt);
    };
    </script>
    <body id="formVIC">
        <?php
        try
        {
            $bdd = new PDO('mysql:host=localhost;dbname=SPS', 'root', 'FuRyAx2004');
        }
        catch(Exception $e)
        {
            die('Erreur : '.$e->getMessage());
        }
        ?>
        <form>
            <div class="formbold-main-wrapper">
                <div class="formbold-form-wrapper">
                    <div class="formbold-mb-5">
                        <fieldset>
                            <header>
                                <div class="flex flex-wrap formbold--mx-3">
                                    <div class="w-full sm:w-half formbold-px-3">
                                        <div class="formbold-mb-5 w-full">
                                            <img class="logo_Eau'sec" src="../images/Eau'Sec.png" class="formbold-form-label">
                                        </div>
                                    </div>
                                    <div class="w-full sm:w-half formbold-px-3">
                                        <div class="formbold-mb-5">
                                        <h1 class="formbold-form-label"><strong>Fiche D'inspection commune </h1></strong>
                                    </div>
                                </div>
                            </header>
                        </fieldset>
                    </div>
                    <br>
                    <div class="formbold-mb-5">
                        <label for="Chantier :" class="formbold-form-label">Chantier :</label>
                        <input type="text"name="Chantier"id="Chantier"class="formbold-form-input"/>
                    </div>
                    <div class="formbold-mb-5">
                        <label for="Maître d’Ouvrage" class="formbold-form-label">Maître d’Ouvrage :</label>
                        <input type="text"name="Maître d’Ouvrage"id="Maître d’Ouvrage"class="formbold-form-input" value="Eau 17"/>
                    </div>
                    <div class="formbold-mb-5">
                        <label for="Maître d’Œuvre" class="formbold-form-label">Maître d’Œuvre :</label>
                        <input type="text"name="Maître d’Œuvre"id="Maître d’Œuvre"class="formbold-form-input"/>
                    </div>
                    <div class="formbold-mb-5">
                        <label for="Lot concerné " class="formbold-form-label">Lot concerné  :</label>
                        <select id = "Lot concerné" name="Lot concerné" class="formbold-form-input" multiple multiselect-select-all="true">
                        <?php
                        $reponse = $bdd->query('SELECT * FROM lot ORDER BY Nom');
                        while ($donnees = $reponse->fetch())
                        {
                            ?>
                            <option value="<?php echo $donnees['Nom']; ?>"> <?php echo $donnees['Nom']; ?></option>
                            <?php
                            }    
                            ?>
                        </select>
                    </div>
                    <br><strong>Entreprise intervenante :</strong></br>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Titulaire :" class="formbold-form-label">Titulaire :</label>
                                <select id = "Titulaire" name="Titulaire"  class="formbold-form-input">
                                <?php
                                $reponse = $bdd->query('SELECT * FROM entreprise ORDER BY Nom');
                                while ($donnees = $reponse->fetch())
                                {
                                    ?>
                                    <option value="<?php echo $donnees['Nom']; ?>"> <?php echo $donnees['Nom']; ?></option>
                                    <?php
                                    }
                                
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="Sous-Traitant de" class="formbold-form-label">Sous-Traitant de :</label>
                                <input type="text"name="Sous-Traitant de"id="Sous-Traitant de"class="formbold-form-input"/>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Date début de travaux" class="formbold-form-label">Date début de travaux :</label>
                                <input type="date"name="Date début de travaux"id="Date début de travaux"class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="Date fin des travaux" class="formbold-form-label">Durée de travaux </label>
                                <input type="text"name="Date fin des travaux"id="Date fin des travaux"class="formbold-form-input"/>
                            </div>
                        </div>
                    </div>
                    <div class="formbold-mb-5">
                        <label for="Travaux sous-traités"class="formbold-form-label"> Travaux sous-traités :</label>
                        <input  type = "text" id ="Travaux sous-traités" name="Travaux sous-traités"class="formbold-form-input"/>
                    </div>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Effectifs moyens" class="formbold-form-label">Effectifs moyens :</label>
                                <input type="number"name="Effectifs moyens"id="Effectifs moyens"class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="Effectifs de pointe" class="formbold-form-label">Effectifs de pointe :</label>
                                <input type="number"name="Effectifs de pointe"id="Effectifs de pointe"class="formbold-form-input"/>
                            </div>
                        </div>
                    </div>
                    <strong><h3>Document préparatoire : </h3></strong>
                    <br>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="P.G.C" class="formbold-form-label">P.G.C :</label>
                                <input type="checkbox" name="P.G.C" id="P.G.C" class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="D.I.C.T" class="formbold-form-label">D.I.C.T :</label>
                                <input type="checkbox" name="D.I.C.T" id="D.I.C.T" class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                P.P.S.P.S<input type="checkbox" name="P.P.S.P.S" id="P.P.S.P.S" />
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="Arrêtés de circulation" class="formbold-form-label">Arrêtés de circulation :</label>
                                <input type="checkbox" name="Arrêtés de circulation" id="Arrêtés de circulation" class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                PRA (SS3)<input type="checkbox" name="PRA" id="PRA"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="MOA" class="formbold-form-label">MOA (SS4) :</label>
                                <input type="checkbox" name="MOA" id="MOA" class="formbold-form-input" />
                            </div>
                        </div>
                        <div class="formbold-mb-5">
                            <label for="Autres documents" class="formbold-form-label"> Autres documents</label>
                            <input  type = "text"  id ="Autres documents"  name="Autres documents" class="formbold-form-input"/>
                        </div>
                    </div>
                    <br><strong>installations de chantier :</strong>
                    <br><br>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Zones d’installation de chantier" class="formbold-form-label">Zones d’installation de chantier :</label>
                                <select id = "Zones d’installation de chantier " name="Zones d’installation de chantier " class="formbold-form-input" multiple multiselect-select-all="true">
                                <?php
                                $reponse = $bdd->query('SELECT * FROM zidc ORDER BY Nom');
                                while ($donnees = $reponse->fetch())
                                {
                                    ?>
                                    <option value="<?php echo $donnees['Nom']; ?>"> <?php echo $donnees['Nom']; ?></option>
                                    <?php
                                    }
                                
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="AutresChantier" class="formbold-form-label">Autres :</label>
                                <input type="text" name="AutresChantier" id="AutresChantier" class="formbold-form-input" />
                            </div>
                        </div>
                    </div>
                    <br><strong> D'accés : </strong></br>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Local existant" class="formbold-form-label">Local existant :</label>
                                <input type="checkbox" name="Local existant"id="Local existant"class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="Sanitaires" class="formbold-form-label">Sanitaires :</label>
                                <input type="checkbox" name="Sanitaires" id="Sanitaires" class="formbold-form-input"/>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Réfectoire" class="formbold-form-label">Réfectoire :</label>
                                <input type="checkbox" name="Réfectoire" id="Réfectoire" class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="Vestiaires" class="formbold-form-label">Vestiaires :</label>
                                <input type="checkbox" name="Vestiaires" id="Vestiaires" class="formbold-form-input"/>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap formbold--mx-3">
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="Repas pris au restaurant" class="formbold-form-label">Repas pris au restaurant :</label>
                                <input type="checkbox" name="LRepas pris au restaurant" id="Repas pris au restaurant" class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                                <label for="A.E.P." class="formbold-form-label">A.E.P. :</label>
                                <input type="checkbox" name="A.E.P." id="A.E.P." class="formbold-form-input"/>
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5 w-full">
                                <label for="E.U." class="formbold-form-label">E.U. :</label>
                                <input type="checkbox" name="E.U." id="E.U." class="formbold-form-input" />
                            </div>
                        </div>
                        <div class="w-full sm:w-half formbold-px-3">
                            <div class="formbold-mb-5">
                            <label for="Electricité" class="formbold-form-label">Electricité :</label>
                            <input type="checkbox" name="Electricité" id="Electricité" class="formbold-form-input"/>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap formbold--mx-3">
                    <div class="formbold-mb-5" >
                        <label for="Signalisation travaux" class="formbold-form-label"> Signalisation travaux:</label>
                        <select class="formbold-form-input" aria-label=".form-select-sm" id = "Signalisation travaux" name="Signalisation travaux" multiple multiselect-select-all="true">
                        <?php
                        $reponse = $bdd->query('SELECT * FROM signalisation ORDER BY Nom');
                        while ($donnees = $reponse->fetch())
                        {
                            ?>
                            <option value="<?php echo $donnees['Nom']; ?>"> <?php echo $donnees['Nom']; ?></option>
                            <?php
                            }    
                            ?>
                            </select>
                    </div>
                </div>
                <div class="flex flex-wrap formbold--mx-3">
                    <div class="w-full sm:w-half formbold-px-3">
                        <div class="formbold-mb-5 w-full">
                            <label for="Conditions et mode d’approvisionnement" class="formbold-form-label">Conditions et mode d’approvisionnement :</label>
                            <input type="text" name="Conditions et mode d’approvisionnement" id="Conditions et mode d’approvisionnement" class="formbold-form-input"/>
                        </div>
                    </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <label for="Survol de grue" class="formbold-form-label">Survol de grue :</label>
                        <select class="formbold-form-input" aria-label=".form-select-sm" id = "Survol de grue" name="Survol de grue">
                            <option value = "1">Grue fixe</option>
                            <option value = "2">Grue mobile </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Contrôle" class="formbold-form-label">Contrôle :</label>
                        <input type="text" name="Contrôle" id="Contrôle" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Secouristes" class="formbold-form-label">Secouristes :</label>
                        <input type="text" name="Secouristes" id="Secouristes" class="formbold-form-input"/>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Trousse d’urgence" class="formbold-form-label">Trousse d’urgence :</label>
                        <input type="checkbox" name="Trousse d’urgence" id="Trousse d’urgence" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Extincteur" class="formbold-form-label">Extincteur :</label>
                        <input type="checkbox" name="Extincteur" id="Extincteur" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Moyens d’alerte" class="formbold-form-label">Moyens d’alerte :</label>
                        <input type="text" name="Moyens d’alerte" id="Moyens d’alerte" class="formbold-form-input"/>
                    </div>
                </div>
            </div>
            <br><h3>Environnement :</h3>
            <br>
            <br> <h4>Déblais :</h4> </br>
            <br>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Evacuation" class="formbold-form-label">Evacuation :</label>
                        <input type="checkbox" name="Evacuation" id="Evacuation" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <label for="Stockage" class="formbold-form-label">Stockage :</label>
                        <input type="checkbox" name="Stockage" id="Stockage" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Bruit" class="formbold-form-label">Bruit :</label>
                        <input type="checkbox" name="Bruit" id="Bruit" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <label for="Poussières" class="formbold-form-label">Poussières :</label>
                        <input type="checkbox" name="Poussières" id="Poussières" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Chute de hauteur" class="formbold-form-label">Chute de hauteur</label>
                        <input type="checkbox" name="Chute de hauteur" id="Chute de hauteur" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5">
                        <label for="Risque d’ensevelissement" class="formbold-form-label">Risque d’ensevelissement</label>
                        <input type="checkbox" name="Risque d’ensevelissement" id="Risque d’ensevelissement" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Electrisation" class="formbold-form-label">Electrisation</label>
                        <input type="checkbox" name="Electrisation" id="Electrisation" class="formbold-form-input"/>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Tri des déchets" class="formbold-form-label">Tri des déchets :</label>
                        <input type="text" name="Tri des déchets" id="Tri des déchets" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Circulation" class="formbold-form-label">Circulation :</label>
                        <input type="text" name="Circulation" id="Circulation" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Autres" class="formbold-form-label">Autres :</label>
                        <input type="text" name="Autres" id="Autres" class="formbold-form-input"/>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Manœuvre d’engin" class="formbold-form-label">Manœuvre d’engin :</label>
                        <input type="text" name="Manœuvre d’engin" id="Manœuvre d’engin" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Protections à installer" class="formbold-form-label">Protections à installer :</label>
                        <input type="text" name="Protections à installer" id="Protections à installer" class="formbold-form-input"/>
                    </div>
                </div>
            </div>
            <br> <strong>Réseaux : </strong> </br>
            <div class="flex flex-wrap formbold--mx-3">
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Enterrés" class="formbold-form-label">Enterrés :</label>
                        <input type="text" name="Enterrés" id="Enterrés" class="formbold-form-input"/>
                    </div>
                </div>
                <div class="w-full sm:w-half formbold-px-3">
                    <div class="formbold-mb-5 w-full">
                        <label for="Aériens" class="formbold-form-label">Aériens :</label>
                        <input type="text" name="Aériens" id="Aériens" class="formbold-form-input"/>
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-half formbold-px-3">
                <div class="formbold-mb-5 w-full">
                    <label for="Risques exportés" class="formbold-form-label">Risques exportés :</label>
                    <input type="text" name="Risques exportés" id="Risques exportés" class="formbold-form-input"/>
                </div>
            </div>
            <div class="w-full sm:w-half formbold-px-3">
                <div class="formbold-mb-5 w-full">
                    <label for="Risques importés" class="formbold-form-label">Risques importés :</label>
                    <input type="text" name="Risques importés" id="Risques importés" class="formbold-form-input"/>
                </div>
            </div>
            <div class="formbold-mb-5">
                <label for="ISDD" class="formbold-form-label">Interventions susceptibles d’être dangereuses :</label>
                <select id = "ISDD" name="ISDD" class="formbold-form-input" multiple multiselect-select-all="true">
                <?php
                    $reponse = $bdd->query('SELECT * FROM protections ORDER BY Nom');
                    while ($donnees = $reponse->fetch())
                    {
                        ?>
                        <option value="<?php echo $donnees['Nom']; ?>"> <?php echo $donnees['Nom']; ?></option>
                        <?php
                        }
                        ?>
                </select>
            </div>
            <div class="w-full sm:w-half formbold-px-3">
                <div class="formbold-mb-5">
                    <label for="AutresChantier" class="formbold-form-label">Autres* :</label>
                    <input type="text" name="AutresChantier" id="AutresChantier" class="formbold-form-input" />
                </div>
            </div>
            <div class="formbold-mb-5">
                <label for="PAI" class="formbold-form-label">Protections à installer :</label>
                <select id = "PAI" name="PAI" class="formbold-form-input" multiple multiselect-select-all="true" >
                    <?php
                    $reponse = $bdd->query('SELECT * FROM protections ORDER BY Nom');
                    while ($donnees = $reponse->fetch())
                    {
                        ?>
                        <option value="<?php echo $donnees['Nom']; ?>"> <?php echo $donnees['Nom']; ?></option>
                        <?php
                        }
                        ?>
                </select>
            </div>
            <div class="w-full sm:w-half formbold-px-3">
                <div class="formbold-mb-5">
                    <label for="AutresChantier" class="formbold-form-label">Autres* :</label>
                    <input type="text" name="AutresChantier" id="AutresChantier" class="formbold-form-input" />
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <canvas id="sig-canvas" width="620" height="100">
                        </canvas>
                    </div>
                </div>
            </div>
        </form>
        <script src="multiselect-dropdown.js" ></script>
        <script src="signature.js"></script>
    </body>
</html>