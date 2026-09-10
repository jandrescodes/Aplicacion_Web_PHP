</main>
<footer class="mt-auto">
  <div class="container-fluid py-3 bg-dark text-white text-center">
    <div class="row">
      <div class="col-12">
        <p class="mb-0 small">
          &copy; <?= date('Y'); ?> <span class="fw-bold">Derechos Reservados UPDS</span>
          <span class="d-none d-md-inline">|</span>
          <br class="d-md-none">
          Sistema de Gestión Empresarial
        </p>
      </div>
    </div>
  </div>
</footer>
<!-- Bootstrap JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous" defer></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous" defer></script>

<!-- DataTables Core & Extensions (solo en páginas con tabla) -->
<?php if (!empty($needDataTables)) : ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" integrity="sha384-edQnMujp90eoACbp4sS9zj/0dMW+mjTJFxCNeW0hM7rVy4OutMVBq6ec4axiLP9U" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js" integrity="sha384-ON66nBewQ67SNHiJWBO8f7ldsYeQ6wShDTaaikVGjNyNxC7P2rTge/Gf77mL/Ijt" crossorigin="anonymous" defer></script>

<!-- DataTables Extensions: Responsive + Buttons -->
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js" integrity="sha384-PY2wFFZbq7kCYodfBP408z2yXR7heditX+7fcNGW1QlNvoNReFROjc2e+gETYS2J" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js" integrity="sha384-vH3aRzwnpaxVgteYf4GLvyv6ISN0tJmjKQtmtO2ZFJG4l7P64N+JGC56jsriiPQy" crossorigin="anonymous" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" integrity="sha384-+mbV2IY1Zk/X1p/nWllGySJSUN8uMs+gUAN10Or95UBH0fpj6GfKgPmgC5EXieXG" crossorigin="anonymous" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js" integrity="sha384-POmQLDS+gc6Qd3ZalIVuutsaZQkTDU8foo3J6egQy4YmrNhxcGuIlKXsas5XaZNG" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js" integrity="sha384-R/1aieuV27Trs+cczprs/2SFjCwdqKQo/4qYM0SBHv/wla+LSTHWds8FCw6s51Bb" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js" integrity="sha384-HTrhO6m5QbXzKQm/28HT6tYIdf/GeCK8xucgDpUbyrlqDwVh5AN5il4gcRxyIF51" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js" integrity="sha384-m1bkDRTx51kIl1OC2S24Tnj0vEwit7u2Be60VaV392LbUhm9qrm5GBlraC3S2U/l" crossorigin="anonymous" defer></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js" integrity="sha384-h/SRPFzc2+BE+XfOqlAqiHb43fnY8jzXhQ0fI1JBfgrjbxUokMr9To2eLbSWEt1g" crossorigin="anonymous" defer></script>
<?php endif; ?>

<!-- Scripts Personalizados -->
<script src="<?= $public_base; ?>js/notifications.js" defer></script>
<script src="<?= $public_base; ?>js/main.js" defer></script>

</body>

</html>