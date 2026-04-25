document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  if (!form) return;

  const lettresRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;

  function showError(input, msg) {
    input.classList.add("is-invalid");
    input.classList.remove("is-valid");
    let fb = input.parentElement.querySelector(".invalid-feedback");
    if (!fb) {
      fb = document.createElement("div");
      fb.className = "invalid-feedback";
      input.parentElement.appendChild(fb);
    }
    fb.textContent = msg;
  }

  function clearError(input) {
    input.classList.remove("is-invalid");
    input.classList.add("is-valid");
    const fb = input.parentElement.querySelector(".invalid-feedback");
    if (fb) fb.textContent = "";
  }

  form.addEventListener("submit", function (event) {
    let valid = true;

    // --- Nom catégorie : lettres uniquement ---
    const nomEl = form.querySelector("input[name='nom']");
    if (nomEl) {
      const nom = nomEl.value.trim();
      if (!nom) {
        showError(nomEl, "Le nom est obligatoire.");
        valid = false;
      } else if (nom.length < 2) {
        showError(nomEl, "Le nom doit contenir au moins 2 caractères.");
        valid = false;
      } else if (nom.length > 100) {
        showError(nomEl, "Le nom ne peut pas dépasser 100 caractères.");
        valid = false;
      } else if (!lettresRegex.test(nom)) {
        showError(nomEl, "Le nom doit contenir uniquement des lettres.");
        valid = false;
      } else {
        clearError(nomEl);
      }
    }

    if (!valid) event.preventDefault();
  });

  // Validation en temps réel
  const nomEl = form.querySelector("input[name='nom']");
  if (nomEl) {
    nomEl.addEventListener("input", function () {
      nomEl.classList.remove("is-invalid", "is-valid");
      const fb = nomEl.parentElement.querySelector(".invalid-feedback");
      if (fb) fb.textContent = "";
    });
  }
});
