<?php
/** $this->params['breadcrumbs']; */

use yii\helpers\Url;
?>

<header class="header header-sticky mb-4">
  <div class="container-fluid">
    <button class="header-toggler px-md-0 me-md-3" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
      <svg class="icon icon-lg">
        <use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-menu') ?>"></use>
      </svg>
    </button>
    <nav class="flex-grow-1" aria-label="breadcrumb">
      <ol class="breadcrumb my-0 ms-2">
        <?php foreach($this->params['breadcrumbs'] as $item): ?>
          <li class="breadcrumb-item <?= $item['active'] ? 'active' : '' ?>">
            <?php if(!$item['active']): ?>
              <a href="<?= $item['link'] ?>"><?= $item['label'] ?></a>
            <?php else: ?>
              <span><?= $item['label'] ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <ul class="header-nav ms-3">
      <li class="nav-item dropdown"><a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
          <div class="avatar avatar-md"><img class="avatar-img w-100 h-100" style="object-fit: cover" src="<?= Url::to('@web/img/profile-picture-1.jpg') ?>" alt="user@email.com"></div>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
          <a class="dropdown-item" href="<?= Url::toRoute('auth/logout', true) ?>">
            <svg class="icon me-2">
              <use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-account-logout') ?>"></use>
            </svg> Logout</a>
        </div>
      </li>
    </ul>
  </div>
  
  <!-- <div class="header-divider"></div>

  <div class="container-fluid">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb my-0 ms-2">
        <?php foreach($this->params['breadcrumbs'] as $item): ?>
          <li class="breadcrumb-item <?= $item['active'] ? 'active' : '' ?>">
            <?php if(!$item['active']): ?>
              <a href="<?= $item['link'] ?>"><?= $item['label'] ?></a>
            <?php else: ?>
              <span><?= $item['label'] ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    </nav>
  </div> -->
</header>