document.addEventListener("DOMContentLoaded", function () {
    var signButton = document.getElementById('signer-button');
    var saveButton = document.getElementById('sig-saveBtn');
    var clearButton = document.getElementById('sig-clearBtn');
    var popupDiv = document.getElementById('popup');
    var signatureDataField = document.getElementById('signature-data');
    var canvas = document.getElementById('sig-canvas');
    var signaturePad = new SignaturePad(canvas);
    var counter = 1; // Initialiser le compteur pour les champs dynamiques

    signButton.addEventListener("click", function () {
        popupDiv.style.display = 'block';
        signaturePad.clear(); // Effacez la signature précédente (si présente)
    });

    var previewImage = document.getElementById('signature-preview');

saveButton.addEventListener("click", function () {
    if (!signaturePad.isEmpty()) {
        var signatureDataURL = signaturePad.toDataURL();
        signatureDataField.value = signatureDataURL;
        previewImage.src = signatureDataURL; // Ajoutez cette ligne
        previewImage.style.display = 'block'; // Affichez la prévisualisation de la signature

        popupDiv.style.display = 'none';
    } else {
        alert("Veuillez signer avant de sauvegarder.");
    }
});

    clearButton.addEventListener("click", function () {
        signaturePad.clear();
    });
});
