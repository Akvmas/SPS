let personneCount = 0;
let observationCount = 0;

$(document).ready(function() {
    personneCount = $("#dynamicInput .personne-input").length;
    observationCount = $(".tab-content").length;

    window.addInput = function(divName) {
        personneCount++;
        let newDiv = document.createElement('div');
        newDiv.className = 'personne-input';
        newDiv.innerHTML = `
        <input type="text" name="personne${personneCount}" id="personne${personneCount}" required>
        <button type="button" class="remove-button" onclick="removeInput(this)">x</button>
        <button type="button" onclick="addInput('dynamicInput')">+</button>
        `;
        document.getElementById(divName).appendChild(newDiv);
    };

    window.removeInput = function(element) {
        let parent = element.parentElement;
        parent.parentElement.removeChild(parent);
        personneCount--;
    };

    window.addObservation = function(event) {
        event.preventDefault();
        observationCount++;
        let newTabLink = document.createElement('button');
        newTabLink.className = 'tab-link';
        newTabLink.innerHTML = `Observation ${observationCount}`;
        newTabLink.onclick = (event) => openTab(event, `observation${observationCount}`);
        document.getElementById('tabs').appendChild(newTabLink);

        let newTabContent = document.createElement('div');
        newTabContent.id = `observation${observationCount}`;
        newTabContent.className = 'tab-content';
        newTabContent.innerHTML = `
        <textarea name="observation${observationCount}" rows="5" cols="50" maxlength="1000" required></textarea>
        <br>
        <input type="file" name="photo${observationCount}" accept="image/*" required>
        <br>
        <label for="entreprise${observationCount}">Entreprise:</label>
        <input type="text" name="entreprise${observationCount}" id="entreprise${observationCount}" required>
        <br>
        <label for="effectif${observationCount}">Effectif:</label>
        <input type="number" name="effectif${observationCount}" id="effectif${observationCount}" required>
        <br>
        `;
        document.querySelector('.part-two').appendChild(newTabContent);
    };

    window.removeObservation = function(event) {
        event.preventDefault();
        let tabs = document.getElementById('tabs');
        tabs.removeChild(tabs.lastChild);
        document.querySelector('.part-two').removeChild(document.querySelector('.part-two').lastChild);
        observationCount--;
    };

    window.openTab = function(evt, tabName) {
        evt.preventDefault();
        let tabContents = document.getElementsByClassName('tab-content');
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].style.display = 'none';
        }
        let tabLinks = document.getElementsByClassName('tab-link');
        for (let i = 0; i < tabLinks.length; i++) {
            tabLinks[i].className = tabLinks[i].className.replace(' active', '');
        }

        document.getElementById(tabName).style.display = 'block';
        evt.currentTarget.className += ' active';
    };

    $('.tab-link').first().trigger('click');
});
let editMode = false;

    function toggleMode() {
      editMode = !editMode;
      let form = document.getElementById('myForm');
      let inputs = form.getElementsByTagName('input');
      let textareas = form.getElementsByTagName('textarea');
      let buttons = form.getElementsByTagName('button');

      for (let input of inputs) {
        input.readOnly = !editMode;
      }

      for (let textarea of textareas) {
        textarea.readOnly = !editMode;
      }

      for (let button of buttons) {
        button.disabled = !editMode;
      }

      document.getElementById('toggleButton').innerText = editMode ? 'Switch to View Mode' : 'Switch to Edit Mode';
    }