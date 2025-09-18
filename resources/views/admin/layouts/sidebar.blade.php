 <!-- Menu -->

 <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
     <div class="app-brand demo">
         <a href="javascript:void(0);" class="app-brand-link">
             <img src="{{ asset('admin/assets/img/logo/main.svg') }}" style="max-width: 100%; width: auto;">
         </a>

         <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
             <i class="bx bx-chevron-left bx-sm align-middle"></i>
         </a>
     </div>

     <div class="menu-inner-shadow"></div>

     <ul class="menu-inner py-1">
         <!-- Dashboards -->
         <li class="menu-item">
             <a href="{{ route('admin.home') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-home-circle"></i>
                 <div data-i18n="Dashboards">Dashboards</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="javascript:void(0);" class="menu-link menu-toggle">
                 <i class="menu-icon tf-icons bx bx-cog"></i>
                 <div class="text-truncate" data-i18n="Wizard Examples">Setting</div>
             </a>
             <ul class="menu-sub">
                 <li class="menu-item">
                     <a href="{{ route('store.setting.form') }}" class="menu-link">
                         <div class="text-truncate" data-i18n="Checkout">Store Setting</div>
                     </a>
                 </li>
                 <li class="menu-item">
                     <a href="{{ route('email-templates.index') }}" class="menu-link">
                         <div class="text-truncate" data-i18n="Checkout">Email Template</div>
                     </a>
                 </li>
                 <li class="menu-item">
                     <a href="{{ route('store.setting.shoppage') }}" class="menu-link">
                         <div class="text-truncate" data-i18n="Checkout">Shop Page & Hot Items Setting</div>
                     </a>
                 </li>
                 <li class="menu-item">
                     <a href="{{ route('authentication.setting') }}" class="menu-link">
                         <div class="text-truncate" data-i18n="Checkout">Authentication</div>
                     </a>
                 </li>
             </ul>
         </li>

         <li class="menu-item">
             <a href="{{ route('category.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-category'></i>
                 <div data-i18n="Dashboards">Category</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('faq.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-help-circle'></i> <!-- bx-help-circle for FAQ icon -->
                 <div data-i18n="Dashboards">FAQ</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('review.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-star'></i> <!-- bx-star icon for Review -->
                 <div data-i18n="Dashboards">Review</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('qrcode.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-qr'></i> <!-- bx-qr icon for QR Code Management -->
                 <div data-i18n="Dashboards">Qr Code</div>
             </a>
         </li>


         <li class="menu-item">
             <a href="javascript:void(0);" class="menu-link menu-toggle">
                 <i class='menu-icon tf-icons bx bx-package'></i>
                 <div class="text-truncate" data-i18n="Wizard Examples">Orders</div>
             </a>
             <ul class="menu-sub">
                 <li class="menu-item">
                     <a href="{{ route('orders.index') }}" class="menu-link">
                         <div class="text-truncate" data-i18n="Checkout">Orders</div>
                     </a>
                 </li>
                 <li class="menu-item">
                     <a href="{{ route('return-orders.index') }}" class="menu-link">
                         <div class="text-truncate" data-i18n="Checkout">Return Orders</div>
                     </a>
                 </li>
             </ul>
         </li>

         <li class="menu-item">
             <a href="{{ route('transactions.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-credit-card'></i>
                 <div data-i18n="Dashboards">Transaction</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('users.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-user'></i> <!-- User icon -->
                 <div data-i18n="Dashboards">Users</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('subscribers.index') }}" class="menu-link">
                 <i class='menu-icon tf-icons bx bx-envelope'></i> <!-- Envelope (mail) icon -->
                 <div data-i18n="Dashboards">Subscribed Emails</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('testimonials.index') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-message-dots"></i>
                 <div data-i18n="Dashboards">Testimonials</div>
             </a>
         </li>

     </ul>

 </aside>
 <!-- / Menu -->
