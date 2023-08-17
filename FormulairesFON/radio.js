document.getElementById("autre").addEventListener("change", function() {
    if (this.checked) {
      document.getElementById("autreText").style.display = "block";
    }
  });
  
  document.getElementById("reunion").addEventListener("change", function() {
    if (this.checked) {
      document.getElementById("autreText").style.display = "none";
    }
  });
  
  document.getElementById("visiteInopinee").addEventListener("change", function() {
    if (this.checked) {
      document.getElementById("autreText").style.display = "none";
    }
  });
  