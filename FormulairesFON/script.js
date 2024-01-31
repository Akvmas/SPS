var observationCount = 1;
var counter = 2;
var companyValues = [];
var effectiveValues = [];

window.onload = function () {
    for (let i = 1; i <= 3; i++) {
        ["autre", "reunion", "visite Inopinee"].forEach(function (type) {
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
        if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') {
            e.preventDefault();
        }
    });

    $(document).ready(function () {
        $('form input, form textarea').on('keypress', function (e) {
            if (e.which === 13 && e.target.tagName.toLowerCase() !== 'textarea') {
                return false;
            }
        });
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
function addInput(divName, event) {
    event.preventDefault();
    var newDiv = document.createElement('div');
    newDiv.className = 'personne-input';

    var newInput = document.createElement('input'); 
    newInput.type = 'text'; 
    newInput.name = 'personne[]'; 
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

$(document).ready(function () {
    $('form input').on('keypress', function (e) {
        return e.which !== 13;
    });
});
