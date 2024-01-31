var observationCount = 1;
var counter = 2;
var companyValues = [];
var effectiveValues = [];

function openPdfGenerationPopup() {
    document.getElementById('pdfGenerationPopup').style.display = 'block';
}

function closePopup() {
    document.getElementById('pdfGenerationPopup').style.display = 'none';
}

function handleFormSubmission(event) {
    if (!validateForm()) {
        event.preventDefault();
    } else {
        var form = document.getElementById('myForm');
        var formData = new FormData(form);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'process.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                openPdfGenerationPopup();
            }
        };
        xhr.send(formData);
    }
}

document.getElementById('myForm').addEventListener('submit', handleFormSubmission);

document.getElementById('pdfGenerationPopup').querySelector('.close-popup-button').addEventListener('click', function () {
    closePopup();
});

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
        link.classList.remove("active");
    });
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
}

function addObservation() {
    var tabs = document.getElementById("tabs");
    var newTab = document.createElement("button");
    newTab.className = "tab-link";
    newTab.type = "button";
    newTab.textContent = "Observation " + observationCounter;
    newTab.onclick = function(event) { openTab(event, 'observation' + observationCounter) };
    tabs.appendChild(newTab);

    var partTwo = document.querySelector(".part-two");
    var newObservation = document.createElement("div");
    newObservation.id = "observation" + observationCounter;
    newObservation.className = "tab-content";
    newObservation.innerHTML = generateObservationContent(observationCounter);
    partTwo.appendChild(newObservation);

    observationCounter++;
}
function generateObservationContent(number) {
    return `
        <label>Type de visite:</label>
        <div class="radio-buttons">
            <label for="reunion${number}"><input type="radio" id="reunion${number}" name="typeVisite${number}" value="reunion">Réunion</label>
            <label for="visiteInopinee${number}"><input type="radio" id="visiteInopinee${number}" name="typeVisite${number}" value="visiteInopinee">Visite inopinée</label>
            <label for="autre${number}"><input type="radio" id="autre${number}" name="typeVisite${number}" value="autre">Autre</label>
        </div>
        <div class="input-group" id="autreText${number}" style="display: none;">
            <label for="autreDescription${number}">Précisez:</label>
            <input type="text" name="autreDescription${number}" id="autreDescription${number}">
        </div>
        <label>Date:</label>
        <input type="date" name="date${number}" id="date${number}">
        <label>Heure:</label>
        <input type="time" name="heure${number}" id="heure${number}">
        <br>
        <textarea name="observation${number}" rows="5" cols="50" maxlength="1000" placeholder="Saisissez votre observation ici..."></textarea>
        <br>
        <input type="file" name="photos${number}[]" accept="image/*" multiple>
        <br>
        <label for="entreprise${number}">Entreprise:</label>
        <input type="text" name="entreprise${number}" id="entreprise${number}">
        <br>
        <label for="effectif${number}">Effectif:</label>
        <input type="text" name="effectif${number}" id="effectif${number}">
    `;
}

function removeObservation(event) {
    event.preventDefault();
    if (observationCount > 1) {
        var tabToDelete = document.getElementById("observation" + observationCount);
        var buttonToDelete = document.querySelector('[data-tab="observation' + observationCount + '"]');
        tabToDelete.remove();
        buttonToDelete.remove();
        observationCount--;
    }
}

function addInput(divName, event) {
    event.preventDefault();
    var newDiv = document.createElement('div');
    newDiv.className = 'personne-input';

    var newInput = document.createElement('input');
    newInput.type = 'text';
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

    newDiv.appendChild(newInput);
    newDiv.appendChild(removeButton);

    document.getElementById(divName).appendChild(newDiv);
    counter++;
}

function removeInput(element, event) {
    event.preventDefault();
    element.parentNode.removeChild(element);
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.nodeName !== 'TEXTAREA') {
        e.preventDefault();
    }
});

document.querySelectorAll('form input').forEach(function (input) {
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.nodeName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
});
