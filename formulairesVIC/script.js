document.addEventListener("DOMContentLoaded", function () {
    var signButton = document.getElementById('signer-button');
    var saveButton = document.getElementById('sig-saveBtn');
    var clearButton = document.getElementById('sig-clearBtn');
    var popupDiv = document.getElementById('popup');
    var signatureDataField = document.getElementById('signature-data');
    var canvas = document.getElementById('sig-canvas');
    var signaturePad = new SignaturePad(canvas);
    var counter = 1;

    signButton.addEventListener("click", function () {
        popupDiv.style.display = 'block';
        signaturePad.clear();
    });

    var previewImage = document.getElementById('signature-preview');

    saveButton.addEventListener("click", function () {
        if (!signaturePad.isEmpty()) {
            var signatureDataURL = signaturePad.toDataURL();
            signatureDataField.value = signatureDataURL;
            previewImage.src = signatureDataURL;
            previewImage.style.display = 'block';

            popupDiv.style.display = 'none';
        } else {
            alert("Veuillez signer avant de sauvegarder.");
        }
    });

    clearButton.addEventListener("click", function () {
        signaturePad.clear();
    });
});
