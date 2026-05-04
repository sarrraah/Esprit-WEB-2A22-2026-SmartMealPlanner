<?php
/**
 * GenerateRecetteController.php — Générateur automatique d'étapes de recette
 *
 * Reçoit une requête POST JSON avec :
 *   - nom          : nom de la recette (obligatoire)
 *   - difficulte   : Facile | Moyen | Difficile
 *   - temps_prep   : minutes de préparation (optionnel)
 *   - temps_cuisson: minutes de cuisson (optionnel)
 *   - nb_personnes : nombre de personnes (optionnel)
 *
 * Retourne un JSON : { "etapes": "..." } ou { "error": "..." }
 *
 * La génération est basée sur une analyse du nom de la recette :
 *   - Détection du mode de cuisson (four, poêle, vapeur, cru...)
 *   - Détection de la catégorie (soupe, salade, gâteau, viande, poisson...)
 *   - Adaptation des étapes selon la difficulté et les temps
 */

header('Content-Type: application/json; charset=utf-8');

// Lire le corps JSON de la requête
$input = json_decode(file_get_contents('php://input'), true);

// Valider les données reçues
$nom        = trim($input['nom']         ?? '');
$difficulte = trim($input['difficulte']  ?? 'Facile');
$tempsPrep  = (int)($input['temps_prep']    ?? 0);
$tempsCuis  = (int)($input['temps_cuisson'] ?? 0);
$nbPers     = max(1, (int)($input['nb_personnes'] ?? 2));

if ($nom === '') {
    echo json_encode(['error' => 'Le nom de la recette est requis.']);
    exit;
}

// ── Analyse du nom pour détecter la catégorie ─────────────────────────────────

$nomLower = mb_strtolower($nom, 'UTF-8');

/**
 * Vérifie si le nom contient l'un des mots-clés donnés.
 */
function contient(string $nom, array $mots): bool {
    foreach ($mots as $mot) {
        if (mb_strpos($nom, $mot) !== false) return true;
    }
    return false;
}

// Détection du mode de cuisson
$auFour    = contient($nomLower, ['rôti','roti','gratin','tarte','cake','gâteau','gateau','pizza','quiche','biscuit','cookie','muffin','pain','brioche','lasagne','moussaka','crumble','soufflé','souffle','feuilleté','feuillette','clafoutis','fondant','brownie']);
$aLaPoele  = contient($nomLower, ['poêlé','poele','sauté','saute','omelette','crêpe','crepe','pancake','steak','côtelette','cotelette','escalope','émincé','emince']);
$vapeur    = contient($nomLower, ['vapeur','cuit à la vapeur']);
$mijote    = contient($nomLower, ['mijoté','mijote','ragoût','ragout','daube','tajine','pot-au-feu','blanquette','bourguignon','cassoulet','curry','colombo']);
$cru       = contient($nomLower, ['salade','carpaccio','tartare','ceviche','gaspacho','smoothie','jus','cocktail','tiramisu','mousse','verrines','verrine']);
$soupe     = contient($nomLower, ['soupe','velouté','veloute','bouillon','potage','bisque','minestrone','gazpacho']);
$dessert   = contient($nomLower, ['gâteau','gateau','tarte','cake','muffin','cookie','biscuit','brownie','fondant','crumble','tiramisu','mousse','panna','crème','creme','glace','sorbet','macaron','éclair','eclair','profiterole','charlotte','bavarois','flan','clafoutis','crêpe','crepe','pancake','brioche','pain perdu','beignet']);
$poisson   = contient($nomLower, ['poisson','saumon','thon','cabillaud','dorade','bar','sole','truite','sardine','maquereau','crevette','moule','homard','langouste','calamar','seiche','poulpe','fruits de mer']);
$viande    = contient($nomLower, ['poulet','bœuf','boeuf','veau','porc','agneau','canard','dinde','lapin','steak','côte','cote','filet','rôti','roti','escalope','saucisse','merguez','chorizo','jambon','lardons','bacon']);
$pates     = contient($nomLower, ['pâtes','pates','spaghetti','tagliatelle','penne','rigatoni','fusilli','lasagne','gnocchi','ravioli','cannelloni','macaroni','linguine','fettuccine']);
$riz       = contient($nomLower, ['riz','risotto','paella','pilaf']);
$legumes   = contient($nomLower, ['légumes','legumes','ratatouille','poêlée','poelée','wok','grillé','grille','farci','farcie']);

// ── Construction des étapes selon la catégorie détectée ──────────────────────

$etapes = '';

// Ligne de contexte selon le nombre de personnes
$persStr = $nbPers > 1 ? "pour {$nbPers} personnes" : "pour 1 personne";

// Temps de préparation formaté
$prepStr = $tempsPrep > 0 ? " ({$tempsPrep} min)" : '';
$cuisStr = $tempsCuis > 0 ? " ({$tempsCuis} min)" : '';

// ── Soupe / Velouté ───────────────────────────────────────────────────────────
if ($soupe) {
    $etapes = "1. Éplucher et couper les légumes en morceaux réguliers{$prepStr}.\n";
    $etapes .= "2. Faire revenir un oignon émincé dans un filet d'huile d'olive à feu moyen.\n";
    $etapes .= "3. Ajouter les légumes et faire revenir 3 à 5 minutes en remuant.\n";
    $etapes .= "4. Couvrir d'eau ou de bouillon chaud et porter à ébullition.\n";
    $etapes .= "5. Laisser mijoter à feu doux{$cuisStr} jusqu'à ce que les légumes soient tendres.\n";
    $etapes .= "6. Mixer finement jusqu'à obtenir une texture lisse et veloutée.\n";
    $etapes .= "7. Rectifier l'assaisonnement en sel et poivre.\n";
    $etapes .= "8. Servir chaud {$persStr}, avec un filet de crème fraîche si désiré.";
}

// ── Dessert / Pâtisserie ──────────────────────────────────────────────────────
elseif ($dessert) {
    if ($auFour) {
        $tempFour = $difficulte === 'Difficile' ? '180°C (thermostat 6)' : '170°C (thermostat 5-6)';
        $etapes = "1. Préchauffer le four à {$tempFour}.\n";
        $etapes .= "2. Préparer et peser tous les ingrédients{$prepStr}.\n";
        $etapes .= "3. Mélanger les ingrédients secs dans un grand saladier (farine, sucre, levure).\n";
        $etapes .= "4. Incorporer les ingrédients humides (œufs, beurre fondu, lait) progressivement.\n";
        $etapes .= "5. Mélanger jusqu'à obtenir une pâte homogène sans grumeaux.\n";
        $etapes .= "6. Verser dans un moule beurré et fariné.\n";
        $etapes .= "7. Enfourner{$cuisStr} — vérifier la cuisson avec la pointe d'un couteau.\n";
        $etapes .= "8. Laisser refroidir 10 minutes avant de démouler.\n";
        $etapes .= "9. Décorer selon votre goût et servir {$persStr}.";
    } else {
        $etapes = "1. Préparer et peser tous les ingrédients{$prepStr}.\n";
        $etapes .= "2. Mélanger les ingrédients de base dans un saladier.\n";
        $etapes .= "3. Incorporer les autres ingrédients progressivement en mélangeant délicatement.\n";
        $etapes .= "4. Répartir dans des verrines ou un plat de service.\n";
        $etapes .= "5. Réfrigérer au minimum 2 heures avant de servir.\n";
        $etapes .= "6. Décorer au moment de servir {$persStr}.";
    }
}

// ── Pâtes ─────────────────────────────────────────────────────────────────────
elseif ($pates) {
    $etapes = "1. Porter une grande casserole d'eau salée à ébullition.\n";
    $etapes .= "2. Préparer la sauce : faire revenir l'ail et l'oignon dans l'huile d'olive{$prepStr}.\n";
    $etapes .= "3. Ajouter les ingrédients principaux de la sauce et laisser mijoter{$cuisStr}.\n";
    $etapes .= "4. Cuire les pâtes selon les indications du paquet (al dente).\n";
    $etapes .= "5. Réserver une louche d'eau de cuisson avant d'égoutter.\n";
    $etapes .= "6. Mélanger les pâtes égouttées avec la sauce, ajouter un peu d'eau de cuisson si nécessaire.\n";
    $etapes .= "7. Rectifier l'assaisonnement en sel et poivre.\n";
    $etapes .= "8. Servir immédiatement {$persStr} avec du parmesan râpé si désiré.";
}

// ── Riz / Risotto ─────────────────────────────────────────────────────────────
elseif ($riz) {
    if (contient($nomLower, ['risotto'])) {
        $etapes = "1. Chauffer le bouillon dans une casserole à part et le maintenir chaud.\n";
        $etapes .= "2. Faire revenir l'échalote dans le beurre à feu moyen{$prepStr}.\n";
        $etapes .= "3. Ajouter le riz arborio et nacrer 2 minutes en remuant.\n";
        $etapes .= "4. Déglacer avec le vin blanc et laisser absorber.\n";
        $etapes .= "5. Ajouter le bouillon chaud louche par louche en remuant constamment{$cuisStr}.\n";
        $etapes .= "6. Incorporer les ingrédients principaux à mi-cuisson.\n";
        $etapes .= "7. Hors du feu, ajouter le beurre froid et le parmesan (mantecatura).\n";
        $etapes .= "8. Laisser reposer 1 minute et servir {$persStr}.";
    } else {
        $etapes = "1. Rincer le riz à l'eau froide jusqu'à ce que l'eau soit claire.\n";
        $etapes .= "2. Préparer les ingrédients d'accompagnement{$prepStr}.\n";
        $etapes .= "3. Cuire le riz selon la méthode choisie (absorption ou grande eau).\n";
        $etapes .= "4. Préparer la garniture dans une poêle chaude{$cuisStr}.\n";
        $etapes .= "5. Assaisonner et mélanger avec le riz cuit.\n";
        $etapes .= "6. Servir chaud {$persStr}.";
    }
}

// ── Salade / Plat cru ─────────────────────────────────────────────────────────
elseif ($cru && !$dessert) {
    $etapes = "1. Laver et essorer soigneusement tous les légumes et herbes{$prepStr}.\n";
    $etapes .= "2. Couper les ingrédients en morceaux réguliers selon la recette.\n";
    $etapes .= "3. Préparer la vinaigrette ou la sauce d'assaisonnement.\n";
    $etapes .= "4. Disposer les ingrédients dans un saladier ou sur les assiettes.\n";
    $etapes .= "5. Ajouter les garnitures (fromage, noix, croûtons, etc.).\n";
    $etapes .= "6. Assaisonner au dernier moment et servir frais {$persStr}.";
}

// ── Plat mijoté ───────────────────────────────────────────────────────────────
elseif ($mijote) {
    $etapes = "1. Couper les viandes et légumes en morceaux réguliers{$prepStr}.\n";
    $etapes .= "2. Faire dorer les morceaux de viande sur toutes les faces dans une cocotte avec de l'huile.\n";
    $etapes .= "3. Retirer la viande et faire revenir les oignons et l'ail dans la même cocotte.\n";
    $etapes .= "4. Remettre la viande, ajouter les épices et les aromates.\n";
    $etapes .= "5. Mouiller avec le bouillon ou la sauce et porter à ébullition.\n";
    $etapes .= "6. Couvrir et laisser mijoter à feu doux{$cuisStr} en remuant de temps en temps.\n";
    $etapes .= "7. Ajouter les légumes à mi-cuisson.\n";
    $etapes .= "8. Rectifier l'assaisonnement et servir chaud {$persStr}.";
}

// ── Poisson ───────────────────────────────────────────────────────────────────
elseif ($poisson) {
    if ($auFour) {
        $etapes = "1. Préchauffer le four à 200°C.\n";
        $etapes .= "2. Préparer le poisson : écailler, vider et rincer si nécessaire{$prepStr}.\n";
        $etapes .= "3. Préparer la marinade ou la garniture aromatique.\n";
        $etapes .= "4. Disposer le poisson dans un plat huilé, assaisonner.\n";
        $etapes .= "5. Enfourner{$cuisStr} — le poisson est cuit quand la chair se détache facilement.\n";
        $etapes .= "6. Préparer la sauce ou l'accompagnement pendant la cuisson.\n";
        $etapes .= "7. Servir immédiatement {$persStr}.";
    } else {
        $etapes = "1. Préparer le poisson : sécher avec du papier absorbant{$prepStr}.\n";
        $etapes .= "2. Assaisonner généreusement sel, poivre et herbes.\n";
        $etapes .= "3. Chauffer une poêle antiadhésive avec un filet d'huile à feu vif.\n";
        $etapes .= "4. Saisir le poisson côté peau en premier{$cuisStr}.\n";
        $etapes .= "5. Retourner délicatement et cuire l'autre côté 2 à 3 minutes.\n";
        $etapes .= "6. Ajouter le beurre et arroser le poisson en fin de cuisson.\n";
        $etapes .= "7. Servir immédiatement {$persStr} avec l'accompagnement choisi.";
    }
}

// ── Viande au four ────────────────────────────────────────────────────────────
elseif ($viande && $auFour) {
    $tempFour = $difficulte === 'Difficile' ? '220°C' : '200°C';
    $etapes = "1. Sortir la viande du réfrigérateur 30 minutes avant la cuisson.\n";
    $etapes .= "2. Préchauffer le four à {$tempFour}.\n";
    $etapes .= "3. Préparer la marinade ou le mélange d'herbes et d'épices{$prepStr}.\n";
    $etapes .= "4. Badigeonner la viande et assaisonner généreusement.\n";
    $etapes .= "5. Saisir la viande dans une cocotte allant au four à feu vif, 3 min de chaque côté.\n";
    $etapes .= "6. Enfourner{$cuisStr} en arrosant régulièrement avec le jus de cuisson.\n";
    $etapes .= "7. Laisser reposer la viande 10 minutes sous aluminium avant de découper.\n";
    $etapes .= "8. Déglacer le plat pour préparer la sauce et servir {$persStr}.";
}

// ── Viande à la poêle ─────────────────────────────────────────────────────────
elseif ($viande) {
    $etapes = "1. Sortir la viande du réfrigérateur 20 minutes avant la cuisson.\n";
    $etapes .= "2. Préparer la marinade ou l'assaisonnement{$prepStr}.\n";
    $etapes .= "3. Chauffer une poêle à feu vif avec un filet d'huile.\n";
    $etapes .= "4. Saisir la viande{$cuisStr} selon la cuisson désirée.\n";
    $etapes .= "5. Laisser reposer 5 minutes avant de servir.\n";
    $etapes .= "6. Préparer la sauce en déglacant la poêle avec du bouillon ou du vin.\n";
    $etapes .= "7. Servir {$persStr} avec l'accompagnement choisi.";
}

// ── Gratin / Plat au four ─────────────────────────────────────────────────────
elseif ($auFour) {
    $tempFour = $difficulte === 'Difficile' ? '200°C' : '180°C';
    $etapes = "1. Préchauffer le four à {$tempFour}.\n";
    $etapes .= "2. Préparer et couper tous les ingrédients{$prepStr}.\n";
    $etapes .= "3. Préparer la sauce ou la béchamel si nécessaire.\n";
    $etapes .= "4. Beurrer un plat à gratin et disposer les ingrédients en couches.\n";
    $etapes .= "5. Napper de sauce et parsemer de fromage râpé.\n";
    $etapes .= "6. Enfourner{$cuisStr} jusqu'à ce que le dessus soit doré.\n";
    $etapes .= "7. Laisser reposer 5 minutes avant de servir {$persStr}.";
}

// ── Plat à la poêle / Wok ─────────────────────────────────────────────────────
elseif ($aLaPoele || $legumes) {
    $etapes = "1. Préparer et couper tous les ingrédients en morceaux réguliers{$prepStr}.\n";
    $etapes .= "2. Chauffer une poêle ou un wok à feu vif avec de l'huile.\n";
    $etapes .= "3. Faire revenir les ingrédients les plus longs à cuire en premier.\n";
    $etapes .= "4. Ajouter les autres ingrédients progressivement{$cuisStr}.\n";
    $etapes .= "5. Assaisonner avec sel, poivre et les épices choisies.\n";
    $etapes .= "6. Mélanger régulièrement pour une cuisson homogène.\n";
    $etapes .= "7. Servir immédiatement {$persStr}.";
}

// ── Recette générique (fallback intelligent) ──────────────────────────────────
else {
    // Adapter le nombre d'étapes à la difficulté
    if ($difficulte === 'Facile') {
        $etapes = "1. Rassembler et préparer tous les ingrédients nécessaires{$prepStr}.\n";
        $etapes .= "2. Préparer les ingrédients : laver, éplucher et couper selon la recette.\n";
        $etapes .= "3. Cuire ou assembler les ingrédients selon la méthode choisie{$cuisStr}.\n";
        $etapes .= "4. Assaisonner avec sel, poivre et les épices de votre choix.\n";
        $etapes .= "5. Dresser et servir {$persStr}.";
    } elseif ($difficulte === 'Moyen') {
        $etapes = "1. Rassembler et peser tous les ingrédients{$prepStr}.\n";
        $etapes .= "2. Préparer les ingrédients : laver, éplucher, couper et mariner si nécessaire.\n";
        $etapes .= "3. Préparer la base de la recette (sauce, fond, marinade).\n";
        $etapes .= "4. Cuire les ingrédients principaux selon la méthode appropriée{$cuisStr}.\n";
        $etapes .= "5. Préparer les accompagnements en parallèle.\n";
        $etapes .= "6. Assembler les éléments et rectifier l'assaisonnement.\n";
        $etapes .= "7. Dresser soigneusement et servir {$persStr}.";
    } else {
        // Difficile
        $etapes = "1. Préparer le matériel et peser précisément tous les ingrédients{$prepStr}.\n";
        $etapes .= "2. Réaliser les préparations de base (fond, sauce, marinade, pâte).\n";
        $etapes .= "3. Préparer les ingrédients principaux : tailler, mariner, assaisonner.\n";
        $etapes .= "4. Cuire les éléments principaux en maîtrisant la température{$cuisStr}.\n";
        $etapes .= "5. Préparer les garnitures et accompagnements.\n";
        $etapes .= "6. Réaliser la sauce de finition.\n";
        $etapes .= "7. Assembler les éléments avec soin.\n";
        $etapes .= "8. Rectifier l'assaisonnement et vérifier les textures.\n";
        $etapes .= "9. Dresser avec précision et servir {$persStr}.";
    }
}

// Retourner les étapes générées en JSON
echo json_encode([
    'etapes' => $etapes,
    'nom'    => $nom,
], JSON_UNESCAPED_UNICODE);
