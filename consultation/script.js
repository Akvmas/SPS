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

$(document).ready(function() {
    $('#newObservationButton').click(function(e) {
        e.preventDefault(); 
        var chantierId = $('#chantier_id').val();
        $.ajax({
            url: 'genOnglet.php',
            type: 'POST',
            data: { chantier_id: chantierId },
            success: function(response) {
                console.log("Nouvel onglet créé avec succès : ", response);
            },
            error: function(xhr, status, error) {
                console.error("Une erreur s'est produite: " + error);
            }
        });
    });
});
