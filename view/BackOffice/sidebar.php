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
        Dashboard
      </a>
    </li>
    <li>
      <a href="meals_admin.php" class="<?php echo $activePage === 'meals' ? 'active' : ''; ?>">
        Meals
      </a>
    </li>
    <li>
      <a href="plans_admin.php" class="<?php echo $activePage === 'plans' ? 'active' : ''; ?>">
        My Plans
      </a>
    </li>
    <li class="bo-sidebar__divider"></li>
    <li>
      <a href="../FrontOffice/index.php">
        View Site
      </a>
    </li>
  </ul>
</nav>
