<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Asignación de evaluadores</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <div class="container mb-4">
    <h4 class="my-4">Listado de proyectos</h4>
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listar convocatorias</button>
      </li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Facultad del grupo
                </th>
                <th>
                  Facultad del proyecto
                </th>
                <th>
                  Programa
                </th>
                <th>
                  Título
                </th>
                <th>
                  Evaluadores
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Facultad del grupo
                </th>
                <th>
                  Facultad del proyecto
                </th>
                <th>
                  Programa
                </th>
                <th>
                  Título
                </th>
                <th>
                  Evaluadores
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!--  Toast para notificaciones -->
  @extends('admin.components.toast')

  <script type="module">
    $(document).ready(function() {
      //  Iniciar tabla, toast y modal
      let toast = new bootstrap.Toast(document.getElementById('myToast'));
      let modal1 = new bootstrap.Modal(document.getElementById('myModal'));
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/admin/facultad/getProyectosYEvaluadores'
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            data: 'id'
          },
          {
            data: 'tipo'
          },
          {
            data: 'linea'
          },
          {
            data: 'facultad_grupo'
          },
          {
            data: 'facultad_proyecto'
          },
          {
            data: 'programa'
          },
          {
            data: 'titulo'
          },
          {
            data: 'evaluadores'
          },
        ],
        //  Idioma dela información mostrada
        language: {
          zeroRecords: "No se encontraron resultados",
          info: "Mostrando _START_-_END_ de _TOTAL_ registros.",
          infoEmpty: "No hay registros ...",
          infoFiltered: "(filtrado de _MAX_ registros)",
          sSearch: "Buscar:",
          sProcessing: "Cargando data...",
          oPaginate: {
            sFirst: "Primero",
            sLast: "Último",
            sNext: "Siguiente",
            sPrevious: "Anterior"
          },
        }
      });
    });
  </script>
</body>

</html>