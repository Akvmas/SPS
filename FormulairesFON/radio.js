for (let i = 1; i <= 3; i++) {
  document.querySelector(`input[name="typeVisite${i}"][value="autre"]`).addEventListener("change", function() {
      if (this.checked) {
          document.getElementById("autreText" + i).style.display = "block";
      }
  });

  document.querySelector(`input[name="typeVisite${i}"][value="reunion"]`).addEventListener("change", function() {
      if (this.checked) {
          document.getElementById("autreText" + i).style.display = "none";
      }
  });

  document.querySelector(`input[name="typeVisite${i}"][value="visite Inopinee"]`).addEventListener("change", function() {
    if (this.checked) {
        document.getElementById("autreText" + i).style.display = "none";
    }
});

  
}
