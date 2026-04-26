/**
 * SmartMeal — Validation partagée
 * Règles :
 *  - Champs texte (nom) : lettres + espaces + accents uniquement, PAS de chiffres
 *  - Champs numériques  : chiffres uniquement, PAS de lettres, valeur >= 0
 */

// ── Helpers ───────────────────────────────────────────────────────────────────

function smShowError(input, msg) {
    smClearFieldError(input);
    input.classList.add('is-invalid');
    const div = document.createElement('div');
    div.className = 'invalid-feedback d-block';
    div.textContent = msg;
    input.parentNode.appendChild(div);
}

function smClearFieldError(input) {
    input.classList.remove('is-invalid');
    input.classList.remove('is-valid');
    const fb = input.parentNode.querySelector('.invalid-feedback');
    if (fb) fb.remove();
}

function smMarkValid(input) {
    smClearFieldError(input);
    input.classList.add('is-valid');
}

function smClearAll(form) {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
}

// ── Règles de validation ──────────────────────────────────────────────────────

/**
 * Valide un champ NOM (texte) :
 * - Obligatoire
 * - Pas de chiffres
 * - Lettres, espaces, accents, tirets, apostrophes uniquement
 */
function smValidateNom(input, label) {
    const val = input.value.trim();
    if (!val) {
        smShowError(input, label + ' est obligatoire.');
        return false;
    }
    if (/\d/.test(val)) {
        smShowError(input, label + ' ne doit pas contenir de chiffres.');
        return false;
    }
    if (!/^[\p{L}\s'\-\.]+$/u.test(val)) {
        smShowError(input, label + ' ne doit contenir que des lettres.');
        return false;
    }
    smMarkValid(input);
    return true;
}

/**
 * Valide un champ NUMÉRIQUE :
 * - Pas de lettres
 * - Valeur >= 0 si renseignée
 * - Valeur >= min si précisé
 */
function smValidateNumber(input, label, min = 0, required = false) {
    const val = input.value.trim();
    if (required && val === '') {
        smShowError(input, label + ' est obligatoire.');
        return false;
    }
    if (val === '') {
        smClearFieldError(input);
        return true; // optionnel vide = OK
    }
    if (isNaN(val) || /[a-zA-Z]/.test(val)) {
        smShowError(input, label + ' doit être un nombre (pas de lettres).');
        return false;
    }
    if (Number(val) < min) {
        smShowError(input, label + ' doit être >= ' + min + '.');
        return false;
    }
    smMarkValid(input);
    return true;
}

// ── Blocage en temps réel ─────────────────────────────────────────────────────

/**
 * Attache les événements de blocage en temps réel sur un formulaire.
 * @param {string} formId  - id du formulaire
 * @param {string[]} nomFields    - noms des champs texte (nom)
 * @param {string[]} numberFields - noms des champs numériques
 */
function smAttachRealtime(formId, nomFields, numberFields) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Champs texte : bloquer chiffres en temps réel
    nomFields.forEach(name => {
        const el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        el.addEventListener('input', function () {
            if (/\d/.test(this.value)) {
                this.value = this.value.replace(/\d/g, '');
                smShowError(this, 'Ce champ ne doit pas contenir de chiffres.');
            } else if (this.value.trim()) {
                smMarkValid(this);
            } else {
                smClearFieldError(this);
            }
        });
        el.addEventListener('blur', function () {
            smValidateNom(this, this.placeholder || 'Ce champ');
        });
    });

    // Champs numériques : bloquer lettres en temps réel
    numberFields.forEach(name => {
        const el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        el.addEventListener('input', function () {
            if (/[a-zA-Z]/.test(this.value)) {
                this.value = this.value.replace(/[a-zA-Z]/g, '');
                smShowError(this, 'Ce champ doit contenir uniquement des chiffres.');
            } else if (this.value.trim()) {
                smMarkValid(this);
            } else {
                smClearFieldError(this);
            }
        });
    });
}

// ── Validation au submit ──────────────────────────────────────────────────────

/**
 * Attache la validation complète au submit d'un formulaire.
 * @param {string} formId
 * @param {Array}  rules  - [{name, type:'nom'|'number', label, min?, required?}]
 */
function smAttachSubmit(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function (e) {
        smClearAll(form);
        let valid = true;

        rules.forEach(rule => {
            const el = form.querySelector('[name="' + rule.name + '"]');
            if (!el) return;

            if (rule.type === 'nom') {
                if (!smValidateNom(el, rule.label)) valid = false;
            } else if (rule.type === 'number') {
                if (!smValidateNumber(el, rule.label, rule.min ?? 0, rule.required ?? false)) valid = false;
            } else if (rule.type === 'select') {
                if (!el.value) {
                    smShowError(el, rule.label + ' est obligatoire.');
                    valid = false;
                } else {
                    smMarkValid(el);
                }
            }
        });

        if (!valid) {
            e.preventDefault();
            // Scroll vers la première erreur
            const first = form.querySelector('.is-invalid');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}
