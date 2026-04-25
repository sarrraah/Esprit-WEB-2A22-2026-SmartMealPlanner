<?php
// Usage: include __DIR__ . '/sidebar.php';
// Pass $activePage = 'meals' | 'plans' | 'dashboard' before including.
$activePage = $activePage ?? '';
$base = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';
?>
<nav class="bo-sidebar">
  <div class="bo-sidebar__logo">
    <img src="<?php echo $base; ?>img/logo-smp.jpg" alt="SmartMealPlanner" height="36">
    <span><span style="color:#212529;">Smart</span><span style="color:#fff;">MealPlanner</span></span>
  </div>
  <ul class="bo-sidebar__menu">
    <li>
      <a href="index.php" class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>
    <li>
      <a href="meals_admin.php" class="<?php echo $activePage === 'meals' ? 'active' : ''; ?>">
        <i class="bi bi-egg-fried"></i> Meals
      </a>
    </li>
    <li>
      <a href="plans_admin.php" class="<?php echo $activePage === 'plans' ? 'active' : ''; ?>">
        <i class="bi bi-calendar-check"></i> My Plans
      </a>
    </li>
    <li class="bo-sidebar__divider"></li>
    <li>
      <a href="../FrontOffice/index.php">
        <i class="bi bi-eye"></i> View Site
      </a>
    </li>
  </ul>
</nav>
