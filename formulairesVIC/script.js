document.addEventListener("DOMContentLoaded", function() {
    var signButton = document.getElementById('signer-button');
    var saveButton = document.getElementById('sig-saveBtn');
    var clearButton = document.getElementById('sig-clearBtn');
    var popupDiv = document.getElementById('popup');
    var signatureDataField = document.getElementById('signature-data');
    var canvas = document.getElementById('sig-canvas');
    var previewImage = document.getElementById('signature-preview');
    var signaturePad;

    signButton.addEventListener("click", function() {
        popupDiv.style.display = 'block';
        signaturePad = new SignaturePad(canvas);
    });

    saveButton.addEventListener("click", function() {
        if (!signaturePad.isEmpty()) {
            var signatureDataURL = signaturePad.toDataURL();
            signatureDataField.value = signatureDataURL;

            // Mettre à jour la prévisualisation de la signature
            previewImage.src = signatureDataURL;

            // Fermer le popup
            popupDiv.style.display = 'none';
        } else {
            alert("Veuillez signer avant de sauvegarder.");
        }
    });

    clearButton.addEventListener("click", function() {
        signaturePad.clear();
    });
});
