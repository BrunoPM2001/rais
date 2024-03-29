<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      @yield('form')
      <div class="modal-header">
        <h1 class="modal-title fs-5">@yield('titulo')</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @yield('contenido')
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
      @yield('end_form')
    </div>
  </div>
</div>