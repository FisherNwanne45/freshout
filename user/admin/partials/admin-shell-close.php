    </main>
  </div><!-- end main wrapper -->
</div><!-- end flex container -->

<script>
function adminOpenSidebar() {
  document.getElementById('admin-sidebar').classList.remove('-translate-x-full');
  document.getElementById('sidebar-overlay').classList.remove('hidden');
}
function adminCloseSidebar() {
  document.getElementById('admin-sidebar').classList.add('-translate-x-full');
  document.getElementById('sidebar-overlay').classList.add('hidden');
}
function adminToggleNav(id) {
  const targets = ['nav-accounts', 'nav-transactions', 'nav-settings'];
  const el = document.getElementById(id);
  if (!el) return;

  const willOpen = el.classList.contains('hidden');

  // Accordion behavior: close other dropdown groups first.
  targets.forEach((targetId) => {
    const group = document.getElementById(targetId);
    const groupArrow = document.getElementById(targetId + '-arrow');
    if (!group) return;
    if (targetId !== id) {
      group.classList.add('hidden');
      if (groupArrow) groupArrow.classList.remove('open');
    }
  });

  el.classList.toggle('hidden', !willOpen);
  const arrow = document.getElementById(id + '-arrow');
  if (arrow) arrow.classList.toggle('open', willOpen);
}
</script>
</body>
</html>
<?php exit(); ?>
