document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("produitForm");
  if (!form) return;

  // Fixer le min de la date d'expiration à aujourd'hui
  const dateInput = document.getElementById("dateExpiration");
  if (dateInput) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);
  }

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

  // Regex : lettres (accents inclus), espaces, tirets, apostrophes uniquement
  const lettresRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;

  form.addEventListener("submit", function (event) {
    let valid = true;

    // --- Nom produit ---
    const nomEl = document.getElementById("nom");
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

    // --- Prix ---
    const prixEl = document.getElementById("prix");
    if (prixEl) {
      const prix = parseFloat(prixEl.value || "0");
      if (isNaN(prix) || prix <= 0) {
        showError(prixEl, "Le prix doit être un nombre strictement positif.");
        valid = false;
      } else if (prix > 99999) {
        showError(prixEl, "Le prix semble trop élevé (max 99 999 DT).");
        valid = false;
      } else {
        clearError(prixEl);
      }
    }

    // --- Quantité : doit être un entier strictement positif ---
    const qteEl = document.getElementById("quantiteStock");
    if (qteEl) {
      const quantite = parseInt(qteEl.value || "0", 10);
      if (isNaN(quantite) || quantite <= 0) {
        showError(qteEl, "La quantité doit être un nombre entier positif (minimum 1).");
        valid = false;
      } else if (quantite > 99999) {
        showError(qteEl, "La quantité semble trop élevée (max 99 999).");
        valid = false;
      } else {
        clearError(qteEl);
      }
    }

    // --- Date d'expiration ---
    const dateEl = document.getElementById("dateExpiration");
    if (dateEl) {
      const dateVal = dateEl.value || "";
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const dateChoisie = new Date(dateVal);
      if (!dateVal) {
        showError(dateEl, "La date d'expiration est obligatoire.");
        valid = false;
      } else if (isNaN(dateChoisie.getTime())) {
        showError(dateEl, "La date d'expiration est invalide.");
        valid = false;
      } else if (dateChoisie < today) {
        showError(dateEl, "La date d'expiration ne peut pas être dans le passé.");
        valid = false;
      } else {
        clearError(dateEl);
      }
    }

    if (!valid) event.preventDefault();
  });

  // Validation en temps réel
  ["nom", "prix", "quantiteStock", "dateExpiration"].forEach(function (id) {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("input", function () {
        el.classList.remove("is-invalid", "is-valid");
        const fb = el.parentElement.querySelector(".invalid-feedback");
        if (fb) fb.textContent = "";
      });
    }
  });
});
