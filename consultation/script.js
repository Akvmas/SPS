var observationCount = 1;
var counter = 2;
var companyValues = [];
var effectiveValues = [];

window.onload = function () {
    for (let i = 1; i <= 3; i++) {
        ["autre", "reunion", "visiteInopinee"].forEach(function (type) {
            document.getElementById(type + i).addEventListener("change", function () {
                document.getElementById("autreText" + i).style.display = this.checked && type == "autre" ? "block" : "none";
            });
        });
    }

    document.getElementById('myForm').addEventListener('submit', function (event) {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    document.querySelector('form').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
}
function validateForm() {
    companyValues = [];
    effectiveValues = [];
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].required && !inputs[i].value) {
            return false;
        }
    }
    for (var i = 1; i <= observationCount; i++) {
        var companyInput = document.getElementById('entreprise' + i);
        var effectiveInput = document.getElementById('effectif' + i);
        if (companyInput && effectiveInput) {
            var companyValue = companyInput.value;
            var effectiveValue = effectiveInput.value;
            if (companyValues.includes(companyValue) || effectiveValues.includes(effectiveValue)) {
                alert("Les valeurs de l'entreprise et de l'effectif doivent être uniques pour chaque observation.");
                return false;
            }
            companyValues.push(companyValue);
            effectiveValues.push(effectiveValue);
        }
    }
    return true;
}

function autoResize() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
}

function openTab(evt, tabName) {
    var tabContent = document.getElementsByClassName("tab-content");
    Array.from(tabContent).forEach(tab => {
        tab.style.display = "none";
    });
    var tabLinks = document.getElementsByClassName("tab-link");
    Array.from(tabLinks).forEach(link => {
        link.className = link.className.replace(" active", "");
    });
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

function addObservation(event) {
    event.preventDefault();

    // Loop pour ajouter 2 nouvelles observations
    for (let i = 0; i < 1; i++) {
        observationCount++;
        var newObservationCount = observationCount;

        var tabs = document.getElementById("tabs");
        var newButton = document.createElement("button");
        newButton.className = "tab-link";
        newButton.innerHTML = "Observation " + newObservationCount;
        newButton.onclick = (event) => openTab(event, 'observation' + newObservationCount);
        tabs.appendChild(newButton);

        var newDiv = document.createElement("div");
        newDiv.id = "observation" + newObservationCount;
        newDiv.className = "tab-content";
        newDiv.innerHTML = `
            <label>Type de visite:</label>
            <div class="radio-buttons">
                <label for="reunion${newObservationCount}"><input type="radio" id="reunion${newObservationCount}" name="typeVisite${newObservationCount}" value="reunion">Réunion</label>
                <label for="visiteInopinee${newObservationCount}"><input type="radio" id="visiteInopinee${newObservationCount}" name="typeVisite${newObservationCount}" value="visiteInopinee">Visite inopinée</label>
                <label for="autre${newObservationCount}"><input type="radio" id="autre${newObservationCount}" name="typeVisite${newObservationCount}" value="autre">Autre</label>
            </div>
            <div class="input-group" id="autreText${newObservationCount}" style="display: none;">
                <label for="autreDescription${newObservationCount}">Précisez:</label>
                <input type="text" name="autreDescription${newObservationCount}" id="autreDescription${newObservationCount}">
            </div>
            <label>Date:</label>
            <input type="date" name="date${newObservationCount}" id="date${newObservationCount}">
            <label>Heure:</label>
            <input type="time" name="heure${newObservationCount}" id="heure${newObservationCount}">
            <br>
            <textarea name="observation${newObservationCount}" rows="5" cols="50" maxlength="1000" placeholder="Saisissez votre observation ici..." ></textarea><br>
            <input type="file" name="photo${newObservationCount}" accept="image/*" ><br>
            <label for="entreprise${newObservationCount}">Entreprise:</label>
            <input type="text" name="entreprise${newObservationCount}" id="entreprise${newObservationCount}" ><br>
            <label for="effectif${newObservationCount}">Effectif:</label>
            <input type="text" name="effectif${newObservationCount}" id="effectif${newObservationCount}" >
        `;

        document.getElementById("tabs").after(newDiv);

        // Ajout d'un écouteur d'événements pour gérer l'affichage du champ "Précisez"
        document.getElementById("autre" + newObservationCount).addEventListener("change", function () {
            document.getElementById("autreText" + newObservationCount).style.display = this.checked ? "block" : "none";
        });

        var companyInput = document.getElementById('entreprise' + newObservationCount);
        var effectiveInput = document.getElementById('effectif' + newObservationCount);
        if (companyInput && effectiveInput) {
            companyInput.value = '';
            effectiveInput.value = '';
        }

        newButton.click();
    }
}


function removeObservation(event) {
    event.preventDefault();
    if (observationCount > 1) {
        var tabToDelete = document.getElementById("observation" + observationCount);
        var buttonToDelete = document.getElementsByClassName("tab-link")[observationCount - 1];
        tabToDelete.remove();
        buttonToDelete.remove();
        observationCount--;
    }
}
function addInput(divName, event) {
    event.preventDefault();
    var newDiv = document.createElement('div');
    newDiv.className = 'personne-input';

    var newInput = document.createElement('input'); // Créez un élément input
    newInput.type = 'text'; // Assurez-vous qu'il s'agit d'un champ de texte
    newInput.name = 'personne' + counter;
    newInput.id = 'personne' + counter;
    newInput.required = true;

    var removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'remove-button';
    removeButton.textContent = 'x';
    removeButton.onclick = function (event) {
        removeInput(newDiv, event);
    };

    newDiv.appendChild(newInput); // Ajoutez l'élément input à la div
    newDiv.appendChild(removeButton);

    document.getElementById(divName).appendChild(newDiv);
    counter++;
}

function removeInput(element, event) {
    event.preventDefault();
    element.parentNode.removeChild(element);
}

$(document).ready(function () {
    $('form input').on('keypress', function (e) {
        return e.which !== 13;
    });
});
