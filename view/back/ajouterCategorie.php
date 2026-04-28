<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/CategorieProduit.php';

$erreur  = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image       = '';

    // Server-side validation
    $errors = [];
    if ($nom === '')                  $errors[] = 'Category name is required.';
    elseif (strlen($nom) < 2)         $errors[] = 'Name must be at least 2 characters.';
    elseif (strlen($nom) > 100)       $errors[] = 'Name must not exceed 100 characters.';
    elseif (!preg_match('/^[\p{L}0-9 \-_&\'\.]+$/u', $nom)) $errors[] = 'Name contains invalid characters.';

    if (strlen($description) > 500)   $errors[] = 'Description must not exceed 500 characters.';

    if (!empty($_FILES['image']['name'])) {
        $fileExt  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileSize = $_FILES['image']['size'];
        if (!in_array($fileExt, $allowedExtensions, true)) {
            $errors[] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp.';
        } elseif ($fileSize > $maxFileSize) {
            $errors[] = 'Image too large. Maximum size is 5MB.';
        } else {
            $image = time().'_'.uniqid().'.'.$fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR.$image);
        }
    }

    if (empty($errors)) {
        $ctrl = new CategorieController();
        $ctrl->addCategorie(new CategorieProduit(null, $nom, $description, $image));
        header('Location: afficherCategorie.php');
        exit;
    } else {
        $erreur = implode('<br>', $errors);
    }
}

include("header.php");
?>

<style>
.form-card { background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.06);max-width:800px; }
.form-card h3 { font-family:'Raleway',sans-serif;font-size:0.85rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#2d2d2d;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f0f0f0; }
.form-label { font-size:0.78rem;font-weight:600;letter-spacing:0.5px;color:#555;text-transform:uppercase; }
.form-control,.form-select { border-radius:8px;border:1px solid #e0e0e0;font-size:0.85rem;padding:9px 12px;transition:border-color 0.2s; }
.form-control:focus,.form-select:focus { border-color:#e74c3c;box-shadow:0 0 0 3px rgba(231,76,60,0.1);outline:none; }
.form-control.is-valid   { border-color:#28a745; }
.form-control.is-invalid { border-color:#dc3545; }
.field-error { font-size:0.75rem;color:#dc3545;margin-top:4px;display:none; }
.field-error.show { display:block; }
.field-hint  { font-size:0.72rem;color:#999;margin-top:3px; }
.char-counter { font-size:0.72rem;color:#999;text-align:right;margin-top:3px; }
.char-counter.warn  { color:#f57f17; }
.char-counter.error { color:#dc3545; }
.btn-save { background:#e74c3c;color:white;border:none;border-radius:8px;padding:10px 24px;font-size:0.85rem;font-weight:600;letter-spacing:0.5px;transition:0.2s; }
.btn-save:hover { background:#c0392b;color:white; }
.btn-save:disabled { background:#ccc;cursor:not-allowed; }
.btn-cancel { background:#fff;color:#333;border:1px solid #e0e0e0;border-radius:8px;padding:10px 24px;font-size:0.85rem;text-decoration:none;transition:0.2s; }
.btn-cancel:hover { background:#f5f5f5;color:#333; }
.img-preview-wrap { position:relative;display:inline-block; }
.img-preview-wrap .remove-img { position:absolute;top:-6px;right:-6px;background:#dc3545;color:white;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;display:none;align-items:center;justify-content:center; }
.img-preview-wrap:hover .remove-img { display:flex; }
</style>

<div class="page-body">
  <div class="form-card">
    <h3><i class="bi bi-plus-circle me-2" style="color:#e74c3c;"></i>Add New Category</h3>

    <?php if ($erreur): ?>
      <div class="alert alert-danger mb-3" style="border-radius:8px;font-size:0.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $erreur ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3" id="catForm" novalidate>

      <!-- Name -->
      <div class="col-md-6">
        <label class="form-label">Name <span style="color:#e74c3c;">*</span></label>
        <input type="text" name="nom" id="f-nom" class="form-control"
               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
               placeholder="e.g. Meal Prep Packs"
               autocomplete="off">
        <div class="field-error" id="err-nom"></div>
        <div class="field-hint">2–100 characters. Letters, numbers, spaces allowed.</div>
      </div>

      <!-- Image -->
      <div class="col-md-6">
        <label class="form-label">Image <span style="color:#999;font-weight:300;">(optional)</span></label>
        <input type="file" name="image" id="f-image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp"
               onchange="handleImageChange(this)">
        <div class="field-error" id="err-image"></div>
        <div class="field-hint">JPG, PNG, GIF, WEBP — max 5MB</div>
        <div class="mt-2">
          <div class="img-preview-wrap" id="preview-wrap" style="display:none;">
            <img id="preview" src="#" alt="Preview" style="height:110px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
            <button type="button" class="remove-img" onclick="removeImage()" title="Remove">✕</button>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="col-12">
        <label class="form-label">Description <span style="color:#999;font-weight:300;">(optional)</span></label>
        <textarea name="description" id="f-desc" class="form-control" rows="3"
                  placeholder="Brief description of this category..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        <div class="field-error" id="err-desc"></div>
        <div class="char-counter" id="desc-counter">0 / 500</div>
      </div>

      <!-- Buttons -->
      <div class="col-12 d-flex gap-2 mt-2 align-items-center">
        <button type="submit" class="btn-save" id="btn-submit">
          <i class="bi bi-check-lg me-1"></i> Save Category
        </button>
        <a href="afficherCategorie.php" class="btn-cancel">Cancel</a>
        <span id="form-status" style="font-size:0.8rem;color:#999;margin-left:8px;"></span>
      </div>
    </form>
  </div>
</div>

<script>
// ── Validation rules ──
var rules = {
  nom: {
    required: true,
    minLen: 2,
    maxLen: 100,
    pattern: /^[\p{L}0-9 \-_&'\.]+$/u,
    messages: {
      required: 'Category name is required.',
      minLen:   'Name must be at least 2 characters.',
      maxLen:   'Name must not exceed 100 characters.',
      pattern:  'Name contains invalid characters.'
    }
  },
  desc: {
    maxLen: 500,
    messages: { maxLen: 'Description must not exceed 500 characters.' }
  }
};

function validateField(id, rule) {
  var el  = document.getElementById('f-' + id);
  var err = document.getElementById('err-' + id);
  var val = el.value.trim();
  var msg = '';

  if (rule.required && val === '')          msg = rule.messages.required;
  else if (val.length < rule.minLen && val) msg = rule.messages.minLen;
  else if (val.length > rule.maxLen)        msg = rule.messages.maxLen;
  else if (rule.pattern && val && !rule.pattern.test(val)) msg = rule.messages.pattern;

  if (msg) {
    el.classList.add('is-invalid'); el.classList.remove('is-valid');
    err.textContent = msg; err.classList.add('show');
  } else {
    el.classList.remove('is-invalid');
    if (val) el.classList.add('is-valid');
    err.textContent = ''; err.classList.remove('show');
  }
  return msg === '';
}

// Name
document.getElementById('f-nom').addEventListener('input', function() {
  validateField('nom', rules.nom);
});
document.getElementById('f-nom').addEventListener('blur', function() {
  validateField('nom', rules.nom);
});

// Description counter
document.getElementById('f-desc').addEventListener('input', function() {
  var len = this.value.length;
  var counter = document.getElementById('desc-counter');
  counter.textContent = len + ' / 500';
  counter.className = 'char-counter' + (len > 450 ? (len >= 500 ? ' error' : ' warn') : '');
  validateField('desc', rules.desc);
});

// Image validation
function handleImageChange(input) {
  var err = document.getElementById('err-image');
  var wrap = document.getElementById('preview-wrap');
  var preview = document.getElementById('preview');
  err.textContent = ''; err.classList.remove('show');
  input.classList.remove('is-invalid','is-valid');

  if (!input.files || !input.files[0]) { wrap.style.display='none'; return; }

  var file = input.files[0];
  var ext  = file.name.split('.').pop().toLowerCase();
  var allowed = ['jpg','jpeg','png','gif','webp'];

  if (!allowed.includes(ext)) {
    err.textContent = 'Invalid format. Allowed: JPG, PNG, GIF, WEBP.';
    err.classList.add('show'); input.classList.add('is-invalid');
    input.value = ''; wrap.style.display='none'; return;
  }
  if (file.size > 5 * 1024 * 1024) {
    err.textContent = 'File too large. Maximum size is 5MB.';
    err.classList.add('show'); input.classList.add('is-invalid');
    input.value = ''; wrap.style.display='none'; return;
  }

  input.classList.add('is-valid');
  var reader = new FileReader();
  reader.onload = function(e) {
    preview.src = e.target.result;
    wrap.style.display = 'inline-block';
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  document.getElementById('f-image').value = '';
  document.getElementById('preview-wrap').style.display = 'none';
  document.getElementById('f-image').classList.remove('is-valid','is-invalid');
}

// Form submit
document.getElementById('catForm').addEventListener('submit', function(e) {
  var ok = true;
  if (!validateField('nom', rules.nom))  ok = false;
  if (!validateField('desc', rules.desc)) ok = false;

  if (!ok) {
    e.preventDefault();
    document.getElementById('form-status').textContent = 'Please fix the errors above.';
    document.getElementById('form-status').style.color = '#dc3545';
    // Scroll to first error
    var first = document.querySelector('.is-invalid');
    if (first) first.scrollIntoView({behavior:'smooth', block:'center'});
  } else {
    document.getElementById('btn-submit').disabled = true;
    document.getElementById('btn-submit').innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving...';
  }
});

// Init counter
(function(){
  var d = document.getElementById('f-desc');
  if (d) document.getElementById('desc-counter').textContent = d.value.length + ' / 500';
})();
</script>

<?php include("footer.php"); ?>
